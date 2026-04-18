-- ============================================================
-- Blog Realtime — SaaS-grade
-- Ajoute blog_posts à la publication realtime (posts/comments/likes
-- sont déjà ajoutés par 0004_forum_realtime.sql).
-- ============================================================

-- REPLICA IDENTITY FULL pour recevoir les OLD rows sur UPDATE/DELETE
alter table if exists public.blog_posts replica identity full;

do $$
begin
  if not exists (
    select 1 from pg_publication_tables
    where pubname = 'supabase_realtime' and schemaname = 'public' and tablename = 'blog_posts'
  ) then
    execute 'alter publication supabase_realtime add table public.blog_posts';
  end if;
end
$$;

-- RPC pour incrémenter atomiquement les vues (évite race conditions)
create or replace function public.increment_blog_views(p_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  update public.blog_posts
     set views = coalesce(views, 0) + 1
   where id = p_id
     and status = 'published';
end;
$$;

grant execute on function public.increment_blog_views(bigint) to anon, authenticated;
