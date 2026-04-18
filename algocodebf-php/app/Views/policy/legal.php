<?php
$pageTitle = 'Mentions Légales - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="policy-section">
    <div class="container">
        <h1 class="policy-title">Mentions Légales</h1>
        <p class="policy-intro">
            Dernière mise à jour : 15 mai 2024
        </p>

        <div class="policy-content">
            <h2>1. Identification de l'éditeur</h2>
            <div class="info-box">
                <h3>AlgoCodeBF</h3>
                <ul class="info-list">
                    <li><strong>Dénomination sociale :</strong> AlgoCodeBF</li>
                    <li><strong>Forme juridique :</strong> Association loi 1901 (équivalent burkinabè)</li>
                    <li><strong>Siège social :</strong> Ouagadougou, Burkina Faso</li>
                    <li><strong>Email :</strong> <a href="mailto:<?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?>"><?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?></a></li>
                    <li><strong>Téléphone :</strong> <?= htmlspecialchars($GLOBALS['site_settings']['contact_phone'] ?? '+226 XX XX XX XX') ?></li>
                    <li><strong>Site web :</strong> <a href="<?= BASE_URL ?>"><?= htmlspecialchars($_SERVER['HTTP_HOST'] . BASE_URL) ?></a></li>
                </ul>
            </div>

            <h2>2. Directeur de la publication</h2>
            <div class="info-box">
                <p>
                    <strong>Nom :</strong> Mohamed SARE<br>
                    <strong>Qualité :</strong> Fondateur et Développeur Principal<br>
                    <strong>Contact :</strong> <a href="mailto:<?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?>"><?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?></a>
                </p>
            </div>

            <h2>3. Hébergement du site</h2>
            <div class="info-box">
                <h3>Hébergeur</h3>
                <ul class="info-list">
                    <li><strong>Nom de l'hébergeur :</strong> [Nom de l'hébergeur]</li>
                    <li><strong>Adresse :</strong> [Adresse complète de l'hébergeur]</li>
                    <li><strong>Téléphone :</strong> [Numéro de téléphone]</li>
                    <li><strong>Site web :</strong> [URL de l'hébergeur]</li>
                </ul>
                <p class="note">
                    <i class="fas fa-info-circle"></i> 
                    En cas de litige concernant l'hébergement, veuillez contacter directement l'hébergeur aux coordonnées ci-dessus.
                </p>
            </div>

            <h2>4. Propriété intellectuelle</h2>
            <h3>4.1 Droits d'auteur</h3>
            <p>
                L'ensemble du contenu présent sur le site AlgoCodeBF (textes, images, logos, graphismes, animations, vidéos, sons, bases de données, logiciels, etc.) est protégé par les lois en vigueur au Burkina Faso sur la propriété intellectuelle.
            </p>
            <p>
                La marque "AlgoCodeBF", ainsi que les logos et éléments graphiques présents sur le site sont la propriété exclusive d'AlgoCodeBF et ne peuvent être utilisés sans autorisation écrite préalable.
            </p>

            <h3>4.2 Contenu utilisateur</h3>
            <p>
                Les utilisateurs conservent l'intégralité de leurs droits de propriété intellectuelle sur le contenu qu'ils publient (tutoriels, articles, projets, commentaires, etc.). En publiant du contenu sur AlgoCodeBF, vous accordez à la plateforme une licence non exclusive, transférable et mondiale pour afficher, distribuer et promouvoir ce contenu dans le cadre du fonctionnement du site.
            </p>

            <h3>4.3 Citation et reproduction</h3>
            <p>
                Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable d'AlgoCodeBF.
            </p>
            <p>
                Toute exploitation non autorisée du site ou de l'un quelconque des éléments qu'il contient sera considérée comme constitutive d'une contrefaçon et poursuivie conformément aux dispositions du Code de la propriété intellectuelle du Burkina Faso.
            </p>

            <h2>5. Protection des données personnelles</h2>
            <p>
                Conformément à la réglementation en vigueur au Burkina Faso, les utilisateurs du site AlgoCodeBF disposent d'un droit d'accès, de rectification, de suppression et d'opposition de leurs données personnelles.
            </p>
            <p>
                Pour plus d'informations sur la collecte et le traitement de vos données personnelles, veuillez consulter notre <a href="<?= BASE_URL ?>/policy/privacy">Politique de Confidentialité</a>.
            </p>
            <div class="info-box">
                <h4>Responsable du traitement des données :</h4>
                <ul class="info-list">
                    <li><strong>Nom :</strong> AlgoCodeBF</li>
                    <li><strong>Email :</strong> <a href="mailto:<?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'privacy@hubtech.bf') ?>"><?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'privacy@hubtech.bf') ?></a></li>
                    <li><strong>Adresse :</strong> Ouagadougou, Burkina Faso</li>
                </ul>
            </div>

            <h2>6. Cookies</h2>
            <p>
                Le site AlgoCodeBF utilise des cookies pour améliorer l'expérience utilisateur, mémoriser vos préférences et analyser le trafic du site.
            </p>
            <h3>Types de cookies utilisés :</h3>
            <ul>
                <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site (authentification, sécurité)</li>
                <li><strong>Cookies de performance :</strong> Permettent d'analyser l'utilisation du site et d'améliorer ses performances</li>
                <li><strong>Cookies fonctionnels :</strong> Mémorisent vos préférences (langue, thème, etc.)</li>
            </ul>
            <p>
                Vous pouvez configurer votre navigateur pour refuser les cookies. Cependant, certaines fonctionnalités du site pourraient ne pas fonctionner correctement.
            </p>

            <h2>7. Limitation de responsabilité</h2>
            <h3>7.1 Contenu du site</h3>
            <p>
                AlgoCodeBF s'efforce de fournir des informations exactes et à jour sur son site. Toutefois, nous ne garantissons pas l'exactitude, la précision ou l'exhaustivité des informations mises à disposition.
            </p>
            <p>
                AlgoCodeBF décline toute responsabilité pour :
            </p>
            <ul>
                <li>Les erreurs ou omissions dans le contenu du site</li>
                <li>Les dommages directs ou indirects résultant de l'utilisation du site</li>
                <li>Les interruptions, bugs ou dysfonctionnements techniques</li>
                <li>Les virus ou logiciels malveillants pouvant infecter votre équipement</li>
                <li>Les pertes de données ou de profits liées à l'utilisation du site</li>
            </ul>

            <h3>7.2 Contenu utilisateur</h3>
            <p>
                Le contenu publié par les utilisateurs (tutoriels, commentaires, projets, etc.) n'engage que la responsabilité de leurs auteurs. AlgoCodeBF ne peut être tenu responsable du contenu publié par les utilisateurs, mais se réserve le droit de le modérer ou de le supprimer s'il contrevient aux règles de la plateforme.
            </p>

            <h3>7.3 Liens externes</h3>
            <p>
                Le site AlgoCodeBF peut contenir des liens vers des sites web tiers. Nous ne sommes pas responsables du contenu, des pratiques de confidentialité ou de la disponibilité de ces sites externes. L'accès à ces sites se fait aux risques et périls de l'utilisateur.
            </p>

            <h2>8. Loi applicable et juridiction</h2>
            <p>
                Les présentes mentions légales sont régies par la loi burkinabè. Tout litige relatif à l'utilisation du site AlgoCodeBF sera soumis à la compétence exclusive des tribunaux du Burkina Faso.
            </p>
            <div class="info-box legal-box">
                <h4>Règlement des litiges :</h4>
                <p>
                    En cas de litige, nous vous encourageons à nous contacter en premier lieu pour tenter de trouver une solution amiable. Si aucune solution n'est trouvée, le litige sera porté devant les juridictions compétentes de Ouagadougou, Burkina Faso.
                </p>
            </div>

            <h2>9. Accessibilité</h2>
            <p>
                AlgoCodeBF s'engage à rendre son site accessible au plus grand nombre, conformément aux standards internationaux d'accessibilité web (WCAG 2.1).
            </p>
            <p>
                Nous mettons en œuvre les mesures suivantes :
            </p>
            <ul>
                <li>Navigation claire et intuitive</li>
                <li>Contraste de couleurs adapté</li>
                <li>Textes alternatifs pour les images</li>
                <li>Compatibilité avec les technologies d'assistance</li>
                <li>Design responsive pour tous les appareils</li>
            </ul>
            <p>
                Si vous rencontrez des difficultés d'accessibilité, n'hésitez pas à nous contacter.
            </p>

            <h2>10. Crédits</h2>
            <div class="credits-box">
                <h3>Technologies utilisées :</h3>
                <ul class="tech-list">
                    <li><i class="fab fa-php"></i> <strong>PHP</strong> - Langage de programmation backend</li>
                    <li><i class="fab fa-js"></i> <strong>JavaScript</strong> - Interactivité et dynamisme</li>
                    <li><i class="fab fa-css3-alt"></i> <strong>CSS3</strong> - Design et mise en page</li>
                    <li><i class="fab fa-html5"></i> <strong>HTML5</strong> - Structure sémantique</li>
                    <li><i class="fas fa-database"></i> <strong>MySQL</strong> - Base de données</li>
                </ul>

                <h3>Bibliothèques et frameworks :</h3>
                <ul class="tech-list">
                    <li><i class="fas fa-code"></i> <strong>TinyMCE</strong> - Éditeur de texte riche</li>
                    <li><i class="fas fa-icons"></i> <strong>Font Awesome</strong> - Icônes</li>
                    <li><i class="fas fa-palette"></i> <strong>Custom CSS</strong> - Design personnalisé</li>
                </ul>

                <h3>Développement :</h3>
                <ul class="tech-list">
                    <li><i class="fas fa-user-tie"></i> <strong>Développeur Principal :</strong> Mohamed SARE</li>
                    <li><i class="fas fa-calendar-alt"></i> <strong>Date de lancement :</strong> Octobre 2025</li>
                    <li><i class="fas fa-code-branch"></i> <strong>Architecture :</strong> MVC (Model-View-Controller)</li>
                </ul>
            </div>

            <h2>11. Contact</h2>
            <p>
                Pour toute question concernant ces mentions légales ou l'utilisation du site AlgoCodeBF, vous pouvez nous contacter :
            </p>
            <div class="contact-box">
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:<?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?>"><?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?></a>
                    </div>
                </div>
                <div class="contact-method">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Téléphone</strong>
                        <span><?= htmlspecialchars($GLOBALS['site_settings']['contact_phone'] ?? '+226 XX XX XX XX') ?></span>
                    </div>
                </div>
                <div class="contact-method">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Adresse</strong>
                        <span>Ouagadougou, Burkina Faso</span>
                    </div>
                </div>
                <div class="contact-method">
                    <i class="fas fa-comments"></i>
                    <div>
                        <strong>Messagerie interne</strong>
                        <a href="<?= BASE_URL ?>/message/compose">Nous envoyer un message</a>
                    </div>
                </div>
            </div>

            <div class="policy-footer">
                <p>
                    <strong>🇧🇫 AlgoCodeBF - Plateforme communautaire tech du Burkina Faso</strong><br>
                    Développé avec passion au cœur de l'Afrique de l'Ouest<br>
                    <em>« Ensemble, codons l'avenir du Burkina Faso »</em>
                </p>
            </div>
        </div>
    </div>
</section>

<style>
/* Styles spécifiques pour les mentions légales */
.policy-section {
    padding: 40px 0 80px;
    background: #f8f9fa;
    min-height: 100vh;
}

.policy-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.policy-intro {
    text-align: center;
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 40px;
    font-style: italic;
}

.policy-content {
    max-width: 900px;
    margin: 0 auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    line-height: 1.7;
}

.policy-content h2 {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-top: 35px;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid rgba(200, 16, 46, 0.1);
}

.policy-content h3 {
    font-size: 1.2rem;
    color: var(--secondary-color);
    margin-top: 20px;
    margin-bottom: 10px;
}

.policy-content h4 {
    font-size: 1.1rem;
    color: #333;
    margin-top: 15px;
    margin-bottom: 10px;
}

.policy-content p {
    margin-bottom: 15px;
    color: #444;
}

.policy-content ul {
    margin-bottom: 20px;
    padding-left: 25px;
}

.policy-content li {
    margin-bottom: 8px;
    color: #555;
}

.policy-content a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.policy-content a:hover {
    color: var(--secondary-color);
    text-decoration: underline;
}

/* Info boxes */
.info-box {
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.03), rgba(0, 106, 78, 0.03));
    border-left: 4px solid var(--primary-color);
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
}

.info-box h3, .info-box h4 {
    margin-top: 0;
    color: var(--primary-color);
}

.info-list {
    list-style: none;
    padding-left: 0;
}

.info-list li {
    padding: 8px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.info-list li:last-child {
    border-bottom: none;
}

.info-list strong {
    color: var(--secondary-color);
    display: inline-block;
    min-width: 150px;
}

.note {
    background: rgba(255, 209, 0, 0.1);
    padding: 12px 15px;
    border-radius: 6px;
    margin-top: 15px;
    font-size: 0.95rem;
}

.note i {
    color: var(--accent-color);
    margin-right: 8px;
}

.legal-box {
    background: linear-gradient(135deg, rgba(0, 106, 78, 0.05), rgba(200, 16, 46, 0.05));
    border-left-color: var(--secondary-color);
}

/* Credits box */
.credits-box {
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.05), rgba(255, 209, 0, 0.05), rgba(0, 106, 78, 0.05));
    padding: 30px;
    border-radius: 12px;
    margin: 25px 0;
    border: 2px solid rgba(200, 16, 46, 0.1);
}

.credits-box h3 {
    color: var(--primary-color);
    margin-top: 20px;
    margin-bottom: 15px;
}

.credits-box h3:first-child {
    margin-top: 0;
}

.tech-list {
    list-style: none;
    padding-left: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 12px;
}

.tech-list li {
    background: white;
    padding: 12px 15px;
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
    transition: all 0.3s ease;
}

.tech-list li:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(200, 16, 46, 0.1);
}

.tech-list i {
    color: var(--primary-color);
    margin-right: 10px;
    font-size: 1.1rem;
}

/* Contact box */
.contact-box {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.contact-method {
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.05), rgba(0, 106, 78, 0.05));
    padding: 20px;
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    transition: all 0.3s ease;
}

.contact-method:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(200, 16, 46, 0.15);
}

.contact-method i {
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-top: 5px;
}

.contact-method strong {
    display: block;
    color: var(--secondary-color);
    margin-bottom: 5px;
}

.contact-method a {
    color: var(--primary-color);
    word-break: break-all;
}

/* Policy footer */
.policy-footer {
    margin-top: 40px;
    padding: 30px;
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.08), rgba(255, 209, 0, 0.08), rgba(0, 106, 78, 0.08));
    border-radius: 12px;
    text-align: center;
    border: 2px solid rgba(200, 16, 46, 0.15);
}

.policy-footer p {
    font-size: 1.1rem;
    color: #333;
    margin: 0;
    line-height: 1.8;
}

.policy-footer strong {
    color: var(--primary-color);
    font-size: 1.3rem;
}

.policy-footer em {
    color: var(--secondary-color);
    font-size: 1.05rem;
}

/* Responsive */
@media (max-width: 768px) {
    .policy-section {
        padding: 20px 0 60px;
    }
    
    .policy-title {
        font-size: 2rem;
    }
    
    .policy-content {
        padding: 20px;
        margin: 0 15px;
    }
    
    .policy-content h2 {
        font-size: 1.3rem;
    }
    
    .policy-content h3 {
        font-size: 1.1rem;
    }
    
    .info-list strong {
        display: block;
        margin-bottom: 5px;
    }
    
    .tech-list {
        grid-template-columns: 1fr;
    }
    
    .contact-box {
        grid-template-columns: 1fr;
    }
    
    .credits-box {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .policy-title {
        font-size: 1.75rem;
    }
    
    .policy-content {
        padding: 15px;
        margin: 0 10px;
    }
    
    .info-box, .credits-box {
        padding: 15px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

