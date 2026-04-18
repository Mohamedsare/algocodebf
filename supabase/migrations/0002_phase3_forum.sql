-- ============================================================
-- Phase 3 — Forum, reports, and blog tags
-- ============================================================

-- ------------------------------------------------------------
-- post_attachments : pièces jointes d'une discussion
-- Remplace l'ancien dossier uploads/forum/ du projet PHP par
-- un stockage Supabase Storage (bucket "uploads", dossier forum/).
-- ------------------------------------------------------------
create table if not exists post_attachments (
  id bigserial primary key,
  post_id bigint not null references posts(id) on delete cascade,
  user_id uuid references profiles(id) on delete set null,
  file_path text not null,
  original_name text,
  mime_type text,
  file_size bigint,
  created_at timestamptz default now()
);
create index if not exists idx_post_attachments_post on post_attachments(post_id);

alter table post_attachments enable row level security;
drop policy if exists "post_attachments_public_read" on post_attachments;
create policy "post_attachments_public_read"
  on post_attachments for select using (true);
drop policy if exists "post_attachments_owner_write" on post_attachments;
create policy "post_attachments_owner_write"
  on post_attachments for insert
  with check (auth.uid() = user_id);
drop policy if exists "post_attachments_owner_delete" on post_attachments;
create policy "post_attachments_owner_delete"
  on post_attachments for delete
  using (
    auth.uid() = user_id
    or auth.uid() in (select id from profiles where role = 'admin')
  );

-- ------------------------------------------------------------
-- reports : signalements (posts, comments, blog posts, etc.)
-- ------------------------------------------------------------
do $$ begin
  if not exists (select 1 from pg_type where typname = 'report_status') then
    create type report_status as enum ('pending', 'reviewed', 'resolved', 'dismissed');
  end if;
end $$;

create table if not exists reports (
  id bigserial primary key,
  reporter_id uuid references profiles(id) on delete set null,
  reportable_type text not null
    check (reportable_type in ('post', 'comment', 'blog', 'tutorial', 'project', 'user')),
  reportable_id bigint,                -- bigint pour les entités numérotées
  reportable_uuid uuid,                -- uuid pour 'user'
  reason text not null,
  details text,
  status report_status default 'pending',
  reviewed_by uuid references profiles(id) on delete set null,
  reviewed_at timestamptz,
  created_at timestamptz default now()
);
create index if not exists idx_reports_status on reports(status);
create index if not exists idx_reports_target on reports(reportable_type, reportable_id);

alter table reports enable row level security;
drop policy if exists "reports_own_read" on reports;
create policy "reports_own_read"
  on reports for select
  using (
    auth.uid() = reporter_id
    or auth.uid() in (select id from profiles where role = 'admin')
  );
drop policy if exists "reports_auth_insert" on reports;
create policy "reports_auth_insert"
  on reports for insert
  with check (auth.uid() = reporter_id);
drop policy if exists "reports_admin_update" on reports;
create policy "reports_admin_update"
  on reports for update
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- ------------------------------------------------------------
-- blog_post_tags : étiquettes pour les articles de blog
-- (simple association sans RLS stricte, lecture publique).
-- ------------------------------------------------------------
create table if not exists blog_post_tags (
  blog_post_id bigint references blog_posts(id) on delete cascade,
  tag_id bigint references tags(id) on delete cascade,
  primary key (blog_post_id, tag_id)
);
create index if not exists idx_blog_post_tags_post on blog_post_tags(blog_post_id);
create index if not exists idx_blog_post_tags_tag on blog_post_tags(tag_id);
alter table blog_post_tags enable row level security;
drop policy if exists "blog_post_tags_public_read" on blog_post_tags;
create policy "blog_post_tags_public_read"
  on blog_post_tags for select using (true);

-- ------------------------------------------------------------
-- RPC : incrément atomique des vues pour posts/blog/tutorials
-- (évite la course à la mise à jour côté client).
-- ------------------------------------------------------------
create or replace function increment_post_views(p_id bigint)
returns void language sql security definer as $$
  update posts set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function increment_blog_views(p_id bigint)
returns void language sql security definer as $$
  update blog_posts set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function increment_tutorial_views(p_id bigint)
returns void language sql security definer as $$
  update tutorials set views = coalesce(views, 0) + 1 where id = p_id;
$$;

create or replace function increment_job_views(p_id bigint)
returns void language sql security definer as $$
  update jobs set views = coalesce(views, 0) + 1 where id = p_id;
$$;
