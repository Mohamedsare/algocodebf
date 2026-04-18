import { createClient } from '@/lib/supabase/server'
import type { Project, ProjectMember, ProjectStatus } from '@/types'

export interface ProjectListItem extends Omit<Project, 'owner' | 'members'> {
  owner: { id: string; prenom: string; nom: string; photo_path: string | null; university: string | null } | null
  members_count: number
}

interface ListOptions {
  status?: ProjectStatus | null
  recruiting?: boolean
  search?: string
  page?: number
  pageSize?: number
}

export async function listProjects(opts: ListOptions = {}) {
  const supabase = await createClient()
  const { status, recruiting, search, page = 1, pageSize = 12 } = opts

  let q = supabase
    .from('projects')
    .select(
      `id, owner_id, title, description, github_link, demo_link, status, visibility,
       looking_for_members, created_at, updated_at,
       profiles!projects_owner_id_fkey(id, prenom, nom, photo_path, university)`,
      { count: 'exact' }
    )
    .eq('visibility', 'public')

  if (status) q = q.eq('status', status)
  if (recruiting) q = q.eq('looking_for_members', true)
  if (search) {
    const p = `%${search}%`
    q = q.or(`title.ilike.${p},description.ilike.${p}`)
  }
  q = q.order('created_at', { ascending: false })
  const from = (page - 1) * pageSize
  q = q.range(from, from + pageSize - 1)

  const { data, count, error } = await q
  if (error) throw error

  const rows = (data ?? []) as unknown as Array<Project & {
    profiles: { id: string; prenom: string; nom: string; photo_path: string | null; university: string | null } | null
  }>

  // Compteurs membres actifs
  const ids = rows.map(r => r.id)
  const { data: memberCounts } = ids.length
    ? await supabase
        .from('project_members')
        .select('project_id')
        .in('project_id', ids)
        .eq('status', 'active')
    : { data: [] as Array<{ project_id: number }> }

  const membersMap = new Map<number, number>()
  for (const m of memberCounts ?? []) {
    const pid = (m as { project_id: number }).project_id
    membersMap.set(pid, (membersMap.get(pid) ?? 0) + 1)
  }

  const projects: ProjectListItem[] = rows.map(r => ({
    ...r,
    owner: r.profiles,
    members_count: membersMap.get(r.id) ?? 0,
  }))

  return {
    projects,
    total: count ?? 0,
    page,
    pageSize,
    totalPages: Math.ceil((count ?? 0) / pageSize),
  }
}

export async function getProjectDetail(id: number, currentUserId?: string | null) {
  const supabase = await createClient()
  const { data: raw } = await supabase
    .from('projects')
    .select(
      `id, owner_id, title, description, github_link, demo_link, status, visibility,
       looking_for_members, created_at, updated_at,
       profiles!projects_owner_id_fkey(id, prenom, nom, photo_path, university, city)`
    )
    .eq('id', id)
    .maybeSingle()
  if (!raw) return null

  const project = raw as unknown as Project & {
    profiles: { id: string; prenom: string; nom: string; photo_path: string | null; university: string | null; city: string | null } | null
  }

  const { data: members } = await supabase
    .from('project_members')
    .select(
      `id, project_id, user_id, role, status, joined_at, created_at,
       profiles!project_members_user_id_fkey(id, prenom, nom, photo_path, university)`
    )
    .eq('project_id', id)
    .order('created_at')

  const typedMembers = ((members ?? []) as unknown as Array<
    ProjectMember & {
      profiles: { id: string; prenom: string; nom: string; photo_path: string | null; university: string | null } | null
    }
  >).map(m => ({ ...m, member: m.profiles ?? undefined }))

  const myMembership = currentUserId
    ? typedMembers.find(m => m.user_id === currentUserId) ?? null
    : null

  return {
    project,
    owner: project.profiles,
    members: typedMembers,
    myMembership,
    isOwner: currentUserId ? project.owner_id === currentUserId : false,
  }
}
