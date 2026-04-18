-- ============================================================================
-- AlgoCodeBF — Migration consolidée (Phases 1 → 7)
-- ----------------------------------------------------------------------------
-- À exécuter UNE SEULE FOIS, après supabase/schema.sql.
-- Idempotent : toutes les instructions utilisent IF NOT EXISTS / DO $$ / CREATE
-- OR REPLACE → ré-exécution sans risque.
-- ============================================================================


-- ============================================================================
-- PHASE 1 — Fondations : newsletter token, points profils, triggers score
-- ============================================================================

-- --- Newsletter : token de désabonnement ------------------------------------
alter table if exists public.newsletter_subscribers
  add column if not exists unsubscribe_token text unique
    default replace(gen_random_uuid()::text, '-', '');

update public.newsletter_subscribers
set unsubscribe_token = replace(gen_random_uuid()::text, '-', '')
where unsubscribe_token is null;

-- --- Profiles : colonne points (leaderboard dénormalisé) --------------------
alter table if exists public.profiles
  add column if not exists points integer not null default 0;

create index if not exists idx_profiles_points on public.profiles (points desc);

-- --- Calcul des points (posts×10 + tuto×25 + comments×2 + likes×1) ----------
create or replace function public.compute_user_points(uid uuid)
returns integer as $$
declare
  p integer := 0;
  t integer := 0;
  c integer := 0;
  l integer := 0;
begin
  select count(*) into p from public.posts where user_id = uid and status = 'active';
  select count(*) into t from public.tutorials where user_id = uid and status = 'active';
  select count(*) into c from public.comments where user_id = uid and status = 'active';
  select count(*) into l
    from public.likes lk
    join public.posts po on po.id = lk.likeable_id and lk.likeable_type = 'post'
    where po.user_id = uid;
  return p * 10 + t * 25 + c * 2 + l;
end;
$$ language plpgsql security definer;

create or replace function public.refresh_user_points(uid uuid)
returns void as $$
begin
  update public.profiles set points = public.compute_user_points(uid) where id = uid;
end;
$$ language plpgsql security definer;

-- --- handle_new_user : profil + badge "Nouveau Membre" ----------------------
create or replace function public.handle_new_user()
returns trigger as $$
declare
  welcome_badge_id bigint;
begin
  insert into public.profiles (id, prenom, nom, phone, university, faculty, city, role)
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'prenom', 'Utilisateur'),
    coalesce(new.raw_user_meta_data->>'nom', ''),
    new.raw_user_meta_data->>'phone',
    new.raw_user_meta_data->>'university',
    new.raw_user_meta_data->>'faculty',
    new.raw_user_meta_data->>'city',
    coalesce(new.raw_user_meta_data->>'role', 'user')
  )
  on conflict (id) do nothing;

  select id into welcome_badge_id from public.badges where name = 'Nouveau Membre' limit 1;
  if welcome_badge_id is not null then
    insert into public.user_badges (user_id, badge_id)
    values (new.id, welcome_badge_id)
    on conflict do nothing;
  end if;

  return new;
end;
$$ language plpgsql security definer;

-- --- Triggers pour maintenir profiles.points à jour --------------------------
create or replace function public.tg_refresh_points_on_change()
returns trigger as $$
begin
  if tg_op = 'DELETE' then
    perform public.refresh_user_points(old.user_id);
  else
    perform public.refresh_user_points(new.user_id);
  end if;
  return null;
end;
$$ language plpgsql security definer;

drop trigger if exists trg_refresh_points_posts on public.posts;
create trigger trg_refresh_points_posts
  after insert or update or delete on public.posts
  for each row execute procedure public.tg_refresh_points_on_change();

drop trigger if exists trg_refresh_points_tutorials on public.tutorials;
create trigger trg_refresh_points_tutorials
  after insert or update or delete on public.tutorials
  for each row execute procedure public.tg_refresh_points_on_change();

drop trigger if exists trg_refresh_points_comments on public.comments;
create trigger trg_refresh_points_comments
  after insert or update or delete on public.comments
  for each row execute procedure public.tg_refresh_points_on_change();

create or replace function public.tg_refresh_points_on_like()
returns trigger as $$
declare
  target_user uuid;
  row_to_use record;
begin
  if tg_op = 'DELETE' then row_to_use := old; else row_to_use := new; end if;

  if row_to_use.likeable_type = 'post' then
    select user_id into target_user from public.posts where id = row_to_use.likeable_id;
  elsif row_to_use.likeable_type = 'tutorial' then
    select user_id into target_user from public.tutorials where id = row_to_use.likeable_id;
  end if;

  if target_user is not null then
    perform public.refresh_user_points(target_user);
  end if;
  return null;
end;
$$ language plpgsql security definer;

drop trigger if exists trg_refresh_points_likes on public.likes;
create trigger trg_refresh_points_likes
  after insert or delete on public.likes
  for each row execute procedure public.tg_refresh_points_on_like();


-- ============================================================================
-- PHASE 3 — Forum (attachments, reports) + tags blog + RPC vues
-- ============================================================================

-- --- post_attachments : pièces jointes de discussion ------------------------
create table if not exists public.post_attachments (
  id bigserial primary key,
  post_id bigint not null references public.posts(id) on delete cascade,
  user_id uuid references public.profiles(id) on delete set null,
  file_path text not null,
  original_name text,
  mime_type text,
  file_size bigint,
  created_at timestamptz default now()
);
create index if not exists idx_post_attachments_post on public.post_attachments(post_id);

alter table public.post_attachments enable row level security;
drop policy if exists "post_attachments_public_read" on public.post_attachments;
create policy "post_attachments_public_read"
  on public.post_attachments for select using (true);
drop policy if exists "post_attachments_owner_write" on public.post_attachments;
create policy "post_attachments_owner_write"
  on public.post_attachments for insert
  with check (auth.uid() = user_id);
drop policy if exists "post_attachments_owner_delete" on public.post_attachments;
create policy "post_attachments_owner_delete"
  on public.post_attachments for delete
  using (
    auth.uid() = user_id
    or auth.uid() in (select id from public.profiles where role = 'admin')
  );

-- --- reports : signalements polymorphiques ----------------------------------
do $$ begin
  if not exists (select 1 from pg_type where typname = 'report_status') then
    create type report_status as enum ('pending', 'reviewed', 'resolved', 'dismissed');
  end if;
end $$;

create table if not exists public.reports (
  id bigserial primary key,
  reporter_id uuid references public.profiles(id) on delete set null,
  reportable_type text not null
    check (reportable_type in ('post', 'comment', 'blog', 'tutorial', 'project', 'user')),
  reportable_id bigint,
  reportable_uuid uuid,
  reason text not null,
  details text,
  status report_status default 'pending',
  reviewed_by uuid references public.profiles(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz default now()
);
create index if not exists idx_reports_status on public.reports(status);
create index if not exists idx_reports_target on public.reports(reportable_type, reportable_id);

alter table public.reports enable row level security;
drop policy if exists "reports_own_read" on public.reports;
create policy "reports_own_read"
  on public.reports for select
  using (
    auth.uid() = reporter_id
    or auth.uid() in (select id from public.profiles where role = 'admin')
  );
drop policy if exists "reports_auth_insert" on public.reports;
create policy "reports_auth_insert"
  on public.reports for insert
  with check (auth.uid() = reporter_id);
drop policy if exists "reports_admin_update" on public.reports;
create policy "reports_admin_update"
  on public.reports for update
  using (auth.uid() in (select id from public.profiles where role = 'admin'));

-- --- blog_post_tags : association étiquettes ↔ articles ---------------------
create table if not exists public.blog_post_tags (
  blog_post_id bigint references public.blog_posts(id) on delete cascade,
  tag_id bigint references public.tags(id) on delete cascade,
  primary key (blog_post_id, tag_id)
);
create index if not exists idx_blog_post_tags_post on public.blog_post_tags(blog_post_id);
create index if not exists idx_blog_post_tags_tag  on public.blog_post_tags(tag_id);

alter table public.blog_post_tags enable row level security;
drop policy if exists "blog_post_tags_public_read" on public.blog_post_tags;
create policy "blog_post_tags_public_read"
  on public.blog_post_tags for select using (true);

-- --- RPC : incrément atomique des vues --------------------------------------
create or replace function public.increment_post_views(p_id bigint)
returns void language sql security definer as $$
  update public.posts set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function public.increment_blog_views(p_id bigint)
returns void language sql security definer as $$
  update public.blog_posts set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function public.increment_tutorial_views(p_id bigint)
returns void language sql security definer as $$
  update public.tutorials set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function public.increment_job_views(p_id bigint)
returns void language sql security definer as $$
  update public.jobs set views = coalesce(views, 0) + 1 where id = p_id;
$$;


-- ============================================================================
-- PHASE 5 — Projets & Messagerie : liens externes, statuts étendus, RPC
-- ============================================================================

-- --- projects : github_link, demo_link et statuts élargis --------------------
alter table public.projects add column if not exists github_link text;
alter table public.projects add column if not exists demo_link text;

do $$
begin
  if exists (
    select 1 from information_schema.check_constraints
    where constraint_name = 'projects_status_check'
  ) then
    alter table public.projects drop constraint projects_status_check;
  end if;
end $$;

alter table public.projects
  add constraint projects_status_check
  check (status in ('planning', 'active', 'in_progress', 'completed', 'paused', 'archived'));

-- --- messages : index pour inbox / sent / unread ----------------------------
create index if not exists idx_messages_receiver
  on public.messages(receiver_id, created_at desc);
create index if not exists idx_messages_sender
  on public.messages(sender_id, created_at desc);
create index if not exists idx_messages_unread
  on public.messages(receiver_id, is_read) where is_read = false;

-- --- project_members : index lookups rapides --------------------------------
create index if not exists idx_project_members_project
  on public.project_members(project_id);
create index if not exists idx_project_members_user
  on public.project_members(user_id);

-- --- RPC : compte des messages non-lus --------------------------------------
create or replace function public.unread_messages_count(uid uuid)
returns integer language sql stable as $$
  select count(*)::int
  from public.messages
  where receiver_id = uid
    and is_read = false
    and is_deleted_by_receiver = false;
$$;


-- ============================================================================
-- FINI. Vérifiez dans Supabase :
--   SELECT * FROM public.profiles LIMIT 1;     -- doit contenir `points`
--   SELECT * FROM public.reports LIMIT 1;
--   SELECT increment_post_views(1);            -- ne doit pas échouer
-- ============================================================================
