-- ============================================================
-- AlgoCodeBF - Supabase PostgreSQL Schema
-- Community platform for Burkina Faso developers & students
-- ============================================================

-- Extensions
create extension if not exists "uuid-ossp";
create extension if not exists "pg_trgm";
create extension if not exists "unaccent";

-- ============================================================
-- PROFILES (extends auth.users)
-- ============================================================
create table if not exists profiles (
  id uuid references auth.users(id) on delete cascade primary key,
  prenom text not null,
  nom text not null,
  phone text unique,
  university text,
  faculty text,
  city text,
  bio text,
  photo_path text,
  cv_path text,
  role text not null default 'user' check (role in ('user', 'company', 'admin')),
  status text not null default 'active' check (status in ('active', 'inactive', 'suspended')),
  can_create_tutorial boolean default false,
  can_create_project boolean default false,
  last_login timestamptz,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

-- Trigger: auto-create profile on auth.users insert
create or replace function handle_new_user()
returns trigger as $$
begin
  insert into profiles (id, prenom, nom, phone, university, faculty, city, role)
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'prenom', 'Utilisateur'),
    coalesce(new.raw_user_meta_data->>'nom', ''),
    new.raw_user_meta_data->>'phone',
    new.raw_user_meta_data->>'university',
    new.raw_user_meta_data->>'faculty',
    new.raw_user_meta_data->>'city',
    coalesce(new.raw_user_meta_data->>'role', 'user')
  );
  return new;
end;
$$ language plpgsql security definer;

-- Confirmer l’e-mail à l’inscription (pas de blocage à la connexion)
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
  for each row execute procedure public.tg_auth_user_auto_confirm_email();

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
  after insert on auth.users
  for each row execute procedure handle_new_user();

-- Trigger: update updated_at timestamp
create or replace function update_updated_at_column()
returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

create trigger update_profiles_updated_at
  before update on profiles
  for each row execute procedure update_updated_at_column();

-- ============================================================
-- SKILLS
-- ============================================================
create table if not exists skills (
  id bigserial primary key,
  name text not null unique,
  category text,
  created_at timestamptz default now()
);

create table if not exists user_skills (
  user_id uuid references profiles(id) on delete cascade,
  skill_id bigint references skills(id) on delete cascade,
  level text check (level in ('beginner', 'intermediate', 'advanced')),
  primary key (user_id, skill_id)
);

-- ============================================================
-- BADGES
-- ============================================================
create table if not exists badges (
  id bigserial primary key,
  name text not null,
  description text,
  icon text,
  created_at timestamptz default now()
);

create table if not exists user_badges (
  user_id uuid references profiles(id) on delete cascade,
  badge_id bigint references badges(id) on delete cascade,
  awarded_at timestamptz default now(),
  primary key (user_id, badge_id)
);

-- Default "new member" badge
insert into badges (name, description, icon) values
  ('Nouveau Membre', 'Membre récemment inscrit sur AlgoCodeBF', '🎉'),
  ('Contributeur', 'A publié au moins 5 discussions', '✍️'),
  ('Tuteur', 'A créé au moins un tutoriel', '🎓'),
  ('Innovateur', 'A lancé un projet collaboratif', '🚀'),
  ('Mentor', 'A reçu plus de 50 likes', '⭐')
on conflict do nothing;

-- ============================================================
-- FOLLOWS
-- ============================================================
create table if not exists follows (
  follower_id uuid references profiles(id) on delete cascade,
  following_id uuid references profiles(id) on delete cascade,
  created_at timestamptz default now(),
  primary key (follower_id, following_id),
  check (follower_id != following_id)
);

-- ============================================================
-- BLOG
-- ============================================================
create table if not exists blog_categories (
  id bigserial primary key,
  name text not null,
  slug text not null unique,
  description text
);

insert into blog_categories (name, slug, description) values
  ('Actualités', 'actualites', 'Nouvelles du monde tech au Burkina Faso'),
  ('Tutoriels', 'tutoriels', 'Guides pratiques et tutoriels'),
  ('Carrière', 'carriere', 'Emploi, conseils et opportunités'),
  ('Startups', 'startups', 'Écosystème startup burkinabè'),
  ('Événements', 'evenements', 'Conférences, hackathons et meetups')
on conflict do nothing;

create table if not exists blog_posts (
  id bigserial primary key,
  author_id uuid references profiles(id) on delete set null,
  title text not null,
  slug text not null unique,
  excerpt text,
  content text,
  featured_image text,
  category text,
  views integer default 0,
  likes_count integer default 0,
  comments_count integer default 0,
  status text default 'draft' check (status in ('draft', 'published', 'archived')),
  published_at timestamptz,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_blog_posts_updated_at
  before update on blog_posts
  for each row execute procedure update_updated_at_column();

-- ============================================================
-- TAGS
-- ============================================================
create table if not exists tags (
  id bigserial primary key,
  name text not null unique
);

-- ============================================================
-- TUTORIALS
-- ============================================================
create table if not exists tutorials (
  id bigserial primary key,
  user_id uuid references profiles(id) on delete set null,
  title text not null,
  description text,
  content text,
  thumbnail text,
  category text,
  type text check (type in ('video', 'text', 'mixed')) default 'video',
  level text check (level in ('beginner', 'intermediate', 'advanced')) default 'beginner',
  views integer default 0,
  likes_count integer default 0,
  status text default 'active' check (status in ('active', 'inactive')),
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_tutorials_updated_at
  before update on tutorials
  for each row execute procedure update_updated_at_column();

create table if not exists tutorial_videos (
  id bigserial primary key,
  tutorial_id bigint references tutorials(id) on delete cascade,
  title text,
  description text,
  file_path text,
  file_name text,
  file_size bigint,
  duration integer,
  order_index integer default 0,
  views integer default 0,
  created_at timestamptz default now()
);

create table if not exists tutorial_chapters (
  id bigserial primary key,
  tutorial_id bigint references tutorials(id) on delete cascade,
  chapter_number integer,
  title text,
  description text,
  video_id bigint references tutorial_videos(id) on delete set null,
  order_index integer default 0,
  created_at timestamptz default now()
);

create table if not exists tutorial_tags (
  tutorial_id bigint references tutorials(id) on delete cascade,
  tag_id bigint references tags(id) on delete cascade,
  primary key (tutorial_id, tag_id)
);

-- ============================================================
-- FORUM / POSTS
-- ============================================================
create table if not exists forum_categories (
  id bigserial primary key,
  name text not null,
  slug text not null unique,
  description text
);

insert into forum_categories (name, slug, description) values
  ('Programmation', 'programmation', 'Questions et discussions sur la programmation'),
  ('Projets', 'projets', 'Présentation et collaboration sur des projets'),
  ('Carrière', 'carriere', 'Emploi, stages et conseils professionnels'),
  ('Actualités Tech', 'actualites-tech', 'Nouvelles technologiques'),
  ('Aide & Support', 'aide-support', 'Demandes d''aide et support communautaire')
on conflict do nothing;

create table if not exists posts (
  id bigserial primary key,
  user_id uuid references profiles(id) on delete set null,
  title text not null,
  body text not null,
  category text,
  views integer default 0,
  is_pinned boolean default false,
  status text default 'active' check (status in ('active', 'inactive')),
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_posts_updated_at
  before update on posts
  for each row execute procedure update_updated_at_column();

-- ============================================================
-- COMMENTS (polymorphic)
-- ============================================================
create table if not exists comments (
  id bigserial primary key,
  user_id uuid references profiles(id) on delete set null,
  commentable_type text not null check (commentable_type in ('post', 'tutorial', 'blog', 'project')),
  commentable_id bigint not null,
  body text not null,
  status text default 'active' check (status in ('active', 'deleted')),
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_comments_updated_at
  before update on comments
  for each row execute procedure update_updated_at_column();

-- ============================================================
-- LIKES (polymorphic)
-- ============================================================
create table if not exists likes (
  id bigserial primary key,
  user_id uuid references profiles(id) on delete cascade,
  likeable_type text not null check (likeable_type in ('post', 'comment', 'tutorial', 'blog', 'project')),
  likeable_id bigint not null,
  created_at timestamptz default now(),
  unique (user_id, likeable_type, likeable_id)
);

-- ============================================================
-- JOBS / OPPORTUNITIES
-- ============================================================
create table if not exists jobs (
  id bigserial primary key,
  company_id uuid references profiles(id) on delete set null,
  company_name text,
  title text not null,
  description text,
  type text check (type in ('job', 'internship', 'hackathon')) default 'job',
  city text,
  salary text,
  deadline date,
  status text default 'active' check (status in ('active', 'expired', 'closed')),
  views integer default 0,
  external_link text,
  is_scraped boolean default false,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_jobs_updated_at
  before update on jobs
  for each row execute procedure update_updated_at_column();

create table if not exists applications (
  id bigserial primary key,
  job_id bigint references jobs(id) on delete cascade,
  user_id uuid references profiles(id) on delete cascade,
  cover_letter text,
  cv_path text,
  status text default 'pending' check (status in ('pending', 'accepted', 'rejected')),
  created_at timestamptz default now(),
  unique (job_id, user_id)
);

-- ============================================================
-- PROJECTS
-- ============================================================
create table if not exists projects (
  id bigserial primary key,
  owner_id uuid references profiles(id) on delete set null,
  title text not null,
  description text,
  status text default 'active' check (status in ('active', 'completed', 'archived')),
  visibility text default 'public' check (visibility in ('public', 'private')),
  looking_for_members boolean default false,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create trigger update_projects_updated_at
  before update on projects
  for each row execute procedure update_updated_at_column();

create table if not exists project_members (
  id bigserial primary key,
  project_id bigint references projects(id) on delete cascade,
  user_id uuid references profiles(id) on delete cascade,
  role text default 'Contributeur',
  status text default 'pending' check (status in ('pending', 'active', 'rejected', 'left')),
  joined_at timestamptz,
  created_at timestamptz default now(),
  unique (project_id, user_id)
);

-- ============================================================
-- MESSAGES
-- ============================================================
create table if not exists messages (
  id bigserial primary key,
  sender_id uuid references profiles(id) on delete set null,
  receiver_id uuid references profiles(id) on delete set null,
  subject text,
  body text not null,
  action_type text,
  action_data jsonb,
  action_status text check (action_status in ('pending', 'accepted', 'rejected', 'cancelled')),
  is_read boolean default false,
  is_deleted_by_sender boolean default false,
  is_deleted_by_receiver boolean default false,
  created_at timestamptz default now()
);

-- ============================================================
-- NEWSLETTER
-- ============================================================
create table if not exists newsletter_subscribers (
  id bigserial primary key,
  email text not null unique,
  status text default 'active' check (status in ('active', 'unsubscribed', 'bounced')),
  ip_address text,
  user_agent text,
  subscribed_at timestamptz default now(),
  unsubscribed_at timestamptz,
  last_sent_at timestamptz,
  total_sent integer default 0
);

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
create table if not exists system_settings (
  id bigserial primary key,
  key text not null unique,
  value text,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

insert into system_settings (key, value) values
  ('site_name', 'AlgoCodeBF'),
  ('site_description', 'La plateforme tech du Burkina Faso'),
  ('maintenance_mode', 'false'),
  ('allow_registration', 'true'),
  ('default_user_role', 'user')
on conflict do nothing;

-- ============================================================
-- PERFORMANCE INDEXES
-- ============================================================

-- Profiles
create index if not exists idx_profiles_role on profiles(role);
create index if not exists idx_profiles_status on profiles(status);
create index if not exists idx_profiles_created_at on profiles(created_at desc);

-- Blog posts
create index if not exists idx_blog_posts_status on blog_posts(status);
create index if not exists idx_blog_posts_author on blog_posts(author_id);
create index if not exists idx_blog_posts_slug on blog_posts(slug);
create index if not exists idx_blog_posts_created_at on blog_posts(created_at desc);
create index if not exists idx_blog_posts_category on blog_posts(category);

-- Tutorials
create index if not exists idx_tutorials_status on tutorials(status);
create index if not exists idx_tutorials_user on tutorials(user_id);
create index if not exists idx_tutorials_created_at on tutorials(created_at desc);
create index if not exists idx_tutorials_category on tutorials(category);
create index if not exists idx_tutorials_type on tutorials(type);
create index if not exists idx_tutorials_level on tutorials(level);

-- Posts (forum)
create index if not exists idx_posts_status on posts(status);
create index if not exists idx_posts_user on posts(user_id);
create index if not exists idx_posts_created_at on posts(created_at desc);
create index if not exists idx_posts_pinned on posts(is_pinned);

-- Comments
create index if not exists idx_comments_commentable on comments(commentable_type, commentable_id);
create index if not exists idx_comments_user on comments(user_id);
create index if not exists idx_comments_status on comments(status);

-- Likes
create index if not exists idx_likes_likeable on likes(likeable_type, likeable_id);
create index if not exists idx_likes_user on likes(user_id);

-- Jobs
create index if not exists idx_jobs_status on jobs(status);
create index if not exists idx_jobs_type on jobs(type);
create index if not exists idx_jobs_created_at on jobs(created_at desc);
create index if not exists idx_jobs_deadline on jobs(deadline);

-- Projects
create index if not exists idx_projects_owner on projects(owner_id);
create index if not exists idx_projects_visibility on projects(visibility);
create index if not exists idx_projects_status on projects(status);
create index if not exists idx_projects_looking on projects(looking_for_members);

-- Messages
create index if not exists idx_messages_receiver on messages(receiver_id, is_read);
create index if not exists idx_messages_sender on messages(sender_id);
create index if not exists idx_messages_created_at on messages(created_at desc);

-- Full-text search indexes (using pg_trgm)
create index if not exists idx_blog_posts_title_trgm on blog_posts using gin (title gin_trgm_ops);
create index if not exists idx_tutorials_title_trgm on tutorials using gin (title gin_trgm_ops);
create index if not exists idx_posts_title_trgm on posts using gin (title gin_trgm_ops);
create index if not exists idx_profiles_name_trgm on profiles using gin ((prenom || ' ' || nom) gin_trgm_ops);

-- ============================================================
-- ROW LEVEL SECURITY (RLS)
-- ============================================================

alter table profiles enable row level security;
alter table user_skills enable row level security;
alter table user_badges enable row level security;
alter table follows enable row level security;
alter table blog_posts enable row level security;
alter table tutorials enable row level security;
alter table tutorial_videos enable row level security;
alter table tutorial_chapters enable row level security;
alter table tutorial_tags enable row level security;
alter table posts enable row level security;
alter table comments enable row level security;
alter table likes enable row level security;
alter table jobs enable row level security;
alter table applications enable row level security;
alter table projects enable row level security;
alter table project_members enable row level security;
alter table messages enable row level security;
alter table newsletter_subscribers enable row level security;

-- Profiles: public read, self-write, admin-all
create policy "profiles_public_read" on profiles for select using (true);
create policy "profiles_self_update" on profiles for update using (auth.uid() = id);
create policy "profiles_self_insert" on profiles for insert with check (auth.uid() = id);

-- Blog posts: public read published, admin all
create policy "blog_posts_public_read" on blog_posts for select
  using (status = 'published' or auth.uid() in (select id from profiles where role = 'admin'));
create policy "blog_posts_admin_all" on blog_posts for all
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- Tutorials: public read active
create policy "tutorials_public_read" on tutorials for select using (status = 'active');
create policy "tutorials_owner_write" on tutorials for insert
  with check (
    auth.uid() = user_id
    and auth.uid() in (
      select id from profiles
      where role = 'admin'
         or coalesce(can_create_tutorial, false) = true
    )
  );
create policy "tutorials_owner_update" on tutorials for update
  using (auth.uid() = user_id or auth.uid() in (select id from profiles where role = 'admin'));
create policy "tutorials_admin_select" on tutorials for select
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- Posts: public read active
create policy "posts_public_read" on posts for select using (status = 'active');
create policy "posts_auth_insert" on posts for insert with check (auth.uid() = user_id);
create policy "posts_owner_update" on posts for update
  using (auth.uid() = user_id or auth.uid() in (select id from profiles where role = 'admin'));

-- Comments: public read active, auth insert, owner/admin update
create policy "comments_public_read" on comments for select using (status = 'active');
create policy "comments_auth_insert" on comments for insert with check (auth.uid() = user_id);
create policy "comments_owner_update" on comments for update
  using (auth.uid() = user_id or auth.uid() in (select id from profiles where role = 'admin'));

-- Likes: public read, auth insert, self delete
create policy "likes_public_read" on likes for select using (true);
create policy "likes_auth_insert" on likes for insert with check (auth.uid() = user_id);
create policy "likes_self_delete" on likes for delete using (auth.uid() = user_id);

-- Jobs: public read active
create policy "jobs_public_read" on jobs for select using (status = 'active');
create policy "jobs_company_insert" on jobs for insert
  with check (auth.uid() = company_id or auth.uid() in (select id from profiles where role = 'admin'));
create policy "jobs_admin_all" on jobs for all
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- Applications: own reads, auth insert
create policy "applications_own_read" on applications for select
  using (auth.uid() = user_id or auth.uid() in (
    select company_id from jobs where id = job_id
  ));
create policy "applications_auth_insert" on applications for insert
  with check (auth.uid() = user_id);

-- Projects: public read public projects
create policy "projects_public_read" on projects for select
  using (visibility = 'public' or auth.uid() = owner_id or auth.uid() in (
    select user_id from project_members where project_id = id and status = 'active'
  ));
create policy "projects_auth_insert" on projects for insert
  with check (auth.uid() = owner_id and auth.uid() in (select id from profiles where can_create_project = true));
create policy "projects_owner_update" on projects for update
  using (auth.uid() = owner_id or auth.uid() in (select id from profiles where role = 'admin'));
create policy "projects_admin_select" on projects for select
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- Project members
create policy "project_members_read" on project_members for select using (true);
create policy "project_members_auth_insert" on project_members for insert
  with check (auth.uid() = user_id);
create policy "project_members_owner_update" on project_members for update
  using (auth.uid() in (
    select owner_id from projects where id = project_id
  ) or auth.uid() in (select id from profiles where role = 'admin'));

-- Messages: own read
create policy "messages_own_read" on messages for select
  using (auth.uid() = sender_id or auth.uid() = receiver_id);
create policy "messages_auth_insert" on messages for insert
  with check (auth.uid() = sender_id);
create policy "messages_own_update" on messages for update
  using (auth.uid() = sender_id or auth.uid() = receiver_id);

-- Follows: public read, auth insert/delete
create policy "follows_public_read" on follows for select using (true);
create policy "follows_auth_insert" on follows for insert with check (auth.uid() = follower_id);
create policy "follows_self_delete" on follows for delete using (auth.uid() = follower_id);

-- Newsletter: insert only for anonymous
create policy "newsletter_insert" on newsletter_subscribers for insert with check (true);
create policy "newsletter_admin_read" on newsletter_subscribers for select
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- User skills/badges: public read, self write
create policy "user_skills_public_read" on user_skills for select using (true);
create policy "user_skills_self_write" on user_skills for all using (auth.uid() = user_id);
create policy "user_badges_public_read" on user_badges for select using (true);
create policy "user_badges_admin_write" on user_badges for all
  using (auth.uid() in (select id from profiles where role = 'admin'));

-- Tutorial videos/chapters: public read
create policy "tutorial_videos_public_read" on tutorial_videos for select using (true);
create policy "tutorial_chapters_public_read" on tutorial_chapters for select using (true);
create policy "tutorial_tags_public_read" on tutorial_tags for select using (true);

-- ============================================================
-- UTILITY VIEWS (for leaderboard scoring)
-- ============================================================
create or replace view leaderboard_scores as
select
  p.id,
  p.prenom,
  p.nom,
  p.photo_path,
  p.university,
  p.city,
  coalesce(post_count.n, 0) * 5 +
  coalesce(tutorial_count.n, 0) * 10 +
  coalesce(comment_count.n, 0) * 2 +
  coalesce(likes_received.n, 0) * 1 as score,
  coalesce(post_count.n, 0) as posts_count,
  coalesce(tutorial_count.n, 0) as tutorials_count,
  coalesce(comment_count.n, 0) as comments_count,
  coalesce(likes_received.n, 0) as likes_received
from profiles p
left join (
  select user_id, count(*) as n from posts where status = 'active' group by user_id
) post_count on post_count.user_id = p.id
left join (
  select user_id, count(*) as n from tutorials where status = 'active' group by user_id
) tutorial_count on tutorial_count.user_id = p.id
left join (
  select user_id, count(*) as n from comments where status = 'active' group by user_id
) comment_count on comment_count.user_id = p.id
left join (
  select
    p2.user_id as user_id,
    count(*) as n
  from likes l
  join posts p2 on (l.likeable_type = 'post' and l.likeable_id = p2.id)
  group by p2.user_id
) likes_received on likes_received.user_id = p.id
where p.status = 'active'
order by score desc;
