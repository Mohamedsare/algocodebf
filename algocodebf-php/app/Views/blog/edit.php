<?php
$pageTitle = 'Modifier l\'article - Blog AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Utiliser les données du post pour pré-remplir
$old = $post ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$categories = $categories ?? [
    'Actualités',
    'Tutoriels',
    'Carrière',
    'Startups',
    'Événements'
];
?>

<section class="create-article-section">
    <div class="container">
        <!-- Header -->
        <div class="page-header-modern">
            <div class="header-left">
                <a href="<?= BASE_URL ?>/blog/show/<?= $post['slug'] ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour à l'article
                </a>
            </div>
            <div class="header-center">
                <h1><i class="fas fa-edit"></i> Modifier l'article</h1>
                <p>Mettez à jour votre contenu</p>
            </div>
            <div class="header-right"></div>
        </div>

        <!-- Formulaire -->
        <form class="article-form" method="POST" action="<?= BASE_URL ?>/blog/edit/<?= $post['slug'] ?>" enctype="multipart/form-data" id="articleForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-layout">
                <!-- Main Form -->
                <div class="form-main">
                    <!-- Titre -->
                    <div class="form-group-modern">
                        <label class="form-label-modern required">
                            <i class="fas fa-heading"></i> Titre de l'article
                        </label>
                        <input type="text" 
                               name="title" 
                               class="form-control-modern" 
                               placeholder="Un titre accrocheur pour votre article..."
                               value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                               required
                               maxlength="255"
                               onkeyup="updateSlugPreview(this.value)">
                        <div class="slug-preview" id="slugPreview">
                            URL: <?= BASE_URL ?>/blog/show/<?= $post['slug'] ?>
                        </div>
                        <?php if (isset($errors['title'])): ?>
                            <span class="form-error"><?= $errors['title'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Résumé -->
                    <div class="form-group-modern">
                        <label class="form-label-modern required">
                            <i class="fas fa-align-left"></i> Résumé (Excerpt)
                        </label>
                        <textarea name="excerpt" 
                                  class="form-control-modern" 
                                  rows="3"
                                  placeholder="Un court résumé de votre article (maximum 300 caractères)..."
                                  required
                                  maxlength="300"
                                  onkeyup="updateCharCount(this)"><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
                        <div class="char-counter">
                            <span id="excerptCount"><?= strlen($old['excerpt'] ?? '') ?></span>/300 caractères
                        </div>
                        <?php if (isset($errors['excerpt'])): ?>
                            <span class="form-error"><?= $errors['excerpt'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Contenu -->
                    <div class="form-group-modern">
                        <label class="form-label-modern required">
                            <i class="fas fa-file-alt"></i> Contenu de l'article
                        </label>
                        <div class="editor-toolbar">
                            <button type="button" class="btn-editor" onclick="insertMarkdown('bold')" title="Gras">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('italic')" title="Italique">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('heading')" title="Titre">
                                <i class="fas fa-heading"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('link')" title="Lien">
                                <i class="fas fa-link"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('list')" title="Liste">
                                <i class="fas fa-list-ul"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('code')" title="Code">
                                <i class="fas fa-code"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="insertMarkdown('quote')" title="Citation">
                                <i class="fas fa-quote-right"></i>
                            </button>
                            <button type="button" class="btn-editor" onclick="togglePreview()" title="Aperçu">
                                <i class="fas fa-eye"></i> Aperçu
                            </button>
                        </div>
                        <textarea name="content" 
                                  id="contentEditor"
                                  class="form-control-modern editor-textarea" 
                                  rows="20"
                                  placeholder="Rédigez votre article en Markdown..."
                                  required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                        <div id="previewPane" class="preview-pane" style="display: none;"></div>
                        <div class="editor-help">
                            <i class="fas fa-info-circle"></i> 
                            Utilisez <a href="https://www.markdownguide.org/cheat-sheet/" target="_blank">Markdown</a> pour formater votre texte
                        </div>
                        <?php if (isset($errors['content'])): ?>
                            <span class="form-error"><?= $errors['content'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Image actuelle -->
                    <?php if (!empty($post['featured_image'])): ?>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-image"></i> Image actuelle
                        </label>
                        <div class="current-image-box">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['featured_image']) ?>" alt="Image actuelle">
                            <p class="current-image-label">Image à la une actuelle</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Nouvelle image -->
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-image"></i> <?= !empty($post['featured_image']) ? 'Changer l\'image' : 'Image à la une' ?>
                        </label>
                        <div class="file-upload-modern">
                            <input type="file" 
                                   name="featured_image" 
                                   id="featuredImage"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <label for="featuredImage" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Cliquez pour <?= !empty($post['featured_image']) ? 'changer' : 'choisir' ?> l'image</span>
                                <small>JPG, PNG, GIF (Max 5MB)</small>
                            </label>
                            <div id="imagePreview" class="image-preview" style="display: none;">
                                <img src="" alt="Preview" id="previewImg">
                                <button type="button" class="btn-remove-preview" onclick="removeImage()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Form -->
                <div class="form-sidebar">
                    <!-- Publier -->
                    <div class="sidebar-card-form">
                        <h4><i class="fas fa-rocket"></i> Publication</h4>
                        
                        <div class="form-group-compact">
                            <label>Statut</label>
                            <select name="status" class="form-control-compact">
                                <option value="draft" <?= ($old['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                                    📝 Brouillon
                                </option>
                                <option value="published" <?= ($old['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>
                                    ✅ Publié
                                </option>
                                <option value="archived" <?= ($old['status'] ?? '') === 'archived' ? 'selected' : '' ?>>
                                    📦 Archivé
                                </option>
                            </select>
                        </div>

                        <div class="form-group-compact">
                            <label>Catégorie</label>
                            <select name="category" class="form-control-compact" required>
                                <option value="">Sélectionner...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                            <?= ($old['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <span class="form-error"><?= $errors['category'] ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn-publish-modern">
                            <i class="fas fa-save"></i> Enregistrer les modifications
                        </button>
                    </div>

                    <!-- Tags -->
                    <div class="sidebar-card-form">
                        <h4><i class="fas fa-tags"></i> Tags</h4>
                        <input type="text" 
                               name="tags" 
                               class="form-control-compact" 
                               placeholder="Ex: PHP, JavaScript, Web..."
                               value="<?= htmlspecialchars($old['tags'] ?? '') ?>">
                        <small class="field-hint">Séparez les tags par des virgules</small>
                    </div>

                    <!-- Statistiques -->
                    <div class="sidebar-card-form stats-card">
                        <h4><i class="fas fa-chart-line"></i> Statistiques</h4>
                        <div class="stats-list">
                            <div class="stat-item">
                                <i class="far fa-eye"></i>
                                <span><?= formatNumber($post['views'] ?? 0) ?> vues</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-heart"></i>
                                <span><?= $post['likes_count'] ?? 0 ?> likes</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-comments"></i>
                                <span><?= $post['comments_count'] ?? 0 ?> commentaires</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-calendar"></i>
                                <span>Créé <?= timeAgo($post['created_at']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="sidebar-card-form danger-card">
                        <h4><i class="fas fa-exclamation-triangle"></i> Zone dangereuse</h4>
                        <p>Suppression définitive de l'article</p>
                        <button type="button" class="btn-danger-modern" onclick="deleteArticle()">
                            <i class="fas fa-trash"></i> Supprimer l'article
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.create-article-section {
    padding: 40px 0 80px;
    background: #f8f9fa;
    min-height: calc(100vh - 140px);
}

/* Page Header */
.page-header-modern {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    align-items: center;
    margin-bottom: 40px;
    gap: 20px;
}

.header-center {
    text-align: center;
}

.page-header-modern h1 {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.page-header-modern p {
    color: #6c757d;
    font-size: 1.1rem;
}

.btn-back {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50px;
    color: var(--dark-color);
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-back:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

/* Form Layout */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.form-main {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Form Groups */
.form-group-modern {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.form-label-modern {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 12px;
    font-size: 1rem;
}

.form-label-modern.required::after {
    content: '*';
    color: var(--danger-color);
    margin-left: 5px;
}

.form-control-modern {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.slug-preview {
    margin-top: 10px;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #6c757d;
    font-family: 'Courier New', monospace;
}

.char-counter {
    margin-top: 8px;
    text-align: right;
    font-size: 0.85rem;
    color: #6c757d;
}

.form-error {
    display: block;
    margin-top: 8px;
    color: var(--danger-color);
    font-size: 0.9rem;
}

/* Editor */
.editor-toolbar {
    display: flex;
    gap: 5px;
    padding: 10px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-bottom: none;
    border-radius: 12px 12px 0 0;
    flex-wrap: wrap;
}

.btn-editor {
    width: 38px;
    height: 38px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: var(--dark-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-editor:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.editor-textarea {
    border-radius: 0 0 12px 12px !important;
    border-top: none !important;
    font-family: 'Courier New', monospace;
    font-size: 0.95rem;
}

.preview-pane {
    padding: 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 12px 12px;
    min-height: 400px;
    line-height: 1.8;
}

.editor-help {
    margin-top: 10px;
    padding: 10px 15px;
    background: #e7f3ff;
    border-left: 4px solid var(--primary-color);
    border-radius: 5px;
    font-size: 0.85rem;
    color: #0056b3;
}

.editor-help a {
    color: var(--primary-color);
    font-weight: 600;
}

/* Current Image */
.current-image-box {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
}

.current-image-box img {
    width: 100%;
    height: auto;
    display: block;
}

.current-image-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
}

/* File Upload */
.file-upload-modern {
    position: relative;
}

.file-upload-modern input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 50px 20px;
    background: #f8f9fa;
    border: 3px dashed #dee2e6;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
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
    font-size: 0.85rem;
}

.image-preview {
    position: relative;
    margin-top: 20px;
    border-radius: 15px;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.btn-remove-preview {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    background: var(--danger-color);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove-preview:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
}

/* Sidebar Cards */
.sidebar-card-form {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.sidebar-card-form h4 {
    margin: 0 0 20px;
    color: var(--dark-color);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group-compact {
    margin-bottom: 15px;
}

.form-group-compact:last-of-type {
    margin-bottom: 20px;
}

.form-group-compact label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.9rem;
}

.form-control-compact {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control-compact:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.field-hint {
    display: block;
    margin-top: 8px;
    font-size: 0.8rem;
    color: #6c757d;
}

.btn-publish-modern {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.05rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-publish-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
}

/* Stats Card */
.stats-card {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #6c757d;
    font-size: 0.9rem;
}

.stat-item i {
    color: var(--primary-color);
    font-size: 1.1rem;
}

/* Danger Card */
.danger-card {
    background: linear-gradient(135deg, #fff5f5, #ffe6e6);
    border: 2px solid #ff6b6b;
}

.danger-card p {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.btn-danger-modern {
    width: 100%;
    padding: 12px;
    background: var(--danger-color);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-danger-modern:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(220, 53, 69, 0.4);
}

/* Responsive */
@media (max-width: 992px) {
    .form-layout {
        grid-template-columns: 1fr;
    }
    
    .page-header-modern {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .btn-back {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .page-header-modern h1 {
        font-size: 1.8rem;
    }
    
    .form-group-modern {
        padding: 20px;
    }
    
    .editor-toolbar {
        gap: 3px;
        padding: 8px;
    }
    
    .btn-editor {
        width: 35px;
        height: 35px;
    }
}
</style>

<script>
// Update char count
function updateCharCount(textarea) {
    const count = textarea.value.length;
    document.getElementById('excerptCount').textContent = count;
}

// Update slug preview
function updateSlugPreview(title) {
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    
    const preview = document.getElementById('slugPreview');
    if (slug) {
        preview.textContent = `URL: <?= BASE_URL ?>/blog/show/${slug}`;
    }
}

// Preview image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            document.querySelector('.file-upload-label').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    document.getElementById('featuredImage').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.querySelector('.file-upload-label').style.display = 'flex';
}

// Markdown editor functions
function insertMarkdown(type) {
    const textarea = document.getElementById('contentEditor');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    let replacement = '';
    
    switch(type) {
        case 'bold':
            replacement = `**${selectedText || 'texte en gras'}**`;
            break;
        case 'italic':
            replacement = `*${selectedText || 'texte en italique'}*`;
            break;
        case 'heading':
            replacement = `\n## ${selectedText || 'Titre de section'}\n`;
            break;
        case 'link':
            replacement = `[${selectedText || 'texte du lien'}](url)`;
            break;
        case 'list':
            replacement = `\n- ${selectedText || 'élément de liste'}\n- élément 2\n`;
            break;
        case 'code':
            replacement = `\`${selectedText || 'code'}\``;
            break;
        case 'quote':
            replacement = `\n> ${selectedText || 'citation'}\n`;
            break;
    }
    
    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
}

function togglePreview() {
    const editor = document.getElementById('contentEditor');
    const preview = document.getElementById('previewPane');
    
    if (preview.style.display === 'none') {
        // Show preview
        preview.innerHTML = markdownToHtmlSimple(editor.value);
        preview.style.display = 'block';
        editor.style.display = 'none';
    } else {
        // Show editor
        preview.style.display = 'none';
        editor.style.display = 'block';
    }
}

function markdownToHtmlSimple(text) {
    return text
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>')
        .replace(/^- (.*$)/gim, '<li>$1</li>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
}

// Delete article
function deleteArticle() {
    if (!confirm('⚠️ ATTENTION : Cette action est IRRÉVERSIBLE.\n\nÊtes-vous absolument sûr de vouloir supprimer cet article ?')) {
        return;
    }
    
    if (!confirm('Dernière confirmation : Supprimer définitivement cet article ?')) {
        return;
    }
    
    window.location.href = '<?= BASE_URL ?>/blog/delete/<?= $post['slug'] ?>';
}

// Form validation
document.getElementById('articleForm')?.addEventListener('submit', function(e) {
    const title = this.querySelector('[name="title"]').value.trim();
    const excerpt = this.querySelector('[name="excerpt"]').value.trim();
    const content = this.querySelector('[name="content"]').value.trim();
    const category = this.querySelector('[name="category"]').value;
    
    if (title.length < 5) {
        e.preventDefault();
        alert('⚠️ Le titre doit contenir au moins 5 caractères');
        return false;
    }
    
    if (excerpt.length < 20) {
        e.preventDefault();
        alert('⚠️ Le résumé doit contenir au moins 20 caractères');
        return false;
    }
    
    if (content.length < 100) {
        e.preventDefault();
        alert('⚠️ Le contenu doit contenir au moins 100 caractères');
        return false;
    }
    
    if (!category) {
        e.preventDefault();
        alert('⚠️ Veuillez sélectionner une catégorie');
        return false;
    }
    
    // Show loading
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
});

// Initialize char counter
updateCharCount(document.querySelector('textarea[name="excerpt"]'));
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

