-- ============================================================
-- Forum Realtime — SaaS-grade
-- Active Supabase Realtime sur posts/comments/likes + RPC views
-- ============================================================

-- 1. REPLICA IDENTITY FULL pour obtenir les OLD rows sur UPDATE/DELETE
--    (nécessaire pour que les événements realtime contiennent les anciens champs)
alter table if exists public.posts replica identity full;
alter table if exists public.comments replica identity full;
alter table if exists public.likes replica identity full;

-- 2. Ajout des tables à la publication supabase_realtime
--    On utilise DO pour éviter l'erreur si la table est déjà membre.
do $$
begin
  if not exists (
    select 1 from pg_publication_tables
    where pubname = 'supabase_realtime' and schemaname = 'public' and tablename = 'posts'
  ) then
    execute 'alter publication supabase_realtime add table public.posts';
  end if;

  if not exists (
    select 1 from pg_publication_tables
    where pubname = 'supabase_realtime' and schemaname = 'public' and tablename = 'comments'
  ) then
    execute 'alter publication supabase_realtime add table public.comments';
  end if;

  if not exists (
    select 1 from pg_publication_tables
    where pubname = 'supabase_realtime' and schemaname = 'public' and tablename = 'likes'
  ) then
    execute 'alter publication supabase_realtime add table public.likes';
  end if;
end
$$;

-- 3. RPC pour incrémenter les vues d'un post (bypass RLS via security definer)
create or replace function public.increment_post_views(p_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  update public.posts
     set views = coalesce(views, 0) + 1
   where id = p_id
     and status = 'active';
end;
$$;

grant execute on function public.increment_post_views(bigint) to anon, authenticated;
