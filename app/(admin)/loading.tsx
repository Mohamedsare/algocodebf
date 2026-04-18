import { ListSkeleton } from '@/components/shared/skeletons'

export default function Loading() {
  return (
    <div style={{ padding: 20 }}>
      <div
        className="skeleton skeleton-title"
        style={{ width: '35%', height: 28, marginBottom: 20 }}
      />
      <ListSkeleton rows={6} />
    </div>
  )
}
