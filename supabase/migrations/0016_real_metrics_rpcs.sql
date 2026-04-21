-- Compteurs fiables : les UPDATE directs échouaient (RLS). RPC security definer
-- + resynchronisation des likes dénormalisés.

create or replace function public.increment_tutorial_views(p_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.tutorials
  set views = coalesce(views, 0) + 1
  where id = p_id and status = 'active';
$$;

create or replace function public.increment_job_views(p_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.jobs
  set views = coalesce(views, 0) + 1
  where id = p_id and status = 'active';
$$;

create or replace function public.increment_tutorial_video_views(p_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.tutorial_videos
  set views = coalesce(views, 0) + 1
  where id = p_id;
$$;

create or replace function public.sync_tutorial_likes_count(p_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.tutorials
  set likes_count = (
    select count(*)::integer
    from public.likes
    where likeable_type = 'tutorial' and likeable_id = p_id
  )
  where id = p_id;
$$;

create or replace function public.sync_blog_likes_count(p_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.blog_posts
  set likes_count = (
    select count(*)::integer
    from public.likes
    where likeable_type = 'blog' and likeable_id = p_id
  )
  where id = p_id;
$$;

-- Cohérence des compteurs existants (dénormalisés vs table likes)
update public.tutorials t
set likes_count = coalesce((
  select count(*)::integer from public.likes l
  where l.likeable_type = 'tutorial' and l.likeable_id = t.id
), 0);

update public.blog_posts p
set likes_count = coalesce((
  select count(*)::integer from public.likes l
  where l.likeable_type = 'blog' and l.likeable_id = p.id
), 0);

grant execute on function public.increment_tutorial_views(bigint) to anon, authenticated;
grant execute on function public.increment_job_views(bigint) to anon, authenticated;
grant execute on function public.increment_tutorial_video_views(bigint) to anon, authenticated;
grant execute on function public.sync_tutorial_likes_count(bigint) to anon, authenticated;
grant execute on function public.sync_blog_likes_count(bigint) to anon, authenticated;
