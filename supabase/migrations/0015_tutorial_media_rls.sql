-- RLS : lecture publique seule n’autorisait pas insert/delete sur
-- tutorial_videos, tutorial_chapters, tutorial_tags.

create policy "tutorial_videos_owner_insert" on public.tutorial_videos
  for insert
  with check (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_videos_owner_delete" on public.tutorial_videos
  for delete
  using (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_chapters_owner_insert" on public.tutorial_chapters
  for insert
  with check (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_chapters_owner_delete" on public.tutorial_chapters
  for delete
  using (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_tags_owner_insert" on public.tutorial_tags
  for insert
  with check (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_tags_owner_update" on public.tutorial_tags
  for update
  using (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  )
  with check (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );

create policy "tutorial_tags_owner_delete" on public.tutorial_tags
  for delete
  using (
    exists (
      select 1
      from public.tutorials t
      where t.id = tutorial_id
        and (
          t.user_id = auth.uid()
          or auth.uid() in (select id from public.profiles where role = 'admin')
        )
    )
  );
