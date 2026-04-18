-- Permet aux admins de lister tous les commentaires (y compris supprimés) pour la modération.
drop policy if exists "comments_admin_read" on public.comments;
create policy "comments_admin_read"
  on public.comments for select
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );
