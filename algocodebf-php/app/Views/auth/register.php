<?php require_once VIEWS . '/layouts/header.php'; ?>

<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-card auth-card-large">
                <div class="auth-header">
                    <h1>Inscription</h1>
                    <p>Rejoignez la communauté des informaticiens du Burkina Faso</p>
                </div>

                <form action="<?= BASE_URL ?>/auth/register" method="POST" class="auth-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input 
                                type="text" 
                                id="prenom" 
                                name="prenom" 
                                class="form-control <?= isset($_SESSION['errors']['prenom']) ? 'is-invalid' : '' ?>"
                                value="<?= $_SESSION['old']['prenom'] ?? '' ?>"
                                required
                            >
                            <?php if (isset($_SESSION['errors']['prenom'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['prenom'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input 
                                type="text" 
                                id="nom" 
                                name="nom" 
                                class="form-control <?= isset($_SESSION['errors']['nom']) ? 'is-invalid' : '' ?>"
                                value="<?= $_SESSION['old']['nom'] ?? '' ?>"
                                required
                            >
                            <?php if (isset($_SESSION['errors']['nom'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['nom'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email *</label>
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
                        <label for="phone">Numéro de téléphone *</label>
                        <div class="input-group">
                            <span class="input-group-text">+226</span>
                            <input 
                                type="text" 
                                id="phone" 
                                name="phone" 
                                class="form-control <?= isset($_SESSION['errors']['phone']) ? 'is-invalid' : '' ?>"
                                value="<?= $_SESSION['old']['phone'] ?? '' ?>"
                                placeholder="XX XX XX XX"
                                required
                            >
                        </div>
                        <?php if (isset($_SESSION['errors']['phone'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['phone'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="university">Université / École *</label>
                            <input 
                                type="text" 
                                id="university" 
                                name="university" 
                                class="form-control <?= isset($_SESSION['errors']['university']) ? 'is-invalid' : '' ?>"
                                value="<?= $_SESSION['old']['university'] ?? '' ?>"
                                placeholder="Ex: Université Joseph Ki-Zerbo"
                                required
                            >
                            <?php if (isset($_SESSION['errors']['university'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['university'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="faculty">Filière / Spécialité *</label>
                            <input 
                                type="text" 
                                id="faculty" 
                                name="faculty" 
                                class="form-control <?= isset($_SESSION['errors']['faculty']) ? 'is-invalid' : '' ?>"
                                value="<?= $_SESSION['old']['faculty'] ?? '' ?>"
                                placeholder="Ex: Informatique, Génie Logiciel"
                                required
                            >
                            <?php if (isset($_SESSION['errors']['faculty'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['faculty'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="city">Ville *</label>
                        <select id="city" name="city" class="form-control" required>
                            <option value="">Sélectionnez une ville</option>
                            <option value="Ouagadougou">Ouagadougou</option>
                            <option value="Bobo-Dioulasso">Bobo-Dioulasso</option>
                            <option value="Koudougou">Koudougou</option>
                            <option value="Ouahigouya">Ouahigouya</option>
                            <option value="Banfora">Banfora</option>
                            <option value="Dédougou">Dédougou</option>
                            <option value="Kaya">Kaya</option>
                            <option value="Tenkodogo">Tenkodogo</option>
                            <option value="Fada N'Gourma">Fada N'Gourma</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <?php if (isset($_SESSION['errors']['city'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['city'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control <?= isset($_SESSION['errors']['password']) ? 'is-invalid' : '' ?>"
                                required
                            >
                            <small>Minimum 8 caractères, avec majuscule, minuscule et chiffre</small>
                            <?php if (isset($_SESSION['errors']['password'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['password'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="password_confirm">Confirmer le mot de passe *</label>
                            <input 
                                type="password" 
                                id="password_confirm" 
                                name="password_confirm" 
                                class="form-control <?= isset($_SESSION['errors']['password_confirm']) ? 'is-invalid' : '' ?>"
                                required
                            >
                            <?php if (isset($_SESSION['errors']['password_confirm'])): ?>
                                <div class="invalid-feedback"><?= $_SESSION['errors']['password_confirm'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                </form>

                <div class="auth-footer">
                    <p>Vous avez déjà un compte ? <a href="<?= BASE_URL ?>/auth/login">Se connecter</a></p>
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

