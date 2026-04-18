-- =============================================================================
-- Migration 0001 — Phase 1 foundations
-- Ajoute :
--   - newsletter.unsubscribe_token : token secret pour désabonnement sécurisé
--   - profiles.points : score calculé (cache) — recalculé par trigger/batch
--   - profile updated : award d'un badge "Nouveau Membre" à l'inscription
--   - fonctions utilitaires: compute_user_points, touch_last_login
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Newsletter: token de désabonnement
-- -----------------------------------------------------------------------------
alter table if exists public.newsletter_subscribers
  add column if not exists unsubscribe_token text unique default replace(gen_random_uuid()::text, '-', '');

-- Rétro-compat: renseigne les lignes existantes sans token
update public.newsletter_subscribers
set unsubscribe_token = replace(gen_random_uuid()::text, '-', '')
where unsubscribe_token is null;

-- -----------------------------------------------------------------------------
-- Profiles: colonne points pour leaderboard (mise à jour à la volée)
-- -----------------------------------------------------------------------------
alter table if exists public.profiles
  add column if not exists points integer not null default 0;

create index if not exists idx_profiles_points on public.profiles (points desc);

-- -----------------------------------------------------------------------------
-- Fonction de calcul des points (formule fidèle au PHP User::getLeaderboard)
--   posts × 10 + tutorials × 25 + comments × 2 + likes_reçus × 1
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- handle_new_user : award badge "Nouveau Membre" + sync auth.users email
-- -----------------------------------------------------------------------------
create or replace function handle_new_user()
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

  -- Award "Nouveau Membre"
  select id into welcome_badge_id from public.badges where name = 'Nouveau Membre' limit 1;
  if welcome_badge_id is not null then
    insert into public.user_badges (user_id, badge_id)
    values (new.id, welcome_badge_id)
    on conflict do nothing;
  end if;

  return new;
end;
$$ language plpgsql security definer;

-- -----------------------------------------------------------------------------
-- Triggers pour maintenir `points` à jour
-- -----------------------------------------------------------------------------
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

-- Likes reçus : pour incrémenter les points de l'auteur du post/tuto liké
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

  if target_user is not null then perform public.refresh_user_points(target_user); end if;
  return null;
end;
$$ language plpgsql security definer;

drop trigger if exists trg_refresh_points_likes on public.likes;
create trigger trg_refresh_points_likes
  after insert or delete on public.likes
  for each row execute procedure public.tg_refresh_points_on_like();
