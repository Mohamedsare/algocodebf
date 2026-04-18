import Link from 'next/link'
import { Code2, ExternalLink, Users, UserPlus } from 'lucide-react'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { formatDateShort } from '@/lib/utils'
import type { ProjectListItem } from '@/lib/queries/projects'

const STATUS_LABELS: Record<string, { label: string; color: 'default' | 'success' | 'warning' | 'outline' }> = {
  planning: { label: 'Planification', color: 'outline' },
  active: { label: 'Actif', color: 'success' },
  in_progress: { label: 'En cours', color: 'success' },
  completed: { label: 'Terminé', color: 'default' },
  paused: { label: 'En pause', color: 'warning' },
  archived: { label: 'Archivé', color: 'outline' },
}

export function ProjectCard({ project }: { project: ProjectListItem }) {
  const status = STATUS_LABELS[project.status] ?? STATUS_LABELS.planning
  return (
    <Link
      href={`/project/${project.id}`}
      className="group block rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 hover:border-[#006A4E] hover:shadow-lg transition-all"
    >
      <div className="flex items-start justify-between gap-3 mb-3">
        <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-[#006A4E] transition-colors">
          {project.title}
        </h3>
        <Badge variant={status.color}>{status.label}</Badge>
      </div>

      {project.description && (
        <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-3 mb-4">
          {project.description}
        </p>
      )}

      <div className="flex items-center justify-between text-xs text-gray-500 dark:text-gray-500 mb-4">
        <span className="flex items-center gap-1.5">
          <Users size={14} />
          {project.members_count} membre{project.members_count > 1 ? 's' : ''}
        </span>
        <span>{formatDateShort(project.created_at)}</span>
      </div>

      <div className="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
        {project.owner ? (
          <div className="flex items-center gap-2 min-w-0">
            <Avatar
              src={project.owner.photo_path}
              prenom={project.owner.prenom}
              nom={project.owner.nom}
              size="sm"
            />
            <div className="min-w-0">
              <div className="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                {project.owner.prenom} {project.owner.nom}
              </div>
              {project.owner.university && (
                <div className="text-xs text-gray-500 truncate">{project.owner.university}</div>
              )}
            </div>
          </div>
        ) : (
          <span className="text-xs text-gray-400">Porteur inconnu</span>
        )}

        <div className="flex items-center gap-2 shrink-0">
          {project.github_link && (
            <span className="text-gray-400" title="GitHub disponible"><Code2 size={16} /></span>
          )}
          {project.demo_link && (
            <span className="text-gray-400" title="Démo disponible"><ExternalLink size={16} /></span>
          )}
          {project.looking_for_members && (
            <span className="text-[#C8102E]" title="Recrute"><UserPlus size={16} /></span>
          )}
        </div>
      </div>
    </Link>
  )
}
