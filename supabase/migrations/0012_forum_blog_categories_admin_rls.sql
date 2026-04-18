-- ============================================================
-- Catégories forum & blog : RLS cohérent avec la console admin
-- Lecture publique ; INSERT/UPDATE/DELETE réservés aux admins.
-- Corrige : "new row violates row-level security policy for table forum_categories"
-- ============================================================

-- ---------- forum_categories ----------
alter table public.forum_categories enable row level security;

drop policy if exists "forum_categories_public_read" on public.forum_categories;
create policy "forum_categories_public_read"
  on public.forum_categories
  for select
  using (true);

drop policy if exists "forum_categories_admin_insert" on public.forum_categories;
create policy "forum_categories_admin_insert"
  on public.forum_categories
  for insert
  with check (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );

drop policy if exists "forum_categories_admin_update" on public.forum_categories;
create policy "forum_categories_admin_update"
  on public.forum_categories
  for update
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  )
  with check (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );

drop policy if exists "forum_categories_admin_delete" on public.forum_categories;
create policy "forum_categories_admin_delete"
  on public.forum_categories
  for delete
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );

-- ---------- blog_categories (même modèle que l’admin) ----------
alter table public.blog_categories enable row level security;

drop policy if exists "blog_categories_public_read" on public.blog_categories;
create policy "blog_categories_public_read"
  on public.blog_categories
  for select
  using (true);

drop policy if exists "blog_categories_admin_insert" on public.blog_categories;
create policy "blog_categories_admin_insert"
  on public.blog_categories
  for insert
  with check (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );

drop policy if exists "blog_categories_admin_update" on public.blog_categories;
create policy "blog_categories_admin_update"
  on public.blog_categories
  for update
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  )
  with check (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );

drop policy if exists "blog_categories_admin_delete" on public.blog_categories;
create policy "blog_categories_admin_delete"
  on public.blog_categories
  for delete
  using (
    auth.uid() in (select id from public.profiles where role = 'admin')
  );
