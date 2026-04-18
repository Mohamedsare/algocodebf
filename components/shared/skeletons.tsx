/* Loading skeleton commun pour toutes les pages */

export function PageSkeleton() {
  return (
    <div className="container" style={{ padding: '40px 20px' }}>
      <div
        className="skeleton skeleton-title"
        style={{ width: '45%', height: 32, marginBottom: 20 }}
      />
      <div
        className="skeleton skeleton-text"
        style={{ width: '70%', marginBottom: 40 }}
      />
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
          gap: 20,
        }}
      >
        {Array.from({ length: 6 }).map((_, i) => (
          <div
            key={i}
            className="skeleton skeleton-card"
            style={{ height: 240 }}
          />
        ))}
      </div>
    </div>
  )
}

export function ListSkeleton({ rows = 5 }: { rows?: number }) {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
      {Array.from({ length: rows }).map((_, i) => (
        <div
          key={i}
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: 14,
            padding: 16,
            borderRadius: 14,
            background: '#fff',
            boxShadow: '0 2px 8px rgba(0,0,0,.04)',
          }}
        >
          <div className="skeleton skeleton-avatar" />
          <div style={{ flex: 1 }}>
            <div
              className="skeleton skeleton-text"
              style={{ width: '40%', height: 16 }}
            />
            <div
              className="skeleton skeleton-text"
              style={{ width: '80%', marginTop: 6 }}
            />
          </div>
        </div>
      ))}
    </div>
  )
}

export function ArticleSkeleton() {
  return (
    <div className="container" style={{ padding: '40px 20px', maxWidth: 900 }}>
      <div
        className="skeleton"
        style={{ height: 280, borderRadius: 18, marginBottom: 30 }}
      />
      <div
        className="skeleton skeleton-title"
        style={{ width: '70%', height: 36, marginBottom: 16 }}
      />
      <div className="skeleton skeleton-text" style={{ width: '40%', marginBottom: 30 }} />
      {Array.from({ length: 8 }).map((_, i) => (
        <div
          key={i}
          className="skeleton skeleton-text"
          style={{ width: `${60 + ((i * 7) % 40)}%` }}
        />
      ))}
    </div>
  )
}
