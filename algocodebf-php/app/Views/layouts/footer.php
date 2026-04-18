    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><?= htmlspecialchars($GLOBALS['site_settings']['site_name'] ?? 'AlgoCodeBF') ?></h3>
                    <p><?= htmlspecialchars($GLOBALS['site_settings']['site_description'] ?? 'Hub numérique des informaticiens du Burkina Faso') ?></p>
                    <div class="social-links">
                        <?php if (!empty($GLOBALS['site_settings']['social_facebook'])): ?>
                            <a href="<?= htmlspecialchars($GLOBALS['site_settings']['social_facebook']) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($GLOBALS['site_settings']['social_twitter'])): ?>
                            <a href="<?= htmlspecialchars($GLOBALS['site_settings']['social_twitter']) ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($GLOBALS['site_settings']['social_linkedin'])): ?>
                            <a href="<?= htmlspecialchars($GLOBALS['site_settings']['social_linkedin']) ?>" target="_blank" rel="noopener"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($GLOBALS['site_settings']['social_github'])): ?>
                            <a href="<?= htmlspecialchars($GLOBALS['site_settings']['social_github']) ?>" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
                        <?php endif; ?>
                        <?php if (empty($GLOBALS['site_settings']['social_facebook']) && empty($GLOBALS['site_settings']['social_twitter']) && empty($GLOBALS['site_settings']['social_linkedin']) && empty($GLOBALS['site_settings']['social_github'])): ?>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/home/about">À propos</a></li>
                        <li><a href="<?= BASE_URL ?>/forum/index">Forum</a></li>
                        <li><a href="<?= BASE_URL ?>/tutorial/index">Tutoriels</a></li>
                        <li><a href="<?= BASE_URL ?>/job/index">Opportunités</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Communauté</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/user/index">Membres</a></li>
                        <li><a href="<?= BASE_URL ?>/project/index">Projets</a></li>
                        <li><a href="<?= BASE_URL ?>/user/leaderboard">Classement</a></li>
                        <li><a href="<?= BASE_URL ?>/blog/index">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> <?= htmlspecialchars($GLOBALS['site_settings']['contact_email'] ?? 'contact@hubtech.bf') ?></li>
                        <?php if (!empty($GLOBALS['site_settings']['contact_phone'])): ?>
                            <li><i class="fas fa-phone"></i> <?= htmlspecialchars($GLOBALS['site_settings']['contact_phone']) ?></li>
                        <?php endif; ?>
                        <?php if (!empty($GLOBALS['site_settings']['contact_address'])): ?>
                            <li><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($GLOBALS['site_settings']['contact_address']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-legal">
                    <div class="legal-links">
                        <a href="<?= BASE_URL ?>/policy/privacy">Politique de confidentialité</a>
                        <a href="<?= BASE_URL ?>/policy/terms">Conditions d'utilisation</a>
                        <a href="<?= BASE_URL ?>/policy/legal">Mentions légales</a>
                    </div>
                    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($GLOBALS['site_settings']['site_name'] ?? 'AlgoCodeBF') ?>. Tous droits réservés. Développé avec 💚 au Burkina Faso</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bouton Retour en Haut 🇧🇫 -->
    <?php if (!isset($is_admin_page) && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === false): ?>
    <button id="scrollToTop" class="scroll-to-top" aria-label="Retour en haut" title="Retour en haut">
        <i class="fas fa-arrow-up"></i>
        <span class="scroll-text">Haut</span>
    </button>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="<?= BASE_URL ?>/public/js/main.js?v=<?= time() ?>&force=<?= rand(1000,9999) ?>"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/newsletter.js"></script>
</body>
</html>

