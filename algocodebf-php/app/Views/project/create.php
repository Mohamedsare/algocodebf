<?php
$pageTitle = 'Créer un Projet - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>

<section class="create-project-section">
    <div class="container">
        <!-- Header -->
        <div class="page-header-create">
            <div class="header-content">
                <h1><i class="fas fa-rocket"></i> Créer un Projet</h1>
                <p>Lancez votre projet et trouvez des collaborateurs talentueux</p>
            </div>
            <a href="<?= BASE_URL ?>/project/index" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour aux projets
            </a>
        </div>

        <div class="create-project-wrapper">
            <!-- Main Form -->
            <div class="form-main">
                <form action="<?= BASE_URL ?>/project/create" method="POST" class="project-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                    <!-- Informations de base -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            <h3>Informations de base</h3>
                        </div>

                        <!-- Titre -->
                        <div class="form-group">
                            <label for="title">
                                Titre du projet *
                                <span class="field-hint">Un titre clair et accrocheur</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   placeholder="Ex: Plateforme e-commerce pour l'artisanat burkinabè"
                                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                                   required>
                            <?php if (isset($errors['title'])): ?>
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> <?= $errors['title'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">
                                Description *
                                <span class="field-hint">Décrivez votre projet en détail</span>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control textarea-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                                      rows="6"
                                      placeholder="Décrivez l'objectif, les technologies utilisées, les fonctionnalités principales..."
                                      required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                            <div class="textarea-footer">
                                <span class="char-counter" id="charCounter">0 caractères (min: 20)</span>
                            </div>
                            <?php if (isset($errors['description'])): ?>
                                <span class="error-message">
                                    <i class="fas fa-exclamation-circle"></i> <?= $errors['description'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Liens du projet -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-link"></i>
                            <h3>Liens du projet</h3>
                        </div>

                        <div class="form-row">
                            <!-- GitHub -->
                            <div class="form-group">
                                <label for="github_link">
                                    <i class="fab fa-github"></i> Lien GitHub
                                    <span class="field-hint">Repository du code source</span>
                                </label>
                                <input type="url" 
                                       id="github_link" 
                                       name="github_link" 
                                       class="form-control"
                                       placeholder="https://github.com/username/repo"
                                       value="<?= htmlspecialchars($old['github_link'] ?? '') ?>">
                            </div>

                            <!-- Demo -->
                            <div class="form-group">
                                <label for="demo_link">
                                    <i class="fas fa-external-link-alt"></i> Lien Démo
                                    <span class="field-hint">Site web ou démo en ligne</span>
                                </label>
                                <input type="url" 
                                       id="demo_link" 
                                       name="demo_link" 
                                       class="form-control"
                                       placeholder="https://demo.example.com"
                                       value="<?= htmlspecialchars($old['demo_link'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres du projet -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-cog"></i>
                            <h3>Paramètres du projet</h3>
                        </div>

                        <div class="form-row">
                            <!-- Statut -->
                            <div class="form-group">
                                <label for="status">
                                    <i class="fas fa-tasks"></i> Statut du projet
                                </label>
                                <select id="status" 
                                        name="status" 
                                        class="form-control select-control">
                                    <option value="planning" <?= ($old['status'] ?? '') == 'planning' ? 'selected' : '' ?>>
                                        📋 Planification
                                    </option>
                                    <option value="in_progress" <?= ($old['status'] ?? '') == 'in_progress' ? 'selected' : '' ?>>
                                        🚀 En cours
                                    </option>
                                    <option value="completed" <?= ($old['status'] ?? '') == 'completed' ? 'selected' : '' ?>>
                                        ✅ Terminé
                                    </option>
                                </select>
                            </div>

                            <!-- Visibilité -->
                            <div class="form-group">
                                <label for="visibility">
                                    <i class="fas fa-eye"></i> Visibilité
                                </label>
                                <select id="visibility" 
                                        name="visibility" 
                                        class="form-control select-control">
                                    <option value="public" <?= ($old['visibility'] ?? 'public') == 'public' ? 'selected' : '' ?>>
                                        🌍 Public
                                    </option>
                                    <option value="private" <?= ($old['visibility'] ?? '') == 'private' ? 'selected' : '' ?>>
                                        🔒 Privé
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Recherche de membres -->
                        <div class="form-group">
                            <div class="checkbox-card">
                                <input type="checkbox" 
                                       id="looking_for_members" 
                                       name="looking_for_members"
                                       <?= isset($old['looking_for_members']) && $old['looking_for_members'] ? 'checked' : '' ?>>
                                <label for="looking_for_members" class="checkbox-label">
                                    <div class="checkbox-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="checkbox-content">
                                        <strong>Recherche de membres</strong>
                                        <p>Je recherche des collaborateurs pour ce projet</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-rocket"></i> Créer le projet
                        </button>
                        <a href="<?= BASE_URL ?>/project/index" class="btn-cancel">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>

            <!-- Sidebar Tips -->
            <aside class="tips-sidebar">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Conseils pour réussir</h3>
                    <ul class="tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Choisissez un titre clair et descriptif
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Détaillez les technologies utilisées
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Précisez les compétences recherchées
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Ajoutez des liens vers GitHub et démo
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Mettez à jour le statut régulièrement
                        </li>
                    </ul>
                </div>

                <div class="tip-card stats-card">
                    <div class="tip-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Statistiques</h3>
                    <div class="stats-info">
                        <div class="stat-item">
                            <span class="stat-value">+50%</span>
                            <span class="stat-label">Plus de visibilité</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">3x</span>
                            <span class="stat-label">Plus de collaborations</span>
                        </div>
                    </div>
                    <p class="stats-note">
                        Les projets bien documentés attirent plus de collaborateurs
                    </p>
                </div>

                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Gagnez des badges</h3>
                    <p>Créez des projets pour débloquer des badges et améliorer votre profil !</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.create-project-section {
    padding: 40px 0 80px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: calc(100vh - 140px);
}

/* Header */
.page-header-create {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 30px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.header-content h1 {
    margin: 0 0 10px;
    font-size: 2.2rem;
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--dark-color);
}

.header-content p {
    margin: 0;
    font-size: 1.1rem;
    color: #6c757d;
}

.btn-back {
    padding: 12px 25px;
    background: #f8f9fa;
    color: var(--dark-color);
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.btn-back:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: translateX(-5px);
}

/* Wrapper */
.create-project-wrapper {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 30px;
}

/* Form Main */
.form-main {
    background: white;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.form-section:last-of-type {
    border-bottom: none;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 25px;
}

.section-title i {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.section-title h3 {
    margin: 0;
    font-size: 1.4rem;
    color: var(--dark-color);
}

/* Form Groups */
.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 1rem;
}

.field-hint {
    display: block;
    font-size: 0.85rem;
    font-weight: 400;
    color: #6c757d;
    margin-top: 3px;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

.form-control.is-invalid {
    border-color: var(--danger-color);
}

.textarea-control {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}

.textarea-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}

.char-counter {
    font-size: 0.85rem;
    color: #6c757d;
}

.select-control {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236c757d' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 12px;
    appearance: none;
}

.error-message {
    display: block;
    margin-top: 8px;
    color: var(--danger-color);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Form Row */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* Checkbox Card */
.checkbox-card {
    position: relative;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
}

.checkbox-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.15);
}

.checkbox-card input[type="checkbox"] {
    position: absolute;
    opacity: 0;
}

.checkbox-card input[type="checkbox"]:checked ~ .checkbox-label {
    color: var(--primary-color);
}

.checkbox-card input[type="checkbox"]:checked ~ .checkbox-label .checkbox-icon {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    transform: scale(1.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    user-select: none;
}

.checkbox-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary-color);
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.checkbox-content {
    flex: 1;
}

.checkbox-content strong {
    display: block;
    font-size: 1.1rem;
    margin-bottom: 4px;
}

.checkbox-content p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.btn-submit {
    flex: 1;
    padding: 16px 35px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
}

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(52, 152, 219, 0.4);
}

.btn-cancel {
    padding: 16px 30px;
    background: white;
    color: #6c757d;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-cancel:hover {
    border-color: var(--danger-color);
    color: var(--danger-color);
}

/* Tips Sidebar */
.tips-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tip-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.tip-icon {
    width: 55px;
    height: 55px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 15px;
}

.tip-card h3 {
    margin: 0 0 15px;
    font-size: 1.2rem;
    color: var(--dark-color);
}

.tip-card p {
    margin: 0;
    color: #6c757d;
    line-height: 1.6;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    padding: 10px 0;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    color: var(--dark-color);
    font-size: 0.95rem;
}

.tips-list i {
    color: var(--secondary-color);
    margin-top: 3px;
}

/* Stats Card */
.stats-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.08), rgba(46, 204, 113, 0.08));
    border-radius: 10px;
}

.stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 5px;
}

.stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
}

.stats-note {
    font-size: 0.85rem;
    color: #6c757d;
    font-style: italic;
}

/* Responsive */
@media (max-width: 992px) {
    .create-project-wrapper {
        grid-template-columns: 1fr;
    }
    
    .tips-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .page-header-create {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .header-content h1 {
        font-size: 1.6rem;
        justify-content: center;
    }
    
    .form-main {
        padding: 25px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Character counter
const descriptionTextarea = document.getElementById('description');
const charCounter = document.getElementById('charCounter');

if (descriptionTextarea && charCounter) {
    descriptionTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCounter.textContent = `${length} caractères (min: 20)`;
        
        if (length >= 20) {
            charCounter.style.color = 'var(--secondary-color)';
        } else {
            charCounter.style.color = '#6c757d';
        }
    });
    
    // Initial count
    const initialLength = descriptionTextarea.value.length;
    charCounter.textContent = `${initialLength} caractères (min: 20)`;
}

// Form validation
const projectForm = document.querySelector('.project-form');
if (projectForm) {
    projectForm.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const description = document.getElementById('description').value.trim();
        
        if (title.length < 5) {
            e.preventDefault();
            alert('Le titre doit contenir au moins 5 caractères');
            document.getElementById('title').focus();
            return;
        }
        
        if (description.length < 20) {
            e.preventDefault();
            alert('La description doit contenir au moins 20 caractères');
            document.getElementById('description').focus();
            return;
        }
    });
}

// URL validation
const urlInputs = document.querySelectorAll('input[type="url"]');
urlInputs.forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value && !this.value.match(/^https?:\/\/.+/)) {
            this.setCustomValidity('Veuillez entrer une URL valide (http:// ou https://)');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

