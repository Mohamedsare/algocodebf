<?php require_once VIEWS . '/layouts/header.php'; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Connexion</h1>
                    <p>Connectez-vous à votre compte AlgoCodeBF</p>
                </div>

                <form action="<?= BASE_URL ?>/auth/login" method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control <?= isset($_SESSION['errors']['email']) ? 'is-invalid' : '' ?>"
                            value="<?= $_SESSION['old']['email'] ?? '' ?>"
                            required
                        >
                        <?php if (isset($_SESSION['errors']['email'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['email'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (isset($_SESSION['errors']['password'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['password'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <a href="<?= BASE_URL ?>/auth/forgotPassword" class="text-link">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </form>

                <div class="auth-footer">
                    <p>Vous n'avez pas de compte ? <a href="<?= BASE_URL ?>/auth/register">S'inscrire</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
unset($_SESSION['errors']);
unset($_SESSION['old']);
require_once VIEWS . '/layouts/footer.php'; 
?>


