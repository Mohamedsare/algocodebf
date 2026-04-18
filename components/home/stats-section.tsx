import { Users, MessageSquare, GraduationCap, Code } from 'lucide-react'
import { formatNumber } from '@/lib/utils'

interface StatsSectionProps {
  stats: { users: number; posts: number; tutorials: number; projects: number }
}

const statConfig = [
  { key: 'users' as const, label: 'Membres actifs', icon: Users, color: 'text-[#C8102E] bg-[#C8102E]/10' },
  { key: 'posts' as const, label: 'Discussions', icon: MessageSquare, color: 'text-[#006A4E] bg-[#006A4E]/10' },
  { key: 'tutorials' as const, label: 'Formations', icon: GraduationCap, color: 'text-[#FFD100] bg-[#FFD100]/10' },
  { key: 'projects' as const, label: 'Projets', icon: Code, color: 'text-blue-600 bg-blue-100' },
]

export function StatsSection({ stats }: StatsSectionProps) {
  return (
    <section className="bg-white dark:bg-gray-950 border-b border-gray-100 dark:border-gray-900">
      <div className="max-w-7xl mx-auto px-4 py-12">
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
          {statConfig.map(({ key, label, icon: Icon, color }) => (
            <div key={key} className="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-900">
              <div className={`w-12 h-12 rounded-xl ${color} flex items-center justify-center flex-shrink-0`}>
                <Icon size={22} />
              </div>
              <div>
                <div className="text-2xl font-black text-gray-900 dark:text-white">
                  {formatNumber(stats[key])}
                </div>
                <div className="text-sm text-gray-500">{label}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
