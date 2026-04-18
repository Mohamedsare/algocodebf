-- Connexion immédiate après inscription : confirmer l’e-mail à la création du compte.
-- Évite l’erreur « Email not confirmed » si l’option Auth « Confirm email » est encore activée.
-- Pour ne plus envoyer d’e-mails de confirmation : Dashboard Supabase → Authentication →
-- Providers → Email → désactiver « Confirm email ».

create or replace function public.tg_auth_user_auto_confirm_email()
returns trigger
language plpgsql
security definer
set search_path = public, auth
as $$
begin
  if new.email_confirmed_at is null then
    new.email_confirmed_at := now();
  end if;
  return new;
end;
$$;

drop trigger if exists on_auth_user_auto_confirm_email on auth.users;

create trigger on_auth_user_auto_confirm_email
  before insert on auth.users
  for each row
  execute procedure public.tg_auth_user_auto_confirm_email();
