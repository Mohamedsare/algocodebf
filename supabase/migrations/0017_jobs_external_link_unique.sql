-- Index unique sur les liens externes : upsert idempotent (import scraper) sans doublons.
-- Supprime les doublons existants (garde l'id le plus petit par URL).

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
  on public.jobs (external_link)
  where external_link is not null
    and btrim(external_link) <> '';
