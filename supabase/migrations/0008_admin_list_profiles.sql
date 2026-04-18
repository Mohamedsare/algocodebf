-- Liste admin des profils avec e-mail (auth.users), réservée aux rôles admin.
-- Équivalent des données exposées par getUsersData côté PHP.

create or replace function public.admin_list_profiles_for_console(
  search_q text default null,
  role_filter text default null,
  status_filter text default null,
  max_rows int default 100
)
returns table (
  id uuid,
  email text,
  email_verified boolean,
  prenom text,
  nom text,
  phone text,
  university text,
  faculty text,
  city text,
  bio text,
  photo_path text,
  cv_path text,
  role text,
  status text,
  can_create_tutorial boolean,
  can_create_project boolean,
  last_login timestamptz,
  points integer,
  created_at timestamptz,
  updated_at timestamptz,
  account_kind text,
  organization_name text,
  job_title text
)
language plpgsql
security definer
set search_path = public
as $$
begin
  if not exists (
    select 1 from public.profiles me
    where me.id = auth.uid() and me.role = 'admin'
  ) then
    raise exception 'Accès refusé' using errcode = '42501';
  end if;

  return query
  select
    p.id,
    coalesce(au.email::text, '') as email,
    (au.email_confirmed_at is not null) as email_verified,
    p.prenom,
    p.nom,
    p.phone,
    p.university,
    p.faculty,
    p.city,
    p.bio,
    p.photo_path,
    p.cv_path,
    p.role::text,
    p.status::text,
    p.can_create_tutorial,
    p.can_create_project,
    p.last_login,
    coalesce(p.points, 0)::integer,
    p.created_at,
    p.updated_at,
    p.account_kind::text,
    p.organization_name,
    p.job_title
  from public.profiles p
  join auth.users au on au.id = p.id
  where
    (search_q is null or trim(search_q) = '' or
      p.prenom ilike '%' || trim(search_q) || '%' or
      p.nom ilike '%' || trim(search_q) || '%' or
      p.university ilike '%' || trim(search_q) || '%' or
      au.email ilike '%' || trim(search_q) || '%' or
      p.city ilike '%' || trim(search_q) || '%'
    )
    and (role_filter is null or trim(role_filter) = '' or p.role::text = trim(role_filter))
    and (status_filter is null or trim(status_filter) = '' or p.status::text = trim(status_filter))
  order by p.created_at desc
  limit least(coalesce(nullif(max_rows, 0), 100), 500);
end;
$$;

revoke all on function public.admin_list_profiles_for_console(text, text, text, int) from public;
grant execute on function public.admin_list_profiles_for_console(text, text, text, int) to authenticated;
