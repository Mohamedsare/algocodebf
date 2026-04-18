import { getFlashes } from '@/lib/flash'

/**
 * Alertes Flash au format PHP original (.alert .alert-success / .alert-error).
 * Rendu côté serveur : les messages sont consommés et affichés 1 seule fois.
 */
export async function FlashMessages() {
  const flashes = await getFlashes()
  if (flashes.length === 0) return null

  return (
    <>
      {flashes.map((f, i) => {
        const cls =
          f.type === 'success'
            ? 'alert alert-success'
            : f.type === 'error'
              ? 'alert alert-error'
              : f.type === 'warning'
                ? 'alert alert-warning'
                : 'alert alert-info'
        const icon =
          f.type === 'success'
            ? 'fa-check-circle'
            : f.type === 'error'
              ? 'fa-exclamation-circle'
              : 'fa-info-circle'
        return (
          <div key={i} className={cls}>
            <div className="container">
              <i className={`fas ${icon}`}></i> {f.message}
            </div>
          </div>
        )
      })}
    </>
  )
}
