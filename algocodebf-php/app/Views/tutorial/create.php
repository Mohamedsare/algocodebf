<?php
$pageTitle = 'Créer un Tutoriel - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Récupérer les anciennes valeurs et erreurs
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>

<section class="create-tutorial-section">
    <div class="container">
        <!-- Header -->
        <div class="page-header-create">
            <div class="header-content">
                <h1><i class="fas fa-graduation-cap"></i> Créer un Tutoriel</h1>
                <p>Partagez vos connaissances et aidez la communauté à apprendre</p>
            </div>
            <a href="<?= BASE_URL ?>/tutorial/index" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour aux tutoriels
            </a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="create-tutorial-wrapper">
            <!-- Main Form -->
            <div class="form-main">
                <form action="<?= BASE_URL ?>/tutorial/create" method="POST" class="tutorial-form"
                    enctype="multipart/form-data">
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
                                Titre du tutoriel *
                                <span class="field-hint">Un titre clair et explicite</span>
                            </label>
                            <input type="text" id="title" name="title"
                                class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                placeholder="Ex: Guide complet pour débuter avec React.js"
                                value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
                            <?php if (isset($errors['title'])): ?>
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?= $errors['title'] ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">
                                Description courte *
                                <span class="field-hint">Résumez votre tutoriel en quelques phrases</span>
                            </label>
                            <textarea id="description" name="description"
                                class="form-control textarea-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                                rows="4" placeholder="Décrivez brièvement ce que les utilisateurs vont apprendre..."
                                required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                            <div class="textarea-footer">
                                <span class="char-counter" id="descCharCounter">0 caractères (min: 20)</span>
                            </div>
                            <?php if (isset($errors['description'])): ?>
                            <span class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?= $errors['description'] ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Type et Catégorie -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-th-large"></i>
                            <h3>Classification</h3>
                        </div>

                        <div class="form-row">
                            <!-- Type -->
                            <div class="form-group">
                                <label for="type">
                                    <i class="fas fa-file-alt"></i> Type de tutoriel *
                                </label>
                                <select id="type" name="type" class="form-control select-control" required>
                                    <option value="">-- Choisir un type --</option>
                                    <option value="video" <?= ($old['type'] ?? '') == 'video' ? 'selected' : '' ?>>
                                        🎥 Vidéo
                                    </option>
                                    <option value="text" <?= ($old['type'] ?? '') == 'text' ? 'selected' : '' ?>>
                                        📝 Texte
                                    </option>
                                    <option value="pdf" <?= ($old['type'] ?? '') == 'pdf' ? 'selected' : '' ?>>
                                        📄 PDF
                                    </option>
                                    <option value="code" <?= ($old['type'] ?? '') == 'code' ? 'selected' : '' ?>>
                                        💻 Code/Snippets
                                    </option>
                                    <option value="mixed" <?= ($old['type'] ?? '') == 'mixed' ? 'selected' : '' ?>>
                                        🔀 Mixte
                                    </option>
                                </select>
                            </div>

                            <!-- Catégorie -->
                            <div class="form-group">
                                <label for="category">
                                    <i class="fas fa-tag"></i> Catégorie *
                                </label>
                                <select id="category" name="category" class="form-control select-control" required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"
                                        <?= ($old['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Niveau de difficulté -->
                        <div class="form-group">
                            <label for="level">
                                <i class="fas fa-signal"></i> Niveau de difficulté *
                                <span class="field-hint">Pour quel public est ce tutoriel</span>
                            </label>
                            <select id="level" name="level" class="form-control select-control" required>
                                <option value="">-- Choisir un niveau --</option>
                                <option value="Débutant" <?= ($old['level'] ?? '') == 'Débutant' ? 'selected' : '' ?>>
                                    ⭐ Débutant
                                </option>
                                <option value="Intermédiaire"
                                    <?= ($old['level'] ?? '') == 'Intermédiaire' ? 'selected' : '' ?>>
                                    ⭐⭐ Intermédiaire
                                </option>
                                <option value="Avancé" <?= ($old['level'] ?? '') == 'Avancé' ? 'selected' : '' ?>>
                                    ⭐⭐⭐ Avancé
                                </option>
                                <option value="Expert" <?= ($old['level'] ?? '') == 'Expert' ? 'selected' : '' ?>>
                                    ⭐⭐⭐⭐ Expert
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-file-text"></i>
                            <h3>Contenu du tutoriel</h3>
                        </div>

                        <div class="form-group">
                            <label for="content">
                                Contenu détaillé *
                                <span class="field-hint">Expliquez étape par étape avec formatage riche et code
                                    coloré</span>
                            </label>
                            <textarea id="content" name="content"
                                class="form-control tinymce-editor"><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                            <small class="form-hint">
                                <i class="fas fa-info-circle"></i> Utilisez l'éditeur pour formater le texte, insérer
                                des images, et ajouter du code avec coloration syntaxique
                            </small>
                        </div>
                    </div>

                    <!-- Sommaire et Chapitres -->
                    <div class="form-section" id="chapters-section">
                        <div class="section-title">
                            <i class="fas fa-list-ol"></i>
                            <h3>Sommaire de la formation</h3>
                        </div>
                        <p class="section-description">
                            Organisez votre formation en chapitres. Chaque chapitre peut être associé à une vidéo.
                        </p>

                        <div id="chapters-container">
                            <!-- Les chapitres seront ajoutés dynamiquement ici -->
                        </div>

                        <button type="button" id="add-chapter-btn" class="btn-add-chapter">
                            <i class="fas fa-plus"></i> Ajouter un chapitre
                        </button>

                        <!-- Champ caché pour envoyer les chapitres -->
                        <input type="hidden" id="chapters-input" name="chapters" value="">
                    </div>

                    <!-- Vidéos de la formation -->
                    <div class="form-section" id="videos-section">
                        <div class="section-title">
                            <i class="fas fa-video"></i>
                            <h3>Vidéos de la formation</h3>
                        </div>
                        <p class="section-description">
                            Uploadez plusieurs vidéos pour votre formation (jusqu'à 500MB par vidéo)
                        </p>

                        <!-- Zone d'upload multiple -->
                        <div class="form-group">
                            <label for="videos">
                                <i class="fas fa-upload"></i> Vidéos de formation *
                                <span class="field-hint">Sélectionnez une ou plusieurs vidéos (Max 500MB par
                                    vidéo)</span>
                            </label>
                            <div class="video-upload-area">
                                <input type="file" id="videos" name="videos[]" class="video-input"
                                    accept="video/*,.mp4,.avi,.mov,.wmv,.webm,.mpeg" multiple>
                                <label for="videos" class="video-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Cliquez pour sélectionner des vidéos ou glissez-déposez ici</span>
                                    <small>Formats acceptés: MP4, AVI, MOV, WebM, MPEG (Max 500MB par vidéo)</small>
                                </label>
                                <div id="videos-preview" class="videos-preview"></div>
                            </div>
                        </div>

                        <!-- Liste des vidéos uploadées -->
                        <div id="videos-list" class="videos-list"></div>
                    </div>

                    <!-- Ressources supplémentaires -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-paperclip"></i>
                            <h3>Ressources supplémentaires</h3>
                        </div>

                        <!-- Lien externe -->
                        <div class="form-group">
                            <label for="external_link">
                                <i class="fas fa-external-link-alt"></i> Lien externe (optionnel)
                                <span class="field-hint">Lien vers une ressource externe (YouTube, GitHub, etc.)</span>
                            </label>
                            <input type="url" id="external_link" name="external_link" class="form-control"
                                placeholder="https://youtube.com/watch?v=..."
                                value="<?= htmlspecialchars($old['external_link'] ?? '') ?>">
                        </div>

                        <!-- Upload de fichier supplémentaire -->
                        <div class="form-group">
                            <label for="file">
                                <i class="fas fa-file"></i> Fichier supplémentaire (optionnel)
                                <span class="field-hint">PDF, images, code source (Max 5MB)</span>
                            </label>
                            <div class="file-upload-area">
                                <input type="file" id="file" name="file" class="file-input"
                                    accept="image/*,.pdf,.doc,.docx,.zip,.txt,.py,.js,.html,.css">
                                <label for="file" class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Cliquez pour ajouter un fichier</span>
                                    <small>Images, PDF, Documents, Code (Max 5MB)</small>
                                </label>
                                <div id="file-preview" class="file-preview"></div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="form-group">
                            <label for="tags">
                                <i class="fas fa-tags"></i> Tags (optionnel)
                                <span class="field-hint">Mots-clés pour faciliter la recherche</span>
                            </label>
                            <input type="text" id="tags" name="tags" class="form-control"
                                placeholder="Ex: react, javascript, débutant"
                                value="<?= htmlspecialchars($old['tags'] ?? '') ?>">
                            <small class="form-hint">Séparez les tags par des virgules</small>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-rocket"></i> Publier le tutoriel
                        </button>
                        <a href="<?= BASE_URL ?>/tutorial/index" class="btn-cancel">
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
                    <h3>Conseils pour un bon tutoriel</h3>
                    <ul class="tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Soyez clair et structuré
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Utilisez des exemples concrets
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Ajoutez des captures d'écran
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Testez votre code avant de publier
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            Répondez aux questions des utilisateurs
                        </li>
                    </ul>
                </div>

                <div class="tip-card stats-card">
                    <div class="tip-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Impact</h3>
                    <p>
                        Les tutoriels de qualité peuvent aider des centaines d'étudiants et de développeurs burkinabè à
                        progresser !
                    </p>
                </div>

                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Badges à gagner</h3>
                    <p>Créez des tutoriels pour débloquer des badges et devenir un mentor de la communauté !</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.create-tutorial-section {
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
.create-tutorial-wrapper {
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
    min-height: 100px;
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

/* TinyMCE Editor Wrapper */
.tinymce-editor {
    border: 2px solid #e9ecef;
    border-radius: 12px;
}

.tox .tox-toolbar {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
}

.tox .tox-edit-area {
    border: none !important;
}

.tox-tinymce {
    border: 2px solid #e9ecef !important;
    border-radius: 12px !important;
    transition: border-color 0.3s ease;
}

.tox-tinymce:focus-within {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1) !important;
}

/* Section Description */
.section-description {
    margin-bottom: 20px;
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.6;
}

/* Chapitres */
.chapter-item {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.chapter-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
}

.chapter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.chapter-number {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.btn-remove-chapter {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove-chapter:hover {
    background: var(--danger-color);
    color: white;
    transform: scale(1.1);
}

.chapter-content .form-group {
    margin-bottom: 15px;
}

.chapter-content .form-group:last-child {
    margin-bottom: 0;
}

.btn-add-chapter {
    width: 100%;
    padding: 14px 20px;
    background: white;
    border: 2px dashed var(--primary-color);
    border-radius: 12px;
    color: var(--primary-color);
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-add-chapter:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Vidéos multiples */
.video-upload-area {
    position: relative;
}

.video-input {
    display: none;
}

.video-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    border: 3px dashed #dee2e6;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    text-align: center;
}

.video-upload-label:hover,
.video-upload-area.drag-over .video-upload-label {
    border-color: var(--primary-color);
    background: rgba(52, 152, 219, 0.05);
}

.video-upload-label i {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.video-upload-label span {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.video-upload-label small {
    color: #6c757d;
    font-size: 0.9rem;
}

.videos-preview {
    margin-top: 15px;
}

.videos-count {
    padding: 12px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    margin: 0;
}

.videos-list {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.video-item {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.video-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
}

.video-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.video-item-number {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
}

.btn-remove-video {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: white;
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove-video:hover {
    background: var(--danger-color);
    color: white;
    transform: scale(1.1);
}

.video-item-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
}

.video-preview-small {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    background: #000;
}

.video-preview-small video {
    width: 100%;
    height: auto;
    display: block;
}

.video-item-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.video-meta {
    display: flex;
    gap: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #6c757d;
}

.video-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Indicateur de taille totale */
.total-size-indicator {
    margin-top: 20px;
    padding: 15px;
    border-radius: 10px;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
}

.size-indicator-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.size-indicator-content.success {
    border-left: 4px solid #28a745;
}

.size-indicator-content.info {
    border-left: 4px solid #17a2b8;
}

.size-indicator-content.warning {
    border-left: 4px solid #ffc107;
}

.size-indicator-content.error {
    border-left: 4px solid #dc3545;
}

.size-indicator-content i {
    margin-right: 8px;
}

.size-progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 5px;
}

.size-progress {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.3s ease;
    border-radius: 4px;
}

.size-indicator-content.warning .size-progress {
    background: linear-gradient(90deg, #ffc107, #ff9800);
}

.size-indicator-content.error .size-progress {
    background: linear-gradient(90deg, #dc3545, #c82333);
}

.size-warning,
.size-error {
    font-size: 0.9rem;
    font-weight: 600;
    margin-top: 5px;
}

.size-warning {
    color: #ffc107;
}

.size-error {
    color: #dc3545;
}

/* File Upload */
.file-upload-area {
    position: relative;
}

.file-input {
    display: none;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.file-upload-label:hover {
    border-color: var(--primary-color);
    background: rgba(52, 152, 219, 0.05);
}

.file-upload-label i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.file-upload-label span {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.file-upload-label small {
    color: #6c757d;
    font-size: 0.875rem;
}

.file-preview {
    margin-top: 15px;
}

.file-preview-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    border: 2px solid #dee2e6;
}

.file-icon {
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-info {
    flex: 1;
}

.file-info strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.file-info small {
    color: #6c757d;
}

.btn-remove-file {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove-file:hover {
    background: var(--danger-color);
    color: white;
    transform: scale(1.1);
}

.video-preview-container,
.image-preview-container {
    margin-top: 15px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.form-hint {
    font-size: 0.85rem;
    color: #6c757d;
    display: block;
    margin-top: 5px;
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

/* Alert */
.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.alert-danger {
    background: #ffebee;
    border: 1px solid #ef5350;
    color: #c62828;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

/* Responsive */
@media (max-width: 992px) {
    .create-tutorial-wrapper {
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

    /* Vidéos et chapitres responsive */
    .video-item-content {
        grid-template-columns: 1fr;
    }

    .video-preview-small {
        max-width: 100%;
        margin-bottom: 15px;
    }

    .chapter-content .form-group {
        margin-bottom: 12px;
    }

    .video-upload-label {
        padding: 30px 15px;
    }

    .video-upload-label i {
        font-size: 3rem;
    }

    .video-upload-label span {
        font-size: 1rem;
    }

    .video-meta {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<!-- TinyMCE Editor -->
<!-- Remplacez VOTRE_CLE_API_ICI par votre vraie clé obtenue sur tiny.cloud -->
<script src="https://cdn.tiny.cloud/1/y9lugfpj1jxq696s71q9d8t99y49ir7sxho2ogultsj83j8u/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>

<script>
// Initialize TinyMCE
tinymce.init({
    selector: '#content',
    height: 600,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
    ],
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
        'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | link image media | codesample | ' +
        'removeformat code fullscreen help',
    content_style: `
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 16px; 
            line-height: 1.8;
            color: #2c3e50;
            padding: 20px;
        }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #3498db;
            overflow-x: auto;
        }
        code { 
            background: #f0f0f0; 
            padding: 2px 6px; 
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e74c3c;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1, h2, h3 { color: #2c3e50; margin-top: 1.5em; }
        h1 { font-size: 2em; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        h2 { font-size: 1.5em; }
        h3 { font-size: 1.2em; }
        blockquote {
            border-left: 4px solid #3498db;
            padding-left: 20px;
            margin: 20px 0;
            color: #6c757d;
            font-style: italic;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        table td, table th {
            border: 1px solid #dee2e6;
            padding: 10px;
        }
        table th {
            background: #f8f9fa;
            font-weight: 600;
        }
    `,
    codesample_languages: [{
            text: 'HTML/XML',
            value: 'markup'
        },
        {
            text: 'JavaScript',
            value: 'javascript'
        },
        {
            text: 'CSS',
            value: 'css'
        },
        {
            text: 'PHP',
            value: 'php'
        },
        {
            text: 'Python',
            value: 'python'
        },
        {
            text: 'Java',
            value: 'java'
        },
        {
            text: 'C',
            value: 'c'
        },
        {
            text: 'C++',
            value: 'cpp'
        },
        {
            text: 'C#',
            value: 'csharp'
        },
        {
            text: 'Ruby',
            value: 'ruby'
        },
        {
            text: 'SQL',
            value: 'sql'
        },
        {
            text: 'Bash',
            value: 'bash'
        },
        {
            text: 'JSON',
            value: 'json'
        },
        {
            text: 'TypeScript',
            value: 'typescript'
        },
        {
            text: 'React JSX',
            value: 'jsx'
        },
        {
            text: 'Go',
            value: 'go'
        },
        {
            text: 'Rust',
            value: 'rust'
        },
        {
            text: 'Swift',
            value: 'swift'
        },
        {
            text: 'Kotlin',
            value: 'kotlin'
        }
    ],
    language: 'fr_FR',
    branding: false,
    promotion: false,
    resize: true,
    paste_as_text: false,
    paste_data_images: true,
    image_title: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    setup: function(editor) {
        editor.on('init', function() {
            console.log('✅ Éditeur TinyMCE initialisé avec succès!');
        });
    }
});

// Character counter for description
const descriptionTextarea = document.getElementById('description');
const descCharCounter = document.getElementById('descCharCounter');

if (descriptionTextarea && descCharCounter) {
    descriptionTextarea.addEventListener('input', function() {
        const length = this.value.length;
        descCharCounter.textContent = `${length} caractères (min: 20)`;

        if (length >= 20) {
            descCharCounter.style.color = 'var(--secondary-color)';
        } else {
            descCharCounter.style.color = '#6c757d';
        }
    });

    // Initial count
    const initialLength = descriptionTextarea.value.length;
    descCharCounter.textContent = `${initialLength} caractères (min: 20)`;
}

// ========================================
// GESTION DES CHAPITRES
// ========================================
let chapterCounter = 0;
const chaptersContainer = document.getElementById('chapters-container');
const addChapterBtn = document.getElementById('add-chapter-btn');
const chaptersInput = document.getElementById('chapters-input');

function addChapter(title = '', description = '', chapterNumber = null) {
    const chapterIndex = chapterCounter++;
    const chapterNum = chapterNumber !== null ? chapterNumber : chapterIndex + 1;

    const chapterHTML = `
        <div class="chapter-item" data-index="${chapterIndex}">
            <div class="chapter-header">
                <div class="chapter-number">${chapterNum}</div>
                <button type="button" class="btn-remove-chapter" onclick="removeChapter(${chapterIndex})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chapter-content">
                <div class="form-group">
                    <label>Titre du chapitre *</label>
                    <input type="text" class="form-control chapter-title" 
                           placeholder="Ex: Introduction à React" 
                           value="${title}" 
                           required>
                </div>
                <div class="form-group">
                    <label>Description (optionnel)</label>
                    <textarea class="form-control chapter-description" 
                              rows="2" 
                              placeholder="Description du chapitre...">${description}</textarea>
                </div>
            </div>
        </div>
    `;

    chaptersContainer.insertAdjacentHTML('beforeend', chapterHTML);
    updateChaptersInput();
}

function removeChapter(index) {
    const chapterItem = document.querySelector(`.chapter-item[data-index="${index}"]`);
    if (chapterItem) {
        chapterItem.remove();
        updateChaptersInput();
        reorderChapters();
    }
}

function updateChaptersInput() {
    const chapters = [];
    document.querySelectorAll('.chapter-item').forEach((item, index) => {
        const title = item.querySelector('.chapter-title').value.trim();
        const description = item.querySelector('.chapter-description').value.trim();

        if (title) {
            chapters.push({
                chapter_number: index + 1,
                title: title,
                description: description || null,
                order_index: index
            });
        }
    });

    chaptersInput.value = JSON.stringify(chapters);
}

function reorderChapters() {
    document.querySelectorAll('.chapter-item').forEach((item, index) => {
        const chapterNumber = item.querySelector('.chapter-number');
        chapterNumber.textContent = index + 1;
    });
    updateChaptersInput();
}

// Ajouter un chapitre au clic
addChapterBtn.addEventListener('click', function() {
    addChapter();
});

// Écouter les changements dans les chapitres
chaptersContainer.addEventListener('input', function(e) {
    if (e.target.classList.contains('chapter-title') || e.target.classList.contains('chapter-description')) {
        updateChaptersInput();
    }
});

// ========================================
// GESTION DES VIDÉOS MULTIPLES
// ========================================
const videosInput = document.getElementById('videos');
const videosPreview = document.getElementById('videos-preview');
const videosList = document.getElementById('videos-list');
const MAX_VIDEO_SIZE = 524288000; // 500MB
const MAX_TOTAL_SIZE = 524288000; // 500MB pour commencer (sera détecté dynamiquement)
let uploadedVideos = [];

// Fonction pour convertir les valeurs PHP en bytes (similaire à convertToBytes PHP)
function convertToBytes(val) {
    if (!val) return 0;
    val = val.toString().trim();
    const last = val.slice(-1).toLowerCase();
    let num = parseInt(val);
    switch (last) {
        case 'g':
            num *= 1024;
        case 'm':
            num *= 1024;
        case 'k':
            num *= 1024;
    }
    return num;
}

// Détecter les limites PHP (essayer de les obtenir via un appel AJAX ou utiliser les valeurs par défaut)
let phpLimits = {
    post_max_size: MAX_TOTAL_SIZE, // 500MB par défaut
    upload_max_filesize: MAX_VIDEO_SIZE // 500MB par défaut
};

// Afficher la taille totale des fichiers sélectionnés
function updateTotalSizeDisplay() {
    let totalSize = 0;

    // Taille des vidéos
    uploadedVideos.forEach(video => {
        totalSize += video.file.size;
    });

    // Taille du fichier supplémentaire
    const fileInput = document.getElementById('file');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        totalSize += fileInput.files[0].size;
    }

    // Afficher/mettre à jour l'indicateur de taille totale
    let sizeIndicator = document.getElementById('total-size-indicator');
    if (!sizeIndicator) {
        sizeIndicator = document.createElement('div');
        sizeIndicator.id = 'total-size-indicator';
        sizeIndicator.className = 'total-size-indicator';
        const videosSection = document.getElementById('videos-section');
        if (videosSection) {
            videosSection.appendChild(sizeIndicator);
        }
    }

    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
    const maxSizeMB = (phpLimits.post_max_size / 1024 / 1024).toFixed(0);

    if (totalSize > 0) {
        const percentage = (totalSize / phpLimits.post_max_size) * 100;
        const statusClass = percentage > 90 ? 'warning' : percentage > 70 ? 'info' : 'success';

        sizeIndicator.innerHTML = `
            <div class="size-indicator-content ${statusClass}">
                <i class="fas fa-weight"></i> 
                <strong>Taille totale:</strong> ${totalSizeMB} MB / ${maxSizeMB} MB
                <div class="size-progress-bar">
                    <div class="size-progress" style="width: ${Math.min(percentage, 100)}%"></div>
                </div>
                ${percentage > 90 ? '<span class="size-warning">⚠️ Attention: Approche de la limite!</span>' : ''}
                ${percentage > 100 ? '<span class="size-error">❌ Erreur: Limite dépassée!</span>' : ''}
            </div>
        `;
        sizeIndicator.style.display = 'block';
    } else {
        sizeIndicator.style.display = 'none';
    }
}

videosInput.addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    let hasErrors = false;
    let totalSize = uploadedVideos.reduce((sum, v) => sum + v.file.size, 0);

    files.forEach((file, index) => {
        // Vérifier la taille individuelle
        if (file.size > MAX_VIDEO_SIZE) {
            alert(`⚠️ La vidéo "${file.name}" est trop volumineuse (max 500MB)`);
            hasErrors = true;
            return;
        }

        // Vérifier la taille totale
        totalSize += file.size;
        if (totalSize > phpLimits.post_max_size) {
            const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
            const maxSizeMB = (phpLimits.post_max_size / 1024 / 1024).toFixed(0);
            alert(
                `⚠️ Erreur: La taille totale (${totalSizeMB}MB) dépasse la limite autorisée (${maxSizeMB}MB).\n\nSolution: Visitez ${window.location.origin}<?= BASE_URL ?>/public/fix_upload_limits.php pour configurer PHP.`
            );
            hasErrors = true;
            return;
        }

        // Vérifier le type
        if (!file.type.startsWith('video/')) {
            alert(`⚠️ Le fichier "${file.name}" n'est pas une vidéo`);
            hasErrors = true;
            return;
        }

        // Ajouter la vidéo à la liste
        addVideoToList(file, index);
    });

    if (!hasErrors) {
        updateVideosPreview();
        updateTotalSizeDisplay();
    }
});

function addVideoToList(file, index) {
    const videoData = {
        file: file,
        title: file.name.replace(/\.[^/.]+$/, ''),
        description: '',
        order: uploadedVideos.length
    };

    uploadedVideos.push(videoData);
    renderVideoItem(videoData, uploadedVideos.length - 1);
}

function renderVideoItem(videoData, index) {
    const fileSize = (videoData.file.size / 1024 / 1024).toFixed(2);
    const videoURL = URL.createObjectURL(videoData.file);

    const videoHTML = `
        <div class="video-item" data-index="${index}">
            <div class="video-item-header">
                <div class="video-item-number">${index + 1}</div>
                <button type="button" class="btn-remove-video" onclick="removeVideo(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="video-item-content">
                <div class="video-preview-small">
                    <video controls>
                        <source src="${videoURL}" type="${videoData.file.type}">
                    </video>
                </div>
                <div class="video-item-info">
                    <div class="form-group">
                        <label>Titre de la vidéo *</label>
                        <input type="text" class="form-control video-title" 
                               name="video_titles[]" 
                               value="${videoData.title}" 
                               required>
                    </div>
                    <div class="form-group">
                        <label>Description (optionnel)</label>
                        <textarea class="form-control video-description" 
                                  name="video_descriptions[]" 
                                  rows="2" 
                                  placeholder="Description de la vidéo...">${videoData.description}</textarea>
                    </div>
                    <div class="video-meta">
                        <span><i class="fas fa-file"></i> ${videoData.file.name}</span>
                        <span><i class="fas fa-weight"></i> ${fileSize} MB</span>
                        <input type="hidden" name="video_orders[]" value="${videoData.order}" class="video-order">
                    </div>
                </div>
            </div>
        </div>
    `;

    videosList.insertAdjacentHTML('beforeend', videoHTML);
}

function removeVideo(index) {
    // Supprimer la vidéo du tableau
    if (index >= 0 && index < uploadedVideos.length) {
        uploadedVideos.splice(index, 1);
    }

    // Supprimer l'élément du DOM
    const videoItem = document.querySelector(`.video-item[data-index="${index}"]`);
    if (videoItem) {
        videoItem.remove();
    }

    // Réorganiser les vidéos restantes
    reorderVideos();
    updateVideosPreview();
    updateTotalSizeDisplay();

    // Recréer l'input file avec les fichiers restants
    updateVideosInput();
}

function updateVideosInput() {
    // Mettre à jour les inputs cachés pour l'ordre
    document.querySelectorAll('.video-order').forEach((input, index) => {
        if (input) {
            input.value = index;
        }
    });

    // Mettre à jour les index des éléments dans le DOM
    document.querySelectorAll('.video-item').forEach((item, index) => {
        item.setAttribute('data-index', index);
        const removeBtn = item.querySelector('.btn-remove-video');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeVideo(${index})`);
        }
    });
}

function reorderVideos() {
    // Mettre à jour les numéros et les index dans le DOM
    document.querySelectorAll('.video-item').forEach((item, index) => {
        const videoNumber = item.querySelector('.video-item-number');
        if (videoNumber) {
            videoNumber.textContent = index + 1;
        }

        // Mettre à jour l'attribut data-index
        item.setAttribute('data-index', index);

        // Mettre à jour le bouton de suppression
        const removeBtn = item.querySelector('.btn-remove-video');
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeVideo(${index})`);
        }
    });

    // Mettre à jour les inputs d'ordre
    updateVideosInput();
}

function updateVideosPreview() {
    if (uploadedVideos.length > 0) {
        if (videosPreview) {
            videosPreview.innerHTML = `<p class="videos-count">${uploadedVideos.length} vidéo(s) sélectionnée(s)</p>`;
        }
    } else {
        if (videosPreview) {
            videosPreview.innerHTML = '';
        }
    }
    // Mettre à jour l'affichage de la taille totale
    updateTotalSizeDisplay();
}

// Drag and drop pour les vidéos
const videoUploadArea = document.querySelector('.video-upload-area');

videoUploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    videoUploadArea.classList.add('drag-over');
});

videoUploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    videoUploadArea.classList.remove('drag-over');
});

videoUploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    videoUploadArea.classList.remove('drag-over');

    const files = Array.from(e.dataTransfer.files);
    const videoFiles = files.filter(file => file.type.startsWith('video/'));

    videoFiles.forEach((file, index) => {
        if (file.size > MAX_VIDEO_SIZE) {
            alert(`⚠️ La vidéo "${file.name}" est trop volumineuse (max 500MB)`);
            return;
        }
        addVideoToList(file, uploadedVideos.length);
    });

    updateVideosPreview();
});

// ========================================
// GESTION DU FICHIER UNIQUE
// ========================================
const fileInput = document.getElementById('file');
const filePreview = document.getElementById('file-preview');

fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Vérifier la taille totale avant d'afficher
        let totalSize = uploadedVideos.reduce((sum, v) => sum + v.file.size, 0) + file.size;
        if (totalSize > phpLimits.post_max_size) {
            const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
            const maxSizeMB = (phpLimits.post_max_size / 1024 / 1024).toFixed(0);
            alert(
                `⚠️ Erreur: La taille totale (${totalSizeMB}MB) dépasse la limite autorisée (${maxSizeMB}MB).\n\nSolution: Visitez ${window.location.origin}<?= BASE_URL ?>/public/fix_upload_limits.php pour configurer PHP.`
            );
            fileInput.value = '';
            return;
        }

        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        const fileType = file.type;
        let icon = 'fa-file';
        let iconColor = '#6c757d';
        let previewHTML = '';

        if (fileType.startsWith('image/')) {
            icon = 'fa-image';
            iconColor = 'var(--secondary-color)';
            const imageURL = URL.createObjectURL(file);
            previewHTML = `
                <div class="image-preview-container">
                    <img src="${imageURL}" alt="Preview" style="max-width: 100%; border-radius: 12px; margin-top: 15px;">
                </div>
            `;
        } else if (fileType === 'application/pdf') {
            icon = 'fa-file-pdf';
            iconColor = '#dc3545';
        } else if (fileType.includes('document') || fileType.includes('word')) {
            icon = 'fa-file-word';
            iconColor = '#2b579a';
        } else if (fileType.includes('zip') || fileType.includes('compressed')) {
            icon = 'fa-file-archive';
            iconColor = '#f39c12';
        } else {
            icon = 'fa-file-code';
            iconColor = 'var(--primary-color)';
        }

        filePreview.innerHTML = `
            <div class="file-preview-item">
                <div class="file-icon" style="color: ${iconColor};">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="file-info">
                    <strong>${file.name}</strong>
                    <small>${fileSize} MB</small>
                </div>
                <button type="button" onclick="clearFile()" class="btn-remove-file">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            ${previewHTML}
        `;

        // Mettre à jour l'affichage de la taille totale
        updateTotalSizeDisplay();
    }
});

function clearFile() {
    fileInput.value = '';
    filePreview.innerHTML = '';
    updateTotalSizeDisplay();
}

// Form validation
document.querySelector('.tutorial-form').addEventListener('submit', function(e) {
    // Synchroniser TinyMCE avec le textarea AVANT la validation
    if (tinymce.get('content')) {
        tinymce.triggerSave();
    }

    const title = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const type = document.getElementById('type').value;
    const category = document.getElementById('category').value;
    const level = document.getElementById('level').value;

    // Récupérer le contenu de TinyMCE
    let content = '';
    if (tinymce.get('content')) {
        content = tinymce.get('content').getContent({
            format: 'text'
        }).trim();
    }

    console.log('Validation:', {
        title,
        description,
        content: content.length,
        type,
        category,
        level
    });

    if (title.length < 5) {
        e.preventDefault();
        alert('⚠️ Le titre doit contenir au moins 5 caractères');
        document.getElementById('title').focus();
        return false;
    }

    if (description.length < 20) {
        e.preventDefault();
        alert('⚠️ La description doit contenir au moins 20 caractères');
        document.getElementById('description').focus();
        return false;
    }

    if (content.length < 50) {
        e.preventDefault();
        alert('⚠️ Le contenu doit contenir au moins 50 caractères');
        if (tinymce.get('content')) {
            tinymce.get('content').focus();
        }
        return false;
    }

    if (!type) {
        e.preventDefault();
        alert('⚠️ Veuillez choisir un type de tutoriel');
        document.getElementById('type').focus();
        return false;
    }

    if (!category) {
        e.preventDefault();
        alert('⚠️ Veuillez choisir une catégorie');
        document.getElementById('category').focus();
        return false;
    }

    if (!level) {
        e.preventDefault();
        alert('⚠️ Veuillez choisir un niveau de difficulté');
        document.getElementById('level').focus();
        return false;
    }

    // Vérifier la taille totale des fichiers avant soumission
    let totalSize = 0;

    // Taille des vidéos
    uploadedVideos.forEach(video => {
        totalSize += video.file.size;
    });

    // Taille du fichier supplémentaire
    const fileInput = document.getElementById('file');
    if (fileInput && fileInput.files && fileInput.files[0]) {
        totalSize += fileInput.files[0].size;
    }

    // Vérifier contre les limites PHP
    if (totalSize > phpLimits.post_max_size) {
        e.preventDefault();
        const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
        const maxSizeMB = (phpLimits.post_max_size / 1024 / 1024).toFixed(0);
        const fixUrl = `${window.location.origin}<?= BASE_URL ?>/public/fix_upload_limits.php`;
        alert(
            `❌ Erreur: La taille totale des fichiers (${totalSizeMB}MB) dépasse la limite autorisée (${maxSizeMB}MB).\n\nL'erreur "Request Entity Too Large" (413) se produira.\n\nSolution: Visitez ${fixUrl} pour configurer PHP pour 500MB.`
        );
        window.location.href = fixUrl;
        return false;
    }

    // Vérifier la taille de chaque vidéo individuellement
    uploadedVideos.forEach((video, index) => {
        if (video.file.size > MAX_VIDEO_SIZE) {
            e.preventDefault();
            const videoSizeMB = (video.file.size / 1024 / 1024).toFixed(2);
            alert(
                `❌ Erreur: La vidéo "${video.file.name}" (${videoSizeMB}MB) dépasse la limite de 500MB par vidéo.`
            );
            return false;
        }
    });

    // Vérifier si des vidéos sont uploadées (pour les formations vidéo)
    const videosInput = document.getElementById('videos');
    if (type === 'video' && (!videosInput || !videosInput.files || videosInput.files.length === 0)) {
        e.preventDefault();
        alert('⚠️ Veuillez uploader au moins une vidéo pour une formation vidéo');
        videosInput?.focus();
        return false;
    }

    // Vérifier que les titres des vidéos sont remplis
    const videoTitles = document.querySelectorAll('.video-title');
    let hasEmptyTitle = false;
    videoTitles.forEach((titleInput, index) => {
        if (!titleInput.value.trim()) {
            hasEmptyTitle = true;
        }
    });

    if (hasEmptyTitle) {
        e.preventDefault();
        alert('⚠️ Veuillez remplir les titres de toutes les vidéos');
        return false;
    }

    // Vérifier que les chapitres ont des titres
    const chapterTitles = document.querySelectorAll('.chapter-title');
    let hasEmptyChapterTitle = false;
    chapterTitles.forEach((titleInput) => {
        if (!titleInput.value.trim()) {
            hasEmptyChapterTitle = true;
        }
    });

    if (chapterTitles.length > 0 && hasEmptyChapterTitle) {
        e.preventDefault();
        alert('⚠️ Veuillez remplir les titres de tous les chapitres');
        return false;
    }

    // Mettre à jour les chapitres avant soumission
    updateChaptersInput();

    // Afficher un message de confirmation si la taille est importante
    if (totalSize > 100 * 1024 * 1024) { // Plus de 100MB
        const confirmMessage =
            `⚠️ Vous êtes sur le point d'uploader ${(totalSize / 1024 / 1024).toFixed(2)}MB de fichiers.\n\nCela peut prendre du temps. Continuer?`;
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    }

    console.log('✅ Validation réussie - Soumission du formulaire');
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>