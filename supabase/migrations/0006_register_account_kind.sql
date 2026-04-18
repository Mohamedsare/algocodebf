-- Types d'inscription : étudiant, professionnel, entreprise (role company pour les entreprises)
-- -----------------------------------------------------------------------------

alter table public.profiles add column if not exists account_kind text;
alter table public.profiles add column if not exists organization_name text;
alter table public.profiles add column if not exists job_title text;

update public.profiles
set account_kind = case when role = 'company' then 'enterprise' else 'student' end
where account_kind is null;

alter table public.profiles alter column account_kind set default 'student';
alter table public.profiles alter column account_kind set not null;

alter table public.profiles drop constraint if exists profiles_account_kind_check;
alter table public.profiles
  add constraint profiles_account_kind_check
  check (account_kind in ('student', 'professional', 'enterprise'));

-- ---------------------------------------------------------------------------
-- Trigger inscription : profil + badge + rôle entreprise si besoin
-- ---------------------------------------------------------------------------
create or replace function public.handle_new_user()
returns trigger as $$
declare
  welcome_badge_id bigint;
  v_kind text;
  v_role text;
begin
  v_kind := coalesce(nullif(trim(new.raw_user_meta_data->>'account_kind'), ''), 'student');
  if v_kind not in ('student', 'professional', 'enterprise') then
    v_kind := 'student';
  end if;

  if v_kind = 'enterprise' then
    v_role := 'company';
  else
    v_role := 'user';
  end if;

  insert into public.profiles (
    id, prenom, nom, phone, university, faculty, city, role,
    account_kind, organization_name, job_title
  )
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'prenom', 'Utilisateur'),
    coalesce(new.raw_user_meta_data->>'nom', ''),
    new.raw_user_meta_data->>'phone',
    new.raw_user_meta_data->>'university',
    new.raw_user_meta_data->>'faculty',
    new.raw_user_meta_data->>'city',
    v_role,
    v_kind,
    nullif(trim(new.raw_user_meta_data->>'organization_name'), ''),
    nullif(trim(new.raw_user_meta_data->>'job_title'), '')
  )
  on conflict (id) do nothing;

  select id into welcome_badge_id from public.badges where name = 'Nouveau Membre' limit 1;
  if welcome_badge_id is not null then
    insert into public.user_badges (user_id, badge_id)
    values (new.id, welcome_badge_id)
    on conflict (user_id, badge_id) do nothing;
  end if;

  return new;
end;
$$ language plpgsql security definer;
