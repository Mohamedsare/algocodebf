-- L’app autorise la création si role = 'admin' OU can_create_tutorial (lib/auth requirePermission).
-- L’ancienne politique RLS ne mentionnait que can_create_tutorial → les admins échouaient à l’INSERT.

drop policy if exists "tutorials_owner_write" on public.tutorials;

create policy "tutorials_owner_write" on public.tutorials
  for insert
  with check (
    auth.uid() = user_id
    and auth.uid() in (
      select id
      from public.profiles
      where role = 'admin'
         or coalesce(can_create_tutorial, false) = true
    )
  );
