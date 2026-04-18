import { z } from 'zod'

const BURKINA_CITIES = [
  'Ouagadougou', 'Bobo-Dioulasso', 'Koudougou', 'Ouahigouya', 'Banfora',
  'Dédougou', 'Kaya', 'Tenkodogo', 'Fada N\'Gourma', 'Manga', 'Gaoua',
  'Dori', 'Kongoussi', 'Nouna', 'Léo', 'Diébougou', 'Tougan',
  'Zorgo', 'Réo', 'Titao', 'Kombissiri', 'Ziniare',
]

export const loginSchema = z.object({
  email: z.string().email('Adresse email invalide'),
  password: z.string().min(1, 'Mot de passe requis'),
})

export const registerSchema = z.object({
  prenom: z.string().min(2, 'Le prénom doit contenir au moins 2 caractères').max(50),
  nom: z.string().min(2, 'Le nom doit contenir au moins 2 caractères').max(50),
  email: z.string().email('Adresse email invalide'),
  phone: z.string()
    .regex(/^\+226\s?[0-9]{8}$/, 'Numéro invalide. Format: +226 XXXXXXXX')
    .optional()
    .or(z.literal('')),
  university: z.string().min(2, 'Université/École requise').max(100),
  faculty: z.string().min(2, 'Filière/Spécialité requise').max(100),
  city: z.string().min(1, 'Ville requise'),
  password: z.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .regex(/[A-Z]/, 'Le mot de passe doit contenir au moins une majuscule')
    .regex(/[0-9]/, 'Le mot de passe doit contenir au moins un chiffre'),
  password_confirmation: z.string(),
}).refine(data => data.password === data.password_confirmation, {
  message: 'Les mots de passe ne correspondent pas',
  path: ['password_confirmation'],
})

export const forgotPasswordSchema = z.object({
  email: z.string().email('Adresse email invalide'),
})

export const resetPasswordSchema = z.object({
  password: z.string()
    .min(8, 'Le mot de passe doit contenir au moins 8 caractères')
    .regex(/[A-Z]/, 'Doit contenir au moins une majuscule')
    .regex(/[0-9]/, 'Doit contenir au moins un chiffre'),
  password_confirmation: z.string(),
}).refine(data => data.password === data.password_confirmation, {
  message: 'Les mots de passe ne correspondent pas',
  path: ['password_confirmation'],
})

export const blogPostSchema = z.object({
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  excerpt: z.string().min(20, 'L\'extrait doit contenir au moins 20 caractères').max(500),
  content: z.string().min(100, 'Le contenu doit contenir au moins 100 caractères'),
  category: z.string().min(1, 'Catégorie requise'),
  status: z.enum(['draft', 'published', 'archived']),
})

export const tutorialSchema = z.object({
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  description: z.string().min(20, 'La description doit contenir au moins 20 caractères').max(1000),
  content: z.string().optional(),
  category: z.string().min(1, 'Catégorie requise'),
  type: z.enum(['video', 'text', 'mixed']),
  level: z.enum(['beginner', 'intermediate', 'advanced']),
  tags: z.array(z.string()).max(10, 'Maximum 10 tags'),
})

export const postSchema = z.object({
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  body: z.string().min(20, 'Le contenu doit contenir au moins 20 caractères'),
  category: z.string().min(1, 'Catégorie requise'),
})

export const jobSchema = z.object({
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  description: z.string().min(50, 'La description doit contenir au moins 50 caractères'),
  type: z.enum(['job', 'internship', 'hackathon']),
  city: z.string().min(1, 'Ville requise'),
  salary: z.string().optional(),
  deadline: z.string().min(1, 'Date limite requise'),
  external_link: z.string().url('URL invalide').optional().or(z.literal('')),
})

export const projectSchema = z.object({
  title: z.string().min(5, 'Le titre doit contenir au moins 5 caractères').max(200),
  description: z.string().min(20, 'La description doit contenir au moins 20 caractères'),
  status: z.enum(['active', 'completed', 'archived']),
  visibility: z.enum(['public', 'private']),
  looking_for_members: z.boolean(),
})

export const messageSchema = z.object({
  receiver_id: z.string().uuid('Destinataire invalide'),
  subject: z.string().min(1, 'Sujet requis').max(200),
  body: z.string().min(10, 'Le message doit contenir au moins 10 caractères'),
})

export const profileEditSchema = z.object({
  prenom: z.string().min(2, 'Prénom trop court').max(50),
  nom: z.string().min(2, 'Nom trop court').max(50),
  phone: z.string()
    .regex(/^\+226\s?[0-9]{8}$/, 'Format: +226 XXXXXXXX')
    .optional()
    .or(z.literal('')),
  university: z.string().max(100).optional(),
  faculty: z.string().max(100).optional(),
  city: z.string().max(100).optional(),
  bio: z.string().max(500, 'La bio ne peut pas dépasser 500 caractères').optional(),
})

export const commentSchema = z.object({
  body: z.string().min(5, 'Le commentaire doit contenir au moins 5 caractères').max(2000),
})

export const newsletterSchema = z.object({
  email: z.string().email('Adresse email invalide'),
})

export { BURKINA_CITIES }
