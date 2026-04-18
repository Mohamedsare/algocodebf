/**
 * Nos valeurs — fond plein écran, image en CSS (background-attachment: fixed).
 * Aucun JS : le fond reste ancré au viewport, le contenu défile ; au scroll, la section
 * « balaie » naturellement différentes zones de l’image (sans translation artificielle).
 */
const VALUES = [
  {
    icon: 'fa-handshake',
    title: 'Communauté',
    text: 'Rassembler les talents tech du Burkina Faso pour apprendre et avancer ensemble.',
  },
  {
    icon: 'fa-share-nodes',
    title: 'Partage',
    text: 'Mettre en commun formations, retours d’expérience et ressources ouvertes.',
  },
  {
    icon: 'fa-users',
    title: 'Inclusion',
    text: 'Accueillir tous les profils — débutants comme experts — dans le respect mutuel.',
  },
  {
    icon: 'fa-lightbulb',
    title: 'Innovation',
    text: 'Encourager les projets, les idées et les bonnes pratiques pour faire grandir l’écosystème.',
  },
] as const

export function HomeValuesSection() {
  return (
    <section className="hm-values" aria-labelledby="hm-values-title">
      <div className="hm-values-overlay" aria-hidden />
      <div className="container hm-values-inner">
        <div className="hm-values-head">
          <h2 id="hm-values-title">
            <i className="fas fa-gem" aria-hidden />
            Nos valeurs
          </h2>
          <p className="hm-values-lead">
            Ce qui guide AlgoCodeBF au quotidien — une communauté numérique ancrée au Burkina Faso.
          </p>
        </div>
        <ul className="hm-values-grid">
          {VALUES.map(item => (
            <li key={item.title} className="hm-values-card">
              <span className="hm-values-ico" aria-hidden>
                <i className={`fas ${item.icon}`} />
              </span>
              <h3>{item.title}</h3>
              <p>{item.text}</p>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
