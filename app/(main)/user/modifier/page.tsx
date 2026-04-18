import type { Metadata } from 'next'
import { requireLogin } from '@/lib/auth'
import { createClient } from '@/lib/supabase/server'
import { ProfileEditorPhp } from '@/components/user/profile-editor-php'

export const metadata: Metadata = {
  title: 'Modifier mon profil - AlgoCodeBF',
  description:
    'Mettez à jour votre profil AlgoCodeBF : formation, ville, biographie, compétences, photo et CV.',
  robots: { index: false },
}

export default async function EditProfilePage() {
  const profile = await requireLogin('/user/modifier')

  const supabase = await createClient()
  const [{ data: userData }, { data: allSkills }, { data: mySkills }] = await Promise.all([
    supabase.auth.getUser(),
    supabase.from('skills').select('id, name, category').order('category').order('name'),
    supabase.from('user_skills').select('skill_id, level').eq('user_id', profile.id),
  ])

  const email = userData.user?.email ?? ''

  const mySkillsMap: Record<number, 'beginner' | 'intermediate' | 'advanced'> = {}
  for (const row of mySkills ?? []) {
    mySkillsMap[row.skill_id as number] = row.level as 'beginner' | 'intermediate' | 'advanced'
  }

  return (
    <ProfileEditorPhp
      profile={profile}
      email={email}
      allSkills={(allSkills ?? []) as Array<{ id: number; name: string; category: string | null }>}
      initialSkills={mySkillsMap}
    />
  )
}
