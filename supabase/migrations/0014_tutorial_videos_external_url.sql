-- Lien externe (YouTube, Vimeo, fichier .mp4/.webm…) en alternative à l’upload dans le bucket.

alter table public.tutorial_videos
  add column if not exists external_url text;

comment on column public.tutorial_videos.external_url is
  'URL publique (YouTube, Vimeo, ou lien direct HTTPS vers une vidéo). Null si fichier uploadé (file_path).';
