// ============================================================
// AlgoCodeBF - TypeScript Types
// Mirrors the Supabase database schema
// ============================================================

export type UserRole = 'user' | 'company' | 'admin'
/** Sous-type choisi à l'inscription (persisté sur profiles.account_kind) */
export type AccountKind = 'student' | 'professional' | 'enterprise'
export type UserStatus = 'active' | 'inactive' | 'suspended'
export type TutorialType = 'video' | 'text' | 'mixed'
export type TutorialLevel = 'beginner' | 'intermediate' | 'advanced'
export type PostStatus = 'active' | 'inactive'
export type BlogStatus = 'draft' | 'published' | 'archived'
export type JobType =
  | 'job'
  | 'internship'
  | 'hackathon'
  | 'stage'
  | 'emploi'
  | 'freelance'
  | 'formation'
export type JobStatus = 'active' | 'expired' | 'closed'
export type ProjectStatus = 'planning' | 'active' | 'in_progress' | 'completed' | 'paused' | 'archived'
export type ProjectVisibility = 'public' | 'private'
export type MemberStatus = 'pending' | 'active' | 'rejected' | 'left'
export type ApplicationStatus = 'pending' | 'accepted' | 'rejected'
export type CommentableType = 'post' | 'tutorial' | 'blog' | 'project'
export type LikeableType = 'post' | 'comment' | 'tutorial' | 'blog' | 'project'
export type SkillLevel = 'beginner' | 'intermediate' | 'advanced'
export type ActionStatus = 'pending' | 'accepted' | 'rejected' | 'cancelled'

export interface Profile {
  id: string
  prenom: string
  nom: string
  phone: string | null
  university: string | null
  faculty: string | null
  city: string | null
  /** Présent après migration `0006_register_account_kind` */
  account_kind?: AccountKind
  organization_name?: string | null
  job_title?: string | null
  bio: string | null
  photo_path: string | null
  cv_path: string | null
  role: UserRole
  status: UserStatus
  can_create_tutorial: boolean
  can_create_project: boolean
  last_login: string | null
  points: number
  created_at: string
  updated_at: string
  // Joined fields
  skills?: UserSkill[]
  badges?: UserBadge[]
  followers_count?: number
  following_count?: number
  posts_count?: number
  tutorials_count?: number
}

export interface Skill {
  id: number
  name: string
  category: string | null
  created_at: string
}

export interface UserSkill {
  user_id: string
  skill_id: number
  level: SkillLevel | null
  skill?: Skill
}

export interface Badge {
  id: number
  name: string
  description: string | null
  icon: string | null
  created_at: string
}

export interface UserBadge {
  user_id: string
  badge_id: number
  awarded_at: string
  badge?: Badge
}

export interface Follow {
  follower_id: string
  following_id: string
  created_at: string
}

export interface BlogCategory {
  id: number
  name: string
  slug: string
  description: string | null
}

export interface BlogPost {
  id: number
  author_id: string | null
  title: string
  slug: string
  excerpt: string | null
  content: string | null
  featured_image: string | null
  category: string | null
  views: number
  likes_count: number
  comments_count: number
  status: BlogStatus
  published_at: string | null
  created_at: string
  updated_at: string
  // Joined
  author?: Profile
  liked_by_user?: boolean
}

export interface Tag {
  id: number
  name: string
}

export interface Tutorial {
  id: number
  user_id: string | null
  title: string
  description: string | null
  content: string | null
  thumbnail: string | null
  category: string | null
  type: TutorialType
  level: TutorialLevel
  views: number
  likes_count: number
  status: 'active' | 'inactive'
  created_at: string
  updated_at: string
  // Joined
  author?: Profile
  videos?: TutorialVideo[]
  chapters?: TutorialChapter[]
  tags?: Tag[]
  liked_by_user?: boolean
}

export interface TutorialVideo {
  id: number
  tutorial_id: number
  title: string | null
  description: string | null
  file_path: string | null
  /** Lien YouTube, Vimeo ou fichier vidéo en HTTPS (si pas d’upload). */
  external_url: string | null
  file_name: string | null
  file_size: number | null
  duration: number | null
  order_index: number
  views: number
  created_at: string
}

export interface TutorialChapter {
  id: number
  tutorial_id: number
  chapter_number: number | null
  title: string | null
  description: string | null
  video_id: number | null
  order_index: number
  created_at: string
  video?: TutorialVideo
}

export interface ForumCategory {
  id: number
  name: string
  slug: string
  description: string | null
}

export interface Post {
  id: number
  user_id: string | null
  title: string
  body: string
  category: string | null
  views: number
  is_pinned: boolean
  status: PostStatus
  created_at: string
  updated_at: string
  // Joined
  author?: Profile
  comments_count?: number
  likes_count?: number
  liked_by_user?: boolean
}

export interface Comment {
  id: number
  user_id: string | null
  commentable_type: CommentableType
  commentable_id: number
  body: string
  status: 'active' | 'deleted'
  created_at: string
  updated_at: string
  // Joined
  author?: Profile
  liked_by_user?: boolean
  likes_count?: number
}

export interface Like {
  id: number
  user_id: string
  likeable_type: LikeableType
  likeable_id: number
  created_at: string
}

export interface Job {
  id: number
  company_id: string | null
  company_name: string | null
  title: string
  description: string | null
  type: JobType
  city: string | null
  salary: string | null
  deadline: string | null
  status: JobStatus
  views: number
  external_link: string | null
  is_scraped: boolean
  skills_required?: string | null
  company_logo?: string | null
  created_at: string
  updated_at: string
  // Joined
  company?: Profile
  applications_count?: number
  already_applied?: boolean
}

export interface Application {
  id: number
  job_id: number
  user_id: string
  cover_letter: string | null
  cv_path: string | null
  status: ApplicationStatus
  created_at: string
  // Joined
  job?: Job
  applicant?: Profile
}

export interface Project {
  id: number
  owner_id: string | null
  title: string
  description: string | null
  github_link: string | null
  demo_link: string | null
  status: ProjectStatus
  visibility: ProjectVisibility
  looking_for_members: boolean
  created_at: string
  updated_at: string
  // Joined
  owner?: Profile
  members?: ProjectMember[]
  members_count?: number
  comments_count?: number
  liked_by_user?: boolean
  user_membership?: ProjectMember
}

export interface ProjectMember {
  id: number
  project_id: number
  user_id: string
  role: string
  status: MemberStatus
  joined_at: string | null
  created_at: string
  // Joined
  member?: Profile
}

export interface Message {
  id: number
  sender_id: string | null
  receiver_id: string | null
  subject: string | null
  body: string
  action_type: string | null
  action_data: Record<string, unknown> | null
  action_status: ActionStatus | null
  is_read: boolean
  is_deleted_by_sender: boolean
  is_deleted_by_receiver: boolean
  created_at: string
  // Joined
  sender?: Profile
  receiver?: Profile
}

export interface PostAttachment {
  id: number
  post_id: number
  user_id: string | null
  file_path: string
  original_name: string | null
  mime_type: string | null
  file_size: number | null
  created_at: string
}

export type ReportStatus = 'pending' | 'reviewed' | 'resolved' | 'dismissed'
export type ReportableType = 'post' | 'comment' | 'blog' | 'tutorial' | 'project' | 'user'

export interface Report {
  id: number
  reporter_id: string | null
  reportable_type: ReportableType
  reportable_id: number | null
  reportable_uuid: string | null
  reason: string
  details: string | null
  status: ReportStatus
  reviewed_by: string | null
  reviewed_at: string | null
  created_at: string
}

export interface NewsletterSubscriber {
  id: number
  email: string
  status: 'active' | 'unsubscribed' | 'bounced'
  unsubscribe_token: string | null
  ip_address: string | null
  user_agent: string | null
  subscribed_at: string
  unsubscribed_at: string | null
  last_sent_at: string | null
  total_sent: number
}

export interface SystemSetting {
  id: number
  key: string
  value: string | null
  created_at: string
  updated_at: string
}

export interface LeaderboardEntry {
  id: string
  prenom: string
  nom: string
  photo_path: string | null
  university: string | null
  city: string | null
  score: number
  posts_count: number
  tutorials_count: number
  comments_count: number
  likes_received: number
}

// ============================================================
// API response types
// ============================================================
export interface PaginatedResponse<T> {
  data: T[]
  total: number
  page: number
  pageSize: number
  totalPages: number
}

export interface ApiError {
  message: string
  code?: string
}

// ============================================================
// Form types
// ============================================================
export interface LoginForm {
  email: string
  password: string
}

export interface RegisterForm {
  prenom: string
  nom: string
  email: string
  phone: string
  university: string
  faculty: string
  city: string
  password: string
  password_confirmation: string
}

export interface BlogPostForm {
  title: string
  excerpt: string
  content: string
  category: string
  featured_image?: File
  status: BlogStatus
}

export interface TutorialForm {
  title: string
  description: string
  content: string
  category: string
  type: TutorialType
  level: TutorialLevel
  tags: string[]
}

export interface PostForm {
  title: string
  body: string
  category: string
}

export interface JobForm {
  title: string
  description: string
  type: JobType
  city: string
  salary: string
  deadline: string
  external_link: string
}

export interface ProjectForm {
  title: string
  description: string
  status: ProjectStatus
  visibility: ProjectVisibility
  looking_for_members: boolean
}

export interface MessageForm {
  receiver_id: string
  subject: string
  body: string
}

export interface ProfileEditForm {
  prenom: string
  nom: string
  phone: string
  university: string
  faculty: string
  city: string
  bio: string
}
