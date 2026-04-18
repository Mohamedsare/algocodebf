-- ============================================================
-- AlgoCodeBF - Seeds (skills, badges, categories supplémentaires)
-- À exécuter APRÈS schema.sql, avant la mise en production.
-- ============================================================

-- SKILLS ------------------------------------------------------
insert into skills (name, category) values
  ('JavaScript', 'Langage'),
  ('TypeScript', 'Langage'),
  ('Python', 'Langage'),
  ('PHP', 'Langage'),
  ('Java', 'Langage'),
  ('C/C++', 'Langage'),
  ('Go', 'Langage'),
  ('Rust', 'Langage'),
  ('Kotlin', 'Langage'),
  ('Swift', 'Langage'),
  ('Dart', 'Langage'),
  ('SQL', 'Langage'),
  ('HTML/CSS', 'Web'),
  ('React', 'Framework'),
  ('Next.js', 'Framework'),
  ('Vue.js', 'Framework'),
  ('Angular', 'Framework'),
  ('Svelte', 'Framework'),
  ('Node.js', 'Runtime'),
  ('Express', 'Framework'),
  ('Nest.js', 'Framework'),
  ('Laravel', 'Framework'),
  ('Symfony', 'Framework'),
  ('Django', 'Framework'),
  ('Flask', 'Framework'),
  ('FastAPI', 'Framework'),
  ('Spring Boot', 'Framework'),
  ('Flutter', 'Mobile'),
  ('React Native', 'Mobile'),
  ('Android', 'Mobile'),
  ('iOS', 'Mobile'),
  ('PostgreSQL', 'Base de données'),
  ('MySQL', 'Base de données'),
  ('MongoDB', 'Base de données'),
  ('Redis', 'Base de données'),
  ('Supabase', 'BaaS'),
  ('Firebase', 'BaaS'),
  ('AWS', 'Cloud'),
  ('Docker', 'DevOps'),
  ('Kubernetes', 'DevOps'),
  ('Git', 'Outil'),
  ('Linux', 'Système'),
  ('UI/UX Design', 'Design'),
  ('Figma', 'Design'),
  ('Tailwind CSS', 'Web'),
  ('Machine Learning', 'Data'),
  ('TensorFlow', 'Data'),
  ('Data Science', 'Data'),
  ('Cybersécurité', 'Sécurité')
on conflict (name) do nothing;

-- Les badges, blog_categories, forum_categories et system_settings par défaut sont déjà
-- insérés par schema.sql.
