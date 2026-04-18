-- ============================================================
-- Phase 6 — Jobs : alignement fidèle avec le schéma PHP
-- Ajoute les colonnes skills_required, company_logo et élargit
-- la liste des types (stage, emploi, freelance, hackathon, formation)
-- ============================================================

-- Nouvelles colonnes
alter table public.jobs
  add column if not exists skills_required text,
  add column if not exists company_logo text;

-- Élargit la contrainte de type pour accepter les libellés FR du PHP
do $$
begin
  -- Supprime l'ancienne contrainte si elle existe
  if exists (
    select 1 from pg_constraint where conname = 'jobs_type_check'
  ) then
    alter table public.jobs drop constraint jobs_type_check;
  end if;

  alter table public.jobs
    add constraint jobs_type_check
    check (type in (
      'job', 'internship', 'hackathon',
      'stage', 'emploi', 'freelance', 'formation'
    ));
end$$;

-- Indice supplémentaire pour la recherche par ville
create index if not exists idx_jobs_city on public.jobs(city);
