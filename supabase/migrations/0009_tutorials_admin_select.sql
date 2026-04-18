-- Les admins doivent pouvoir lister toutes les formations (y compris inactives) dans la console.
create policy "tutorials_admin_select" on public.tutorials
  for select
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );
