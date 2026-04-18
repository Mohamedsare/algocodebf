-- Admins : lister tous les projets (y compris privés / non visibles pour le public).
create policy "projects_admin_select" on public.projects
  for select
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );
