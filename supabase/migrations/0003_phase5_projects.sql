-- ============================================================
-- Phase 5 — Projects & Messages : colonnes manquantes & workflow
-- ============================================================

-- Les projets PHP avaient github_link et demo_link, pas encore dans le schéma.
alter table projects add column if not exists github_link text;
alter table projects add column if not exists demo_link text;

-- On autorise les statuts étendus utilisés par le PHP (planning/in_progress/paused).
-- Le check contraint est remplacé pour ajouter ces valeurs.
do $$
begin
  if exists (
    select 1 from information_schema.check_constraints
    where constraint_name = 'projects_status_check'
  ) then
    alter table projects drop constraint projects_status_check;
  end if;
end $$;
alter table projects
  add constraint projects_status_check
  check (status in ('planning', 'active', 'in_progress', 'completed', 'paused', 'archived'));

-- Messages : on autorise une action de type project_join_request / project_invite.
-- action_type est déjà text libre, pas besoin de contraindre.
create index if not exists idx_messages_receiver on messages(receiver_id, created_at desc);
create index if not exists idx_messages_sender on messages(sender_id, created_at desc);
create index if not exists idx_messages_unread on messages(receiver_id, is_read) where is_read = false;

-- project_members : index pour lookup rapide.
create index if not exists idx_project_members_project on project_members(project_id);
create index if not exists idx_project_members_user on project_members(user_id);

-- RPC utilitaire : compte des messages non-lus pour un user.
create or replace function unread_messages_count(uid uuid)
returns integer language sql stable as $$
  select count(*)::int
  from messages
  where receiver_id = uid
    and is_read = false
    and is_deleted_by_receiver = false;
$$;
