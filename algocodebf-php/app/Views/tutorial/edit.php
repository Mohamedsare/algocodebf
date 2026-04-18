<?php
$pageTitle = 'Modifier le Tutoriel - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Récupérer les anciennes valeurs ou les données du tutoriel
$old = $_SESSION['old'] ?? $tutorial;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

// Décoder le contenu HTML si nécessaire (pour TinyMCE)
if (isset($tutorial['content'])) {
    $tutorial['content'] = html_entity_decode($tutorial['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
if (isset($old['content'])) {
    $old['content'] = html_entity_decode($old['content'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>

<section class="create-tutorial-section">
    <div class="container">
        <!-- Header -->
        <div class="page-header-create">
            <div class="header-content">
                <h1><i class="fas fa-edit"></i> Modifier le Tutoriel</h1>
                <p>Mettez à jour votre tutoriel</p>
            </div>
            <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour au tutoriel
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
                <form action="<?= BASE_URL ?>/tutorial/edit/<?= $tutorial['id'] ?>" method="POST" class="tutorial-form" enctype="multipart/form-data">
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
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   placeholder="Ex: Guide complet pour débuter avec React.js"
                                   value="<?= htmlspecialchars($old['title'] ?? $tutorial['title'] ?? '') ?>"
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
                                Description courte *
                                <span class="field-hint">Résumez votre tutoriel en quelques phrases</span>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control textarea-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                                      rows="4"
                                      placeholder="Décrivez brièvement ce que les utilisateurs vont apprendre..."
                                      required><?= htmlspecialchars($old['description'] ?? $tutorial['description'] ?? '') ?></textarea>
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
                                <select id="type" 
                                        name="type" 
                                        class="form-control select-control"
                                        required>
                                    <option value="">-- Choisir un type --</option>
                                    <option value="video" <?= ($old['type'] ?? $tutorial['type'] ?? '') == 'video' ? 'selected' : '' ?>>
                                        🎥 Vidéo
                                    </option>
                                    <option value="article" <?= ($old['type'] ?? $tutorial['type'] ?? '') == 'article' ? 'selected' : '' ?>>
                                        📝 Article
                                    </option>
                                    <option value="pdf" <?= ($old['type'] ?? $tutorial['type'] ?? '') == 'pdf' ? 'selected' : '' ?>>
                                        📄 PDF
                                    </option>
                                    <option value="code" <?= ($old['type'] ?? $tutorial['type'] ?? '') == 'code' ? 'selected' : '' ?>>
                                        💻 Code/Snippets
                                    </option>
                                </select>
                            </div>

                            <!-- Catégorie -->
                            <div class="form-group">
                                <label for="category">
                                    <i class="fas fa-tag"></i> Catégorie *
                                </label>
                                <select id="category" 
                                        name="category" 
                                        class="form-control select-control"
                                        required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($old['category'] ?? $tutorial['category'] ?? '') === $cat ? 'selected' : '' ?>>
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
                            <select id="level" 
                                    name="level" 
                                    class="form-control select-control"
                                    required>
                                <option value="">-- Choisir un niveau --</option>
                                <option value="Débutant" <?= ($old['level'] ?? $tutorial['level'] ?? '') == 'Débutant' ? 'selected' : '' ?>>
                                    ⭐ Débutant
                                </option>
                                <option value="Intermédiaire" <?= ($old['level'] ?? $tutorial['level'] ?? '') == 'Intermédiaire' ? 'selected' : '' ?>>
                                    ⭐⭐ Intermédiaire
                                </option>
                                <option value="Avancé" <?= ($old['level'] ?? $tutorial['level'] ?? '') == 'Avancé' ? 'selected' : '' ?>>
                                    ⭐⭐⭐ Avancé
                                </option>
                                <option value="Expert" <?= ($old['level'] ?? $tutorial['level'] ?? '') == 'Expert' ? 'selected' : '' ?>>
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
                                <span class="field-hint">Expliquez étape par étape avec formatage riche et code coloré</span>
                            </label>
                            <textarea id="content" 
                                      name="content" 
                                      class="form-control tinymce-editor"><?= $old['content'] ?? $tutorial['content'] ?? '' ?></textarea>
                            <small class="form-hint">
                                <i class="fas fa-info-circle"></i> Utilisez l'éditeur pour formater le texte, insérer des images, et ajouter du code avec coloration syntaxique
                            </small>
                        </div>
                    </div>

                    <!-- Ressources -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-paperclip"></i>
                            <h3>Ressources et médias</h3>
                        </div>

                        <!-- Lien externe -->
                        <div class="form-group">
                            <label for="external_link">
                                <i class="fas fa-external-link-alt"></i> Lien externe (optionnel)
                                <span class="field-hint">Lien vers une vidéo YouTube, GitHub, etc.</span>
                            </label>
                            <input type="url" 
                                   id="external_link" 
                                   name="external_link" 
                                   class="form-control"
                                   placeholder="https://youtube.com/watch?v=..."
                                   value="<?= htmlspecialchars($old['external_link'] ?? $tutorial['external_link'] ?? '') ?>">
                        </div>

                        <!-- Fichier actuel -->
                        <?php if (!empty($tutorial['file_path'])): ?>
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-file"></i> Fichier actuel
                                </label>
                                <div class="current-file-box">
                                    <div class="file-icon-current">
                                        <?php
                                        $ext = pathinfo($tutorial['file_path'], PATHINFO_EXTENSION);
                                        $videoExts = ['mp4', 'webm', 'avi', 'mov', 'wmv'];
                                        if (in_array(strtolower($ext), $videoExts)) {
                                            echo '<i class="fas fa-film" style="color: #dc3545;"></i>';
                                        } elseif (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                                            echo '<i class="fas fa-image" style="color: var(--secondary-color);"></i>';
                                        } elseif (strtolower($ext) === 'pdf') {
                                            echo '<i class="fas fa-file-pdf" style="color: #dc3545;"></i>';
                                        } else {
                                            echo '<i class="fas fa-file-code" style="color: var(--primary-color);"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="file-info-current">
                                        <strong><?= basename($tutorial['file_path']) ?></strong>
                                        <?php if (file_exists($tutorial['file_path'])): ?>
                                            <small><?= round(filesize($tutorial['file_path']) / 1024 / 1024, 2) ?> MB</small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($tutorial['file_path']) ?>" 
                                       class="btn-view-file" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </div>
                                <small class="form-hint">Uploadez un nouveau fichier ci-dessous pour remplacer l'ancien</small>
                            </div>
                        <?php endif; ?>

                        <!-- Upload de fichier -->
                        <div class="form-group">
                            <label for="file">
                                <i class="fas fa-upload"></i> <?= !empty($tutorial['file_path']) ? 'Remplacer le fichier (optionnel)' : 'Fichier ou Vidéo (optionnel)' ?>
                                <span class="field-hint">Vidéos (Max 50MB), PDF, images, code source (Max 5MB)</span>
                            </label>
                            <div class="file-upload-area">
                                <input type="file" 
                                       id="file" 
                                       name="file" 
                                       class="file-input"
                                       accept="video/*,image/*,.pdf,.doc,.docx,.zip,.txt,.py,.js,.html,.css,.mp4,.avi,.mov,.wmv,.webm">
                                <label for="file" class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Cliquez pour ajouter un fichier ou une vidéo</span>
                                    <small>Vidéos (MP4, AVI, MOV, WebM - Max 50MB) • Images, PDF, Documents, Code (Max 5MB)</small>
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
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   class="form-control"
                                   placeholder="Ex: react, javascript, débutant"
                                   value="<?= htmlspecialchars($tags_string ?? $old['tags'] ?? '') ?>">
                            <small class="form-hint">Séparez les tags par des virgules</small>
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
                            <!-- Chapitres existants -->
                            <?php if (!empty($chapters)): ?>
                                <?php foreach ($chapters as $index => $chapter): ?>
                                    <div class="chapter-item-existing" data-chapter-id="<?= $chapter['id'] ?>">
                                        <div class="chapter-header">
                                            <div class="chapter-number"><?= $chapter['chapter_number'] ?? ($index + 1) ?></div>
                                            <button type="button" class="btn-remove-chapter-existing" onclick="deleteExistingChapter(<?= $chapter['id'] ?>, '<?= htmlspecialchars(addslashes($chapter['title'])) ?>')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                        <div class="chapter-content">
                                            <div class="form-group">
                                                <label>Titre du chapitre *</label>
                                                <input type="text" 
                                                       class="form-control chapter-title-existing" 
                                                       name="existing_chapter_titles[<?= $chapter['id'] ?>]" 
                                                       placeholder="Ex: Introduction à React" 
                                                       value="<?= htmlspecialchars($chapter['title']) ?>" 
                                                       required>
                                            </div>
                                            <div class="form-group">
                                                <label>Description (optionnel)</label>
                                                <textarea class="form-control chapter-description-existing" 
                                                          name="existing_chapter_descriptions[<?= $chapter['id'] ?>]" 
                                                          rows="2" 
                                                          placeholder="Description du chapitre..."><?= htmlspecialchars($chapter['description'] ?? '') ?></textarea>
                                            </div>
                                            <?php if (!empty($videos)): ?>
                                                <div class="form-group">
                                                    <label>Associer à une vidéo (optionnel)</label>
                                                    <select class="form-control chapter-video-select" name="existing_chapter_videos[<?= $chapter['id'] ?>]">
                                                        <option value="">-- Aucune vidéo --</option>
                                                        <?php foreach ($videos as $video): ?>
                                                            <option value="<?= $video['id'] ?>" <?= ($chapter['video_id'] ?? null) == $video['id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($video['title']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            <?php endif; ?>
                                            <input type="hidden" name="existing_chapter_orders[<?= $chapter['id'] ?>]" value="<?= $chapter['order_index'] ?? $index ?>" class="chapter-order-existing">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-chapters-message" style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 12px; margin-bottom: 20px;">
                                    <i class="fas fa-list-ol" style="font-size: 3rem; color: #6c757d; margin-bottom: 15px;"></i>
                                    <p style="color: #6c757d; font-size: 1.1rem;">Aucun chapitre pour ce tutoriel</p>
                                    <p style="color: #adb5bd; font-size: 0.9rem;">Ajoutez des chapitres ci-dessous</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="button" id="add-chapter-btn" class="btn-add-chapter">
                            <i class="fas fa-plus"></i> Ajouter un chapitre
                        </button>

                        <!-- Champ caché pour envoyer les nouveaux chapitres -->
                        <input type="hidden" id="chapters-input" name="chapters" value="">
                    </div>

                    <!-- Vidéos de la formation -->
                    <div class="form-section" id="videos-section">
                        <div class="section-title">
                            <i class="fas fa-video"></i>
                            <h3>Vidéos de la formation</h3>
                        </div>
                        <p class="section-description">
                            Gérez les vidéos de votre tutoriel. Vous pouvez ajouter, modifier ou supprimer des vidéos.
                        </p>

                        <!-- Vidéos existantes -->
                        <?php if (!empty($videos)): ?>
                            <div class="existing-videos-list" id="existing-videos-list">
                                <?php foreach ($videos as $index => $video): ?>
                                    <div class="video-item-existing" data-video-id="<?= $video['id'] ?>">
                                        <div class="video-item-header">
                                            <div class="video-item-number"><?= $index + 1 ?></div>
                                            <button type="button" class="btn-remove-video-existing" onclick="deleteExistingVideo(<?= $video['id'] ?>, '<?= htmlspecialchars(addslashes($video['title'])) ?>')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                        <div class="video-item-content">
                                            <?php if (!empty($video['file_path']) && file_exists($video['file_path'])): ?>
                                                <div class="video-preview-small">
                                                    <video controls>
                                                        <source src="<?= BASE_URL ?>/<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                                                    </video>
                                                </div>
                                            <?php else: ?>
                                                <div class="video-preview-placeholder">
                                                    <i class="fas fa-video" style="font-size: 3rem; color: #6c757d;"></i>
                                                    <p>Fichier vidéo non disponible</p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="video-item-info">
                                                <div class="form-group">
                                                    <label>Titre de la vidéo *</label>
                                                    <input type="text" 
                                                           class="form-control video-title-existing" 
                                                           name="existing_video_titles[<?= $video['id'] ?>]" 
                                                           value="<?= htmlspecialchars($video['title']) ?>" 
                                                           required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Description (optionnel)</label>
                                                    <textarea class="form-control video-description-existing" 
                                                              name="existing_video_descriptions[<?= $video['id'] ?>]" 
                                                              rows="2" 
                                                              placeholder="Description de la vidéo..."><?= htmlspecialchars($video['description'] ?? '') ?></textarea>
                                                </div>
                                                <div class="video-meta">
                                                    <span><i class="fas fa-file"></i> <?= htmlspecialchars($video['file_name'] ?? basename($video['file_path'] ?? '')) ?></span>
                                                    <?php if (!empty($video['file_size'])): ?>
                                                        <span><i class="fas fa-weight"></i> <?= round($video['file_size'] / 1024 / 1024, 2) ?> MB</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($video['views'])): ?>
                                                        <span><i class="fas fa-eye"></i> <?= $video['views'] ?> vues</span>
                                                    <?php endif; ?>
                                                    <input type="hidden" name="existing_video_orders[<?= $video['id'] ?>]" value="<?= $video['order_index'] ?? $index ?>" class="video-order-existing">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-videos-message" style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 12px; margin-bottom: 20px;">
                                <i class="fas fa-video" style="font-size: 3rem; color: #6c757d; margin-bottom: 15px;"></i>
                                <p style="color: #6c757d; font-size: 1.1rem;">Aucune vidéo pour ce tutoriel</p>
                                <p style="color: #adb5bd; font-size: 0.9rem;">Ajoutez des vidéos ci-dessous</p>
                            </div>
                        <?php endif; ?>

                        <!-- Zone d'upload pour nouvelles vidéos -->
                        <div class="form-group" style="margin-top: 30px;">
                            <label for="new_videos">
                                <i class="fas fa-plus-circle"></i> Ajouter de nouvelles vidéos
                                <span class="field-hint">Sélectionnez une ou plusieurs vidéos (Max 500MB par vidéo)</span>
                            </label>
                            <div class="video-upload-area">
                                <input type="file" id="new_videos" name="new_videos[]" class="video-input" 
                                       accept="video/*,.mp4,.avi,.mov,.wmv,.webm,.mpeg" 
                                       multiple>
                                <label for="new_videos" class="video-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Cliquez pour sélectionner des vidéos ou glissez-déposez ici</span>
                                    <small>Formats acceptés: MP4, AVI, MOV, WebM, MPEG (Max 500MB par vidéo)</small>
                                </label>
                                <div id="new-videos-preview" class="videos-preview"></div>
                            </div>
                        </div>

                        <!-- Liste des nouvelles vidéos uploadées -->
                        <div id="new-videos-list" class="videos-list"></div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                        <a href="<?= BASE_URL ?>/tutorial/show/<?= $tutorial['id'] ?>" class="btn-cancel">
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
                        Les tutoriels de qualité peuvent aider des centaines d'étudiants et de développeurs burkinabè à progresser !
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

/* Current File Box */
.current-file-box {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    border: 2px solid #dee2e6;
    margin-bottom: 10px;
}

.file-icon-current {
    font-size: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-info-current {
    flex: 1;
}

.file-info-current strong {
    display: block;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.file-info-current small {
    color: #6c757d;
}

.btn-view-file {
    padding: 10px 20px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-file:hover {
    background: var(--secondary-color);
    transform: translateY(-2px);
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

/* Vidéos */
.section-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 20px;
    line-height: 1.6;
}

/* Chapitres */
.chapter-item-existing {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.chapter-item-existing:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
}

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

.btn-remove-chapter-existing {
    padding: 8px 16px;
    background: white;
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.btn-remove-chapter-existing:hover {
    background: var(--danger-color);
    color: white;
    transform: translateY(-2px);
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

.chapter-video-select {
    cursor: pointer;
}

.existing-videos-list {
    margin-bottom: 30px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.video-item-existing {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.video-item-existing:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
}

.btn-remove-video-existing {
    padding: 8px 16px;
    background: white;
    border: 2px solid var(--danger-color);
    color: var(--danger-color);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.btn-remove-video-existing:hover {
    background: var(--danger-color);
    color: white;
    transform: translateY(-2px);
}

.video-preview-placeholder {
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.video-preview-placeholder p {
    margin-top: 10px;
    font-size: 0.9rem;
}

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
    padding: 40px 20px;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.video-upload-label:hover {
    border-color: var(--primary-color);
    background: rgba(52, 152, 219, 0.05);
}

.video-upload-label i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.video-upload-label span {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.video-upload-label small {
    color: #6c757d;
    font-size: 0.875rem;
}

.videos-preview {
    margin-top: 15px;
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
    flex-wrap: wrap;
}

.video-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Responsive */
@media (max-width: 992px) {
    .create-tutorial-wrapper {
        grid-template-columns: 1fr;
    }
    
    .tips-sidebar {
        order: -1;
    }
    
    .video-item-content {
        grid-template-columns: 1fr;
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

<!-- TinyMCE Editor -->
<!-- Remplacez VOTRE_CLE_API_ICI par votre vraie clé obtenue sur tiny.cloud -->
<script src="https://cdn.tiny.cloud/1/y9lugfpj1jxq696s71q9d8t99y49ir7sxho2ogultsj83j8u/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

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
    init_instance_callback: function(editor) {
        // Le contenu est déjà dans le textarea, TinyMCE le chargera automatiquement
        console.log('✅ Éditeur TinyMCE initialisé avec le contenu du tutoriel');
    },
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
    codesample_languages: [
        {text: 'HTML/XML', value: 'markup'},
        {text: 'JavaScript', value: 'javascript'},
        {text: 'CSS', value: 'css'},
        {text: 'PHP', value: 'php'},
        {text: 'Python', value: 'python'},
        {text: 'Java', value: 'java'},
        {text: 'C', value: 'c'},
        {text: 'C++', value: 'cpp'},
        {text: 'C#', value: 'csharp'},
        {text: 'Ruby', value: 'ruby'},
        {text: 'SQL', value: 'sql'},
        {text: 'Bash', value: 'bash'},
        {text: 'JSON', value: 'json'},
        {text: 'TypeScript', value: 'typescript'},
        {text: 'React JSX', value: 'jsx'},
        {text: 'Go', value: 'go'},
        {text: 'Rust', value: 'rust'},
        {text: 'Swift', value: 'swift'},
        {text: 'Kotlin', value: 'kotlin'}
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
    setup: function (editor) {
        editor.on('init', function () {
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

// File upload handling
const fileInput = document.getElementById('file');
const filePreview = document.getElementById('file-preview');

fileInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
        const fileType = file.type;
        let icon = 'fa-file';
        let iconColor = '#6c757d';
        let previewHTML = '';
        
        // Déterminer l'icône selon le type
        if (fileType.startsWith('video/')) {
            icon = 'fa-film';
            iconColor = 'var(--danger-color)';
            
            // Créer un aperçu vidéo
            const videoURL = URL.createObjectURL(file);
            previewHTML = `
                <div class="video-preview-container">
                    <video controls style="max-width: 100%; border-radius: 12px; margin-top: 15px;">
                        <source src="${videoURL}" type="${fileType}">
                        Votre navigateur ne supporte pas la lecture vidéo.
                    </video>
                </div>
            `;
            
            // Validation taille vidéo
            if (file.size > 52428800) { // 50 MB
                alert('⚠️ Attention : La vidéo est trop volumineuse (max 50MB)');
                fileInput.value = '';
                return;
            }
        } else if (fileType.startsWith('image/')) {
            icon = 'fa-image';
            iconColor = 'var(--secondary-color)';
            
            // Créer un aperçu image
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
    }
});

function clearFile() {
    fileInput.value = '';
    filePreview.innerHTML = '';
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
        content = tinymce.get('content').getContent({format: 'text'}).trim();
    }
    
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
    
    console.log('✅ Validation réussie - Soumission du formulaire');
    return true;
});

// ========================================
// GESTION DES CHAPITRES
// ========================================
let chapterCounter = <?= !empty($chapters) ? count($chapters) : 0 ?>;
const chaptersContainer = document.getElementById('chapters-container');
const addChapterBtn = document.getElementById('add-chapter-btn');
const chaptersInput = document.getElementById('chapters-input');

function addChapter(title = '', description = '', chapterNumber = null) {
    const chapterIndex = chapterCounter++;
    const chapterNum = chapterNumber !== null ? chapterNumber : chapterIndex + 1;
    
    // Obtenir la liste des vidéos pour le select
    const videosSelect = <?= !empty($videos) ? json_encode(array_map(function($v) { return ['id' => $v['id'], 'title' => $v['title']]; }, $videos)) : '[]' ?>;
    let videosOptions = '<option value="">-- Aucune vidéo --</option>';
    videosSelect.forEach(video => {
        videosOptions += `<option value="${video.id}">${video.title}</option>`;
    });
    
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
                ${videosSelect.length > 0 ? `
                <div class="form-group">
                    <label>Associer à une vidéo (optionnel)</label>
                    <select class="form-control chapter-video-select" name="new_chapter_videos[]">
                        ${videosOptions}
                    </select>
                </div>
                ` : ''}
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

function deleteExistingChapter(chapterId, chapterTitle) {
    if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer le chapitre "${chapterTitle}" ?\n\nCette action est irréversible.`)) {
        // Marquer le chapitre pour suppression
        const chapterItem = document.querySelector(`.chapter-item-existing[data-chapter-id="${chapterId}"]`);
        if (chapterItem) {
            // Créer un input hidden pour indiquer la suppression
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_chapters[]';
            deleteInput.value = chapterId;
            chapterItem.appendChild(deleteInput);
            
            // Masquer l'élément avec animation
            chapterItem.style.transition = 'all 0.5s ease';
            chapterItem.style.opacity = '0.3';
            chapterItem.style.transform = 'translateX(-20px)';
            chapterItem.style.background = '#ffe6e6';
            chapterItem.style.pointerEvents = 'none';
        }
    }
}

function updateChaptersInput() {
    const chapters = [];
    // Récupérer les nouveaux chapitres (pas les existants)
    document.querySelectorAll('.chapter-item').forEach((item, index) => {
        const title = item.querySelector('.chapter-title').value.trim();
        const description = item.querySelector('.chapter-description').value.trim();
        const videoSelect = item.querySelector('.chapter-video-select');
        const videoId = videoSelect ? videoSelect.value : null;
        
        if (title) {
            chapters.push({
                chapter_number: index + 1,
                title: title,
                description: description || null,
                video_id: videoId ? parseInt(videoId) : null,
                order_index: index
            });
        }
    });
    
    chaptersInput.value = JSON.stringify(chapters);
}

function reorderChapters() {
    document.querySelectorAll('.chapter-item').forEach((item, index) => {
        const chapterNumber = item.querySelector('.chapter-number');
        if (chapterNumber) {
            chapterNumber.textContent = index + 1;
        }
    });
    updateChaptersInput();
}

// Ajouter un chapitre au clic
if (addChapterBtn) {
    addChapterBtn.addEventListener('click', function() {
        addChapter();
    });
}

// Écouter les changements dans les chapitres
if (chaptersContainer) {
    chaptersContainer.addEventListener('input', function(e) {
        if (e.target.classList.contains('chapter-title') || e.target.classList.contains('chapter-description')) {
            updateChaptersInput();
        }
    });
}

// ========================================
// GESTION DES VIDÉOS EXISTANTES
// ========================================
function deleteExistingVideo(videoId, videoTitle) {
    if (confirm(`⚠️ Êtes-vous sûr de vouloir supprimer la vidéo "${videoTitle}" ?\n\nCette action est irréversible.`)) {
        // Marquer la vidéo pour suppression
        const videoItem = document.querySelector(`.video-item-existing[data-video-id="${videoId}"]`);
        if (videoItem) {
            // Créer un input hidden pour indiquer la suppression
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_videos[]';
            deleteInput.value = videoId;
            videoItem.appendChild(deleteInput);
            
            // Masquer l'élément avec animation
            videoItem.style.transition = 'all 0.5s ease';
            videoItem.style.opacity = '0.3';
            videoItem.style.transform = 'translateX(-20px)';
            videoItem.style.background = '#ffe6e6';
            videoItem.style.pointerEvents = 'none';
        }
    }
}

// ========================================
// GESTION DES NOUVELLES VIDÉOS
// ========================================
const newVideosInput = document.getElementById('new_videos');
const newVideosPreview = document.getElementById('new-videos-preview');
const newVideosList = document.getElementById('new-videos-list');
const MAX_VIDEO_SIZE = 524288000; // 500MB
let newUploadedVideos = [];

if (newVideosInput) {
    newVideosInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        let hasErrors = false;
        
        files.forEach((file) => {
            // Vérifier la taille individuelle
            if (file.size > MAX_VIDEO_SIZE) {
                alert(`⚠️ La vidéo "${file.name}" est trop volumineuse (max 500MB)`);
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
            addNewVideoToList(file);
        });
        
        if (!hasErrors) {
            updateNewVideosPreview();
        }
    });
}

function addNewVideoToList(file) {
    const videoData = {
        file: file,
        title: file.name.replace(/\.[^/.]+$/, ''),
        description: '',
        order: newUploadedVideos.length
    };
    
    newUploadedVideos.push(videoData);
    renderNewVideoItem(videoData, newUploadedVideos.length - 1);
}

function renderNewVideoItem(videoData, index) {
    const videoURL = URL.createObjectURL(videoData.file);
    const fileSize = (videoData.file.size / 1024 / 1024).toFixed(2);
    
    const videoHTML = `
        <div class="video-item" data-index="${index}">
            <div class="video-item-header">
                <div class="video-item-number">${index + 1}</div>
                <button type="button" class="btn-remove-video" onclick="removeNewVideo(${index})">
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
                               name="new_video_titles[]" 
                               value="${videoData.title}" 
                               required>
                    </div>
                    <div class="form-group">
                        <label>Description (optionnel)</label>
                        <textarea class="form-control video-description" 
                                  name="new_video_descriptions[]" 
                                  rows="2" 
                                  placeholder="Description de la vidéo...">${videoData.description}</textarea>
                    </div>
                    <div class="video-meta">
                        <span><i class="fas fa-file"></i> ${videoData.file.name}</span>
                        <span><i class="fas fa-weight"></i> ${fileSize} MB</span>
                        <input type="hidden" name="new_video_orders[]" value="${videoData.order}" class="video-order">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    newVideosList.insertAdjacentHTML('beforeend', videoHTML);
}

function removeNewVideo(index) {
    // Supprimer la vidéo du tableau
    if (index >= 0 && index < newUploadedVideos.length) {
        newUploadedVideos.splice(index, 1);
    }
    
    // Supprimer l'élément du DOM
    const videoItem = document.querySelector(`#new-videos-list .video-item[data-index="${index}"]`);
    if (videoItem) {
        videoItem.remove();
    }
    
    // Réorganiser les vidéos restantes
    reorderNewVideos();
    updateNewVideosPreview();
}

function reorderNewVideos() {
    document.querySelectorAll('#new-videos-list .video-item').forEach((item, index) => {
        const videoNumber = item.querySelector('.video-item-number');
        if (videoNumber) {
            videoNumber.textContent = index + 1;
        }
        item.setAttribute('data-index', index);
    });
}

function updateNewVideosPreview() {
    if (newUploadedVideos.length === 0) {
        newVideosPreview.innerHTML = '';
        return;
    }
    
    const totalSize = newUploadedVideos.reduce((sum, v) => sum + v.file.size, 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
    
    newVideosPreview.innerHTML = `
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-top: 15px;">
            <strong><i class="fas fa-info-circle"></i> ${newUploadedVideos.length} vidéo(s) sélectionnée(s)</strong>
            <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 0.9rem;">Taille totale: ${totalSizeMB} MB</p>
        </div>
    `;
}

// Mettre à jour les selects de vidéos dans les chapitres quand une nouvelle vidéo est ajoutée
function updateChapterVideoSelects() {
    // Les nouvelles vidéos seront associées par leur index dans le formulaire
    // On ne peut pas les ajouter aux selects existants car elles n'ont pas encore d'ID en base
    // Mais on peut les référencer dans les nouveaux chapitres via un système de mapping
    console.log('📹 Nouvelles vidéos disponibles pour les chapitres');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

