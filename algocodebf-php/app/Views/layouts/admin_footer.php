    </main>

    <!-- Footer Admin simplifié -->
    <footer class="footer"
        style="margin-top: auto; padding: 20px 0; background: #f8f9fa; border-top: 1px solid #dee2e6;">
        <div class="container">
            <div style="text-align: center; color: #6c757d;">
                <p>&copy; <?= date('Y') ?>
                    <?= htmlspecialchars($GLOBALS['site_settings']['site_name'] ?? 'AlgoCodeBF') ?> Admin Panel. Tous
                    droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Pas de main.js pour éviter les conflits -->
    </body>

    </html>