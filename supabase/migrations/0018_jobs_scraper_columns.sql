-- Colonnes attendues par l’import EmploiBurkina / formulaires (déjà dans 0004_phase6_jobs.sql si toutes les migrations ont été appliquées).
-- À exécuter si PostgREST répond : colonne « company_logo » ou « skills_required » absente du cache schéma.

alter table public.jobs
  add column if not exists skills_required text,
  add column if not exists company_logo text;

alter table public.jobs
  add column if not exists is_scraped boolean default false;
