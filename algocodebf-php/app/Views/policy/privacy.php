<?php
$pageTitle = 'Politique de Confidentialité - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section -->
<section class="policy-hero">
    <div class="container">
        <div class="policy-hero-content">
            <h1><i class="fas fa-shield-alt"></i> Politique de Confidentialité</h1>
            <p class="policy-subtitle">Protection de vos données personnelles et respect de votre vie privée</p>
            <div class="policy-meta">
                <span><i class="fas fa-calendar"></i> Dernière mise à jour : <?= date('d/m/Y') ?></span>
                <span><i class="fas fa-globe"></i> Conforme RGPD</span>
            </div>
        </div>
    </div>
</section>

<!-- Policy Content -->
<section class="policy-content">
    <div class="container">
        <div class="policy-wrapper">
            <!-- Table des matières -->
            <nav class="policy-toc">
                <h3><i class="fas fa-list"></i> Table des matières</h3>
                <ul>
                    <li><a href="#introduction">1. Introduction</a></li>
                    <li><a href="#collecte">2. Collecte des données</a></li>
                    <li><a href="#utilisation">3. Utilisation des données</a></li>
                    <li><a href="#partage">4. Partage des données</a></li>
                    <li><a href="#protection">5. Protection des données</a></li>
                    <li><a href="#droits">6. Vos droits</a></li>
                    <li><a href="#cookies">7. Cookies et technologies similaires</a></li>
                    <li><a href="#conservation">8. Conservation des données</a></li>
                    <li><a href="#mineurs">9. Protection des mineurs</a></li>
                    <li><a href="#modifications">10. Modifications de la politique</a></li>
                    <li><a href="#contact">11. Contact</a></li>
                </ul>
            </nav>

            <!-- Contenu principal -->
            <div class="policy-main">
                <!-- 1. Introduction -->
                <section id="introduction" class="policy-section">
                    <h2><i class="fas fa-info-circle"></i> 1. Introduction</h2>
                    <div class="policy-text">
                        <p>AlgoCodeBF s'engage à protéger votre vie privée et vos données personnelles. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations lorsque vous utilisez notre plateforme.</p>
                        
                        <div class="highlight-box">
                            <h4><i class="fas fa-star"></i> Notre engagement</h4>
                            <ul>
                                <li><strong>Transparence :</strong> Nous vous informons clairement sur l'utilisation de vos données</li>
                                <li><strong>Contrôle :</strong> Vous gardez le contrôle sur vos données personnelles</li>
                                <li><strong>Sécurité :</strong> Nous protégeons vos données avec les meilleures technologies</li>
                                <li><strong>Conformité :</strong> Respect du RGPD et des lois burkinabè en vigueur</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 2. Collecte des données -->
                <section id="collecte" class="policy-section">
                    <h2><i class="fas fa-database"></i> 2. Collecte des données</h2>
                    <div class="policy-text">
                        <h3>2.1 Données collectées directement</h3>
                        <p>Nous collectons les informations que vous nous fournissez volontairement :</p>
                        
                        <div class="data-categories">
                            <div class="data-category">
                                <h4><i class="fas fa-user"></i> Informations d'identification</h4>
                                <ul>
                                    <li>Nom et prénom</li>
                                    <li>Adresse e-mail</li>
                                    <li>Numéro de téléphone</li>
                                    <li>Photo de profil (optionnelle)</li>
                                </ul>
                            </div>
                            
                            <div class="data-category">
                                <h4><i class="fas fa-graduation-cap"></i> Informations académiques</h4>
                                <ul>
                                    <li>Université ou école</li>
                                    <li>Filière ou spécialité</li>
                                    <li>Niveau d'études</li>
                                    <li>Compétences techniques</li>
                                </ul>
                            </div>
                            
                            <div class="data-category">
                                <h4><i class="fas fa-map-marker-alt"></i> Informations géographiques</h4>
                                <ul>
                                    <li>Ville de résidence</li>
                                    <li>Pays</li>
                                </ul>
                            </div>
                            
                            <div class="data-category">
                                <h4><i class="fas fa-comment"></i> Contenu généré</h4>
                                <ul>
                                    <li>Posts et discussions</li>
                                    <li>Tutoriels et projets</li>
                                    <li>Commentaires et réactions</li>
                                    <li>Messages privés</li>
                                </ul>
                            </div>
                        </div>

                        <h3>2.2 Données collectées automatiquement</h3>
                        <div class="auto-data">
                            <div class="auto-data-item">
                                <h4><i class="fas fa-chart-line"></i> Données d'utilisation</h4>
                                <p>Pages visitées, temps passé, actions effectuées, fonctionnalités utilisées</p>
                            </div>
                            
                            <div class="auto-data-item">
                                <h4><i class="fas fa-desktop"></i> Données techniques</h4>
                                <p>Adresse IP, type de navigateur, système d'exploitation, résolution d'écran</p>
                            </div>
                            
                            <div class="auto-data-item">
                                <h4><i class="fas fa-mobile-alt"></i> Données de localisation</h4>
                                <p>Pays et région (basés sur l'adresse IP), fuseau horaire</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 3. Utilisation des données -->
                <section id="utilisation" class="policy-section">
                    <h2><i class="fas fa-cogs"></i> 3. Utilisation des données</h2>
                    <div class="policy-text">
                        <p>Nous utilisons vos données pour les finalités suivantes :</p>
                        
                        <div class="usage-grid">
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h4>Gestion du compte</h4>
                                <p>Création, authentification et maintenance de votre compte utilisateur</p>
                            </div>
                            
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-network-wired"></i>
                                </div>
                                <h4>Services de la plateforme</h4>
                                <p>Fourniture des fonctionnalités : discussions, tutoriels, projets, messagerie</p>
                            </div>
                            
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h4>Notifications</h4>
                                <p>Envoi de notifications sur vos activités et interactions</p>
                            </div>
                            
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h4>Sécurité</h4>
                                <p>Protection contre la fraude, spam et utilisation abusive</p>
                            </div>
                            
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h4>Amélioration</h4>
                                <p>Analyse anonymisée pour améliorer nos services</p>
                            </div>
                            
                            <div class="usage-card">
                                <div class="usage-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h4>Communication</h4>
                                <p>Newsletter, actualités et communications importantes</p>
                            </div>
                        </div>

                        <div class="legal-basis">
                            <h3><i class="fas fa-balance-scale"></i> Base légale du traitement</h3>
                            <ul>
                                <li><strong>Exécution du contrat :</strong> Fourniture des services demandés</li>
                                <li><strong>Intérêt légitime :</strong> Amélioration des services et sécurité</li>
                                <li><strong>Consentement :</strong> Newsletter et communications marketing</li>
                                <li><strong>Obligation légale :</strong> Conservation des données de connexion</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 4. Partage des données -->
                <section id="partage" class="policy-section">
                    <h2><i class="fas fa-share-alt"></i> 4. Partage des données</h2>
                    <div class="policy-text">
                        <p>Nous ne vendons jamais vos données personnelles. Nous pouvons partager vos informations uniquement dans les cas suivants :</p>
                        
                        <div class="sharing-scenarios">
                            <div class="sharing-item">
                                <div class="sharing-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="sharing-content">
                                    <h4>Communauté AlgoCodeBF</h4>
                                    <p>Votre profil public (nom, photo, compétences) est visible par les autres membres</p>
                                </div>
                            </div>
                            
                            <div class="sharing-item">
                                <div class="sharing-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div class="sharing-content">
                                    <h4>Prestataires de services</h4>
                                    <p>Fournisseurs d'hébergement, services d'analyse (avec accès limité et sécurisé)</p>
                                </div>
                            </div>
                            
                            <div class="sharing-item">
                                <div class="sharing-icon">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <div class="sharing-content">
                                    <h4>Obligations légales</h4>
                                    <p>Autorités compétentes en cas d'obligation légale ou de protection des droits</p>
                                </div>
                            </div>
                            
                            <div class="sharing-item">
                                <div class="sharing-icon">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div class="sharing-content">
                                    <h4>Avec votre consentement</h4>
                                    <p>Partage avec des tiers uniquement avec votre accord explicite</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 5. Protection des données -->
                <section id="protection" class="policy-section">
                    <h2><i class="fas fa-lock"></i> 5. Protection des données</h2>
                    <div class="policy-text">
                        <p>Nous mettons en œuvre des mesures de sécurité robustes pour protéger vos données :</p>
                        
                        <div class="security-measures">
                            <div class="security-category">
                                <h3><i class="fas fa-shield-alt"></i> Sécurité technique</h3>
                                <ul>
                                    <li><strong>Chiffrement :</strong> SSL/TLS pour toutes les communications</li>
                                    <li><strong>Mots de passe :</strong> Hachage sécurisé avec bcrypt</li>
                                    <li><strong>Authentification :</strong> Système d'authentification robuste</li>
                                    <li><strong>Firewall :</strong> Protection contre les intrusions</li>
                                </ul>
                            </div>
                            
                            <div class="security-category">
                                <h3><i class="fas fa-server"></i> Sécurité infrastructure</h3>
                                <ul>
                                    <li><strong>Hébergement sécurisé :</strong> Serveurs avec certifications de sécurité</li>
                                    <li><strong>Sauvegardes :</strong> Sauvegardes régulières et chiffrées</li>
                                    <li><strong>Monitoring :</strong> Surveillance continue des systèmes</li>
                                    <li><strong>Mises à jour :</strong> Maintenance et mises à jour régulières</li>
                                </ul>
                            </div>
                            
                            <div class="security-category">
                                <h3><i class="fas fa-user-shield"></i> Sécurité organisationnelle</h3>
                                <ul>
                                    <li><strong>Accès limité :</strong> Seuls les employés autorisés y ont accès</li>
                                    <li><strong>Formation :</strong> Sensibilisation à la protection des données</li>
                                    <li><strong>Audits :</strong> Vérifications régulières des pratiques</li>
                                    <li><strong>Incident response :</strong> Procédure en cas de violation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 6. Vos droits -->
                <section id="droits" class="policy-section">
                    <h2><i class="fas fa-user-check"></i> 6. Vos droits</h2>
                    <div class="policy-text">
                        <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                        
                        <div class="rights-grid">
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <h4>Droit d'accès</h4>
                                <p>Consulter vos données personnelles que nous détenons</p>
                                <a href="<?= BASE_URL ?>/user/profile" class="btn-right">Accéder à mon profil</a>
                            </div>
                            
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h4>Droit de rectification</h4>
                                <p>Corriger ou mettre à jour vos informations</p>
                                <a href="<?= BASE_URL ?>/user/edit" class="btn-right">Modifier mes données</a>
                            </div>
                            
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-trash"></i>
                                </div>
                                <h4>Droit à l'effacement</h4>
                                <p>Demander la suppression de vos données</p>
                                <a href="<?= BASE_URL ?>/contact" class="btn-right">Demander la suppression</a>
                            </div>
                            
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-download"></i>
                                </div>
                                <h4>Droit à la portabilité</h4>
                                <p>Récupérer vos données dans un format structuré</p>
                                <a href="<?= BASE_URL ?>/contact" class="btn-right">Exporter mes données</a>
                            </div>
                            
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <h4>Droit d'opposition</h4>
                                <p>Vous opposer au traitement de vos données</p>
                                <a href="<?= BASE_URL ?>/contact" class="btn-right">Exercer mon droit</a>
                            </div>
                            
                            <div class="right-card">
                                <div class="right-icon">
                                    <i class="fas fa-pause"></i>
                                </div>
                                <h4>Droit de limitation</h4>
                                <p>Limiter le traitement de certaines données</p>
                                <a href="<?= BASE_URL ?>/contact" class="btn-right">Limiter le traitement</a>
                            </div>
                        </div>

                        <div class="rights-exercise">
                            <h3><i class="fas fa-envelope"></i> Comment exercer vos droits</h3>
                            <p>Pour exercer vos droits, vous pouvez :</p>
                            <ul>
                                <li>Utiliser les liens directs ci-dessus pour les droits les plus courants</li>
                                <li>Nous contacter par e-mail à <strong>privacy@algocodebf.com</strong></li>
                                <li>Nous écrire à notre adresse postale</li>
                                <li>Nous répondrons dans un délai de 30 jours maximum</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 7. Cookies -->
                <section id="cookies" class="policy-section">
                    <h2><i class="fas fa-cookie-bite"></i> 7. Cookies et technologies similaires</h2>
                    <div class="policy-text">
                        <p>Nous utilisons des cookies et technologies similaires pour améliorer votre expérience :</p>
                        
                        <div class="cookies-types">
                            <div class="cookie-type">
                                <h3><i class="fas fa-cog"></i> Cookies essentiels</h3>
                                <p><strong>Nécessaires au fonctionnement</strong></p>
                                <ul>
                                    <li>Authentification et session</li>
                                    <li>Préférences de langue</li>
                                    <li>Sécurité et prévention de la fraude</li>
                                </ul>
                                <span class="cookie-status required">Requis</span>
                            </div>
                            
                            <div class="cookie-type">
                                <h3><i class="fas fa-chart-bar"></i> Cookies d'analyse</h3>
                                <p><strong>Amélioration des services</strong></p>
                                <ul>
                                    <li>Statistiques d'utilisation anonymes</li>
                                    <li>Performance du site</li>
                                    <li>Optimisation de l'expérience utilisateur</li>
                                </ul>
                                <span class="cookie-status optional">Optionnel</span>
                            </div>
                            
                            <div class="cookie-type">
                                <h3><i class="fas fa-bullhorn"></i> Cookies marketing</h3>
                                <p><strong>Personnalisation du contenu</strong></p>
                                <ul>
                                    <li>Recommandations personnalisées</li>
                                    <li>Publicité ciblée (si applicable)</li>
                                    <li>Newsletter et communications</li>
                                </ul>
                                <span class="cookie-status optional">Optionnel</span>
                            </div>
                        </div>

                        <div class="cookie-controls">
                            <h3><i class="fas fa-sliders-h"></i> Gestion des cookies</h3>
                            <p>Vous pouvez gérer vos préférences de cookies :</p>
                            <ul>
                                <li>Via les paramètres de votre navigateur</li>
                                <li>En nous contactant pour désactiver certains cookies</li>
                                <li>En utilisant notre bannière de consentement</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 8. Conservation -->
                <section id="conservation" class="policy-section">
                    <h2><i class="fas fa-clock"></i> 8. Conservation des données</h2>
                    <div class="policy-text">
                        <p>Nous conservons vos données uniquement le temps nécessaire aux finalités définies :</p>
                        
                        <div class="retention-table">
                            <div class="retention-item">
                                <h4><i class="fas fa-user"></i> Données de compte</h4>
                                <p><strong>Durée :</strong> Tant que votre compte est actif + 3 ans</p>
                                <p><strong>Raison :</strong> Fourniture du service et obligations légales</p>
                            </div>
                            
                            <div class="retention-item">
                                <h4><i class="fas fa-comment"></i> Contenu publié</h4>
                                <p><strong>Durée :</strong> Tant que votre compte est actif</p>
                                <p><strong>Raison :</strong> Préservation du contenu communautaire</p>
                            </div>
                            
                            <div class="retention-item">
                                <h4><i class="fas fa-chart-line"></i> Données d'utilisation</h4>
                                <p><strong>Durée :</strong> 2 ans maximum</p>
                                <p><strong>Raison :</strong> Amélioration des services et statistiques</p>
                            </div>
                            
                            <div class="retention-item">
                                <h4><i class="fas fa-envelope"></i> Communications</h4>
                                <p><strong>Durée :</strong> 3 ans maximum</p>
                                <p><strong>Raison :</strong> Support client et historique</p>
                            </div>
                            
                            <div class="retention-item">
                                <h4><i class="fas fa-shield-alt"></i> Données de sécurité</h4>
                                <p><strong>Durée :</strong> 1 an maximum</p>
                                <p><strong>Raison :</strong> Protection contre la fraude et abus</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 9. Mineurs -->
                <section id="mineurs" class="policy-section">
                    <h2><i class="fas fa-child"></i> 9. Protection des mineurs</h2>
                    <div class="policy-text">
                        <div class="minors-protection">
                            <div class="protection-item">
                                <h3><i class="fas fa-calendar-check"></i> Âge minimum</h3>
                                <p>Notre plateforme est destinée aux utilisateurs de <strong>16 ans et plus</strong>. Les mineurs de moins de 16 ans doivent obtenir le consentement de leurs parents ou tuteurs légaux.</p>
                            </div>
                            
                            <div class="protection-item">
                                <h3><i class="fas fa-user-shield"></i> Protection renforcée</h3>
                                <ul>
                                    <li>Contenu adapté à l'âge</li>
                                    <li>Modération renforcée des discussions</li>
                                    <li>Limitation des informations personnelles</li>
                                    <li>Signalement facilité des contenus inappropriés</li>
                                </ul>
                            </div>
                            
                            <div class="protection-item">
                                <h3><i class="fas fa-exclamation-triangle"></i> Signaler un problème</h3>
                                <p>Si vous êtes parent et que vous découvrez que votre enfant a fourni des informations personnelles sans votre consentement, contactez-nous immédiatement.</p>
                                <a href="<?= BASE_URL ?>/contact" class="btn btn-warning">
                                    <i class="fas fa-flag"></i> Signaler un problème
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 10. Modifications -->
                <section id="modifications" class="policy-section">
                    <h2><i class="fas fa-edit"></i> 10. Modifications de la politique</h2>
                    <div class="policy-text">
                        <p>Nous pouvons mettre à jour cette politique de confidentialité pour refléter :</p>
                        <ul>
                            <li>Les changements dans nos services</li>
                            <li>Les évolutions légales et réglementaires</li>
                            <li>Les améliorations de nos pratiques</li>
                        </ul>
                        
                        <div class="modification-process">
                            <h3><i class="fas fa-bell"></i> Notification des changements</h3>
                            <p>En cas de modification significative, nous vous informerons par :</p>
                            <ul>
                                <li>Notification sur la plateforme</li>
                                <li>E-mail à votre adresse enregistrée</li>
                                <li>Bannière sur le site web</li>
                            </ul>
                            <p>La date de dernière mise à jour est indiquée en haut de cette page.</p>
                        </div>
                    </div>
                </section>

                <!-- 11. Contact -->
                <section id="contact" class="policy-section">
                    <h2><i class="fas fa-envelope"></i> 11. Contact</h2>
                    <div class="policy-text">
                        <p>Pour toute question concernant cette politique de confidentialité ou vos données personnelles :</p>
                        
                        <div class="contact-info">
                            <div class="contact-method">
                                <h3><i class="fas fa-envelope"></i> E-mail</h3>
                                <p><strong>Délégué à la Protection des Données</strong></p>
                                <p>📧 <a href="mailto:privacy@algocodebf.com">privacy@algocodebf.com</a></p>
                            </div>
                            
                            <div class="contact-method">
                                <h3><i class="fas fa-map-marker-alt"></i> Adresse postale</h3>
                                <p><strong>AlgoCodeBF</strong></p>
                                <p>🏢 Service Protection des Données<br>
                                Ouagadougou, Burkina Faso</p>
                            </div>
                            
                            <div class="contact-method">
                                <h3><i class="fas fa-phone"></i> Téléphone</h3>
                                <p>📞 <a href="tel:+226XXXXXXXX">+226 XX XX XX XX</a></p>
                                <p><small>Lundi - Vendredi, 9h - 17h (GMT)</small></p>
                            </div>
                        </div>
                        
                        <div class="response-time">
                            <h3><i class="fas fa-clock"></i> Délais de réponse</h3>
                            <ul>
                                <li><strong>Questions générales :</strong> 48 heures</li>
                                <li><strong>Exercice de droits :</strong> 30 jours maximum</li>
                                <li><strong>Urgences de sécurité :</strong> 24 heures</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>

<style>
/* ===================================
   STYLES POLITIQUE DE CONFIDENTIALITÉ
   =================================== */

.policy-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.policy-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.policy-hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.policy-hero h1 {
    font-size: 3rem;
    margin-bottom: 20px;
    font-weight: 800;
}

.policy-subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.policy-meta {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}

.policy-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    opacity: 0.8;
}

.policy-content {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.policy-wrapper {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.policy-toc {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.policy-toc h3 {
    margin-bottom: 20px;
    color: var(--primary-color);
    font-size: 1.2rem;
}

.policy-toc ul {
    list-style: none;
    padding: 0;
}

.policy-toc li {
    margin-bottom: 8px;
}

.policy-toc a {
    color: #6c757d;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 8px;
    display: block;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.policy-toc a:hover {
    background: rgba(200, 16, 46, 0.1);
    color: var(--primary-color);
    transform: translateX(5px);
}

.policy-main {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.policy-section {
    margin-bottom: 50px;
    scroll-margin-top: 100px;
}

.policy-section h2 {
    color: var(--primary-color);
    font-size: 1.8rem;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.policy-text {
    line-height: 1.7;
    color: #444;
}

.policy-text h3 {
    color: var(--dark-color);
    margin: 30px 0 15px;
    font-size: 1.3rem;
}

.policy-text h4 {
    color: var(--dark-color);
    margin: 20px 0 10px;
    font-size: 1.1rem;
}

.highlight-box {
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.05), rgba(0, 106, 78, 0.05));
    border: 2px solid rgba(200, 16, 46, 0.1);
    border-radius: 12px;
    padding: 25px;
    margin: 25px 0;
}

.highlight-box h4 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.data-categories {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.data-category {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
}

.data-category h4 {
    color: var(--primary-color);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.auto-data {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.auto-data-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.auto-data-item h4 {
    color: var(--primary-color);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.usage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.usage-card {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
}

.usage-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.usage-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 1.5rem;
}

.usage-card h4 {
    color: var(--dark-color);
    margin-bottom: 10px;
}

.legal-basis {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin: 25px 0;
}

.legal-basis h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sharing-scenarios {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.sharing-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
}

.sharing-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.sharing-content h4 {
    color: var(--dark-color);
    margin-bottom: 8px;
}

.security-measures {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin: 25px 0;
}

.security-category {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    border-top: 4px solid var(--primary-color);
}

.security-category h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.right-card {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
}

.right-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.right-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 1.5rem;
}

.right-card h4 {
    color: var(--dark-color);
    margin-bottom: 10px;
}

.btn-right {
    display: inline-block;
    padding: 8px 16px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 10px;
    transition: all 0.3s ease;
}

.btn-right:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
}

.rights-exercise {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin: 25px 0;
}

.rights-exercise h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.cookies-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.cookie-type {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 25px;
    position: relative;
}

.cookie-type h3 {
    color: var(--dark-color);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.cookie-status {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.cookie-status.required {
    background: #dc3545;
    color: white;
}

.cookie-status.optional {
    background: #28a745;
    color: white;
}

.cookie-controls {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin: 25px 0;
}

.cookie-controls h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.retention-table {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 25px 0;
}

.retention-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
}

.retention-item h4 {
    color: var(--primary-color);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.minors-protection {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin: 25px 0;
}

.protection-item {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    border-top: 4px solid var(--primary-color);
}

.protection-item h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modification-process {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin: 25px 0;
}

.modification-process h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin: 25px 0;
}

.contact-method {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
}

.contact-method:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.contact-method h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.response-time {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    margin: 25px 0;
}

.response-time h3 {
    color: var(--primary-color);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Responsive */
@media (max-width: 992px) {
    .policy-wrapper {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .policy-toc {
        position: static;
        order: 2;
    }
    
    .policy-main {
        order: 1;
    }
}

@media (max-width: 768px) {
    .policy-hero h1 {
        font-size: 2.2rem;
    }
    
    .policy-meta {
        flex-direction: column;
        gap: 15px;
    }
    
    .policy-main {
        padding: 25px;
    }
    
    .data-categories,
    .usage-grid,
    .rights-grid,
    .cookies-types {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once __DIR__ . '/../layouts/footer.php';
?>
