/**
 * Bande décorative : drapeau du Burkina Faso (sans titre — section purement visuelle).
 * Ratio 3:2 — rouge au-dessus, vert en dessous, étoile à cinq branches jaune au centre.
 */
export function HomeFlagBand() {
  return (
    <section className="hm-flag-band" aria-hidden="true">
      <div className="hm-flag-band-inner">
        <svg
          className="hm-flag-svg"
          viewBox="0 0 3 2"
          xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="xMidYMid slice"
        >
          <rect width="3" height="1" fill="#C8102E" />
          <rect y="1" width="3" height="1" fill="#006A4E" />
          <path
            fill="#FFD100"
            d="M1.5,0.62L1.5823,0.8867L1.8614,0.8826L1.6331,1.0433L1.7234,1.3074L1.5,1.14L1.2766,1.3074L1.3669,1.0433L1.1386,0.8826L1.4177,0.8867Z"
          />
        </svg>
      </div>
    </section>
  )
}
