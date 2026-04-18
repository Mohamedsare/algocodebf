-- =============================================================================
-- AlgoCodeBF - Supabase Storage setup
-- Deux buckets :
--   `uploads` : public read (avatars/, blog/, tutorials/, forum/)
--   `cvs`     : private     (accessible uniquement au propriétaire + admin
--                              + recruteur via candidature)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- Création des buckets (idempotent)
-- -----------------------------------------------------------------------------
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values
  ('uploads', 'uploads', true, 524288000,
   array['image/jpeg','image/png','image/webp','image/gif','video/mp4','video/webm','video/ogg','video/quicktime','application/pdf']),
  ('cvs', 'cvs', false, 10485760, array['application/pdf'])
on conflict (id) do update
  set public = excluded.public,
      file_size_limit = excluded.file_size_limit,
      allowed_mime_types = excluded.allowed_mime_types;

-- -----------------------------------------------------------------------------
-- Policies — bucket `uploads` (public)
-- -----------------------------------------------------------------------------
drop policy if exists "uploads_public_read" on storage.objects;
create policy "uploads_public_read"
  on storage.objects for select
  using (bucket_id = 'uploads');

-- Avatars : lecture publique OK ; écriture réservée au propriétaire
-- (path convention : avatars/{user_id}/...)
drop policy if exists "uploads_avatar_write_own" on storage.objects;
create policy "uploads_avatar_write_own"
  on storage.objects for insert to authenticated
  with check (
    bucket_id = 'uploads'
    and (
      -- avatars : seulement dans son propre dossier
      (split_part(name, '/', 1) = 'avatars' and split_part(name, '/', 2) = auth.uid()::text)
      -- blog : admin only
      or (split_part(name, '/', 1) = 'blog'
          and exists (select 1 from public.profiles p where p.id = auth.uid() and p.role = 'admin'))
      -- tutorials : créateur/admin uniquement (contrôle applicatif côté controller)
      or (split_part(name, '/', 1) = 'tutorials'
          and exists (
            select 1 from public.profiles p
            where p.id = auth.uid()
              and (p.role = 'admin' or p.can_create_tutorial = true)
          ))
      -- forum : tout membre connecté
      or (split_part(name, '/', 1) = 'forum')
    )
  );

drop policy if exists "uploads_update_own" on storage.objects;
create policy "uploads_update_own"
  on storage.objects for update to authenticated
  using (
    bucket_id = 'uploads'
    and (
      (split_part(name, '/', 1) = 'avatars' and split_part(name, '/', 2) = auth.uid()::text)
      or exists (select 1 from public.profiles p where p.id = auth.uid() and p.role = 'admin')
    )
  );

drop policy if exists "uploads_delete_own_or_admin" on storage.objects;
create policy "uploads_delete_own_or_admin"
  on storage.objects for delete to authenticated
  using (
    bucket_id = 'uploads'
    and (
      (split_part(name, '/', 1) = 'avatars' and split_part(name, '/', 2) = auth.uid()::text)
      or exists (select 1 from public.profiles p where p.id = auth.uid() and p.role = 'admin')
    )
  );

-- -----------------------------------------------------------------------------
-- Policies — bucket `cvs` (privé)
-- -----------------------------------------------------------------------------
-- Lecture : propriétaire + admin + recruteur qui a reçu une candidature pour ce cv
drop policy if exists "cvs_owner_read" on storage.objects;
create policy "cvs_owner_read"
  on storage.objects for select to authenticated
  using (
    bucket_id = 'cvs'
    and (
      split_part(name, '/', 1) = auth.uid()::text
      or exists (select 1 from public.profiles p where p.id = auth.uid() and p.role = 'admin')
      or exists (
        select 1
        from public.applications a
        join public.jobs j on j.id = a.job_id
        where a.cv_path = storage.objects.name
          and j.company_id = auth.uid()
      )
    )
  );

drop policy if exists "cvs_owner_write" on storage.objects;
create policy "cvs_owner_write"
  on storage.objects for insert to authenticated
  with check (
    bucket_id = 'cvs'
    and split_part(name, '/', 1) = auth.uid()::text
  );

drop policy if exists "cvs_owner_update" on storage.objects;
create policy "cvs_owner_update"
  on storage.objects for update to authenticated
  using (
    bucket_id = 'cvs'
    and split_part(name, '/', 1) = auth.uid()::text
  );

drop policy if exists "cvs_owner_delete" on storage.objects;
create policy "cvs_owner_delete"
  on storage.objects for delete to authenticated
  using (
    bucket_id = 'cvs'
    and (
      split_part(name, '/', 1) = auth.uid()::text
      or exists (select 1 from public.profiles p where p.id = auth.uid() and p.role = 'admin')
    )
  );
