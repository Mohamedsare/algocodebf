-- PostgREST « upsert » / ON CONFLICT (external_link) ne s’appuie pas sur un index unique PARTIEL.
-- On remplace l’index de 0017 par un index unique sur toute la colonne (plusieurs NULL autorisés en PostgreSQL).

drop index if exists public.idx_jobs_external_link_unique;

delete from public.jobs j
where j.id in (
  select id from (
    select id,
           row_number() over (
             partition by external_link
             order by id
           ) as rn
    from public.jobs
    where external_link is not null
      and btrim(external_link) <> ''
  ) t
  where t.rn > 1
);

create unique index if not exists idx_jobs_external_link_unique
  on public.jobs (external_link);
