<?php
$pageTitle = 'Créer un article - Blog AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Récupérer les anciennes valeurs et erreurs
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

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
                <a href="<?= BASE_URL ?>/blog/index" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Retour au blog
                </a>
            </div>
            <div class="header-center">
                <h1><i class="fas fa-pen-fancy"></i> Créer un article</h1>
                <p>Partagez vos connaissances avec la communauté</p>
            </div>
            <div class="header-right"></div>
        </div>

        <!-- Formulaire -->
        <form class="article-form" method="POST" enctype="multipart/form-data" id="articleForm">
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
                        <div class="slug-preview" id="slugPreview"></div>
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
                                  maxlength="300"><?= htmlspecialchars($old['excerpt'] ?? '') ?></textarea>
                        <div class="char-counter">
                            <span id="excerptCount">0</span>/300 caractères
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
                        <textarea name="content" 
                                  id="contentEditor"
                                  class="form-control-modern" 
                                  rows="20"
                                  placeholder="Rédigez votre article..."
                                  required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                        <div class="editor-help">
                            <i class="fas fa-info-circle"></i> 
                            Utilisez l'éditeur WYSIWYG pour formater votre texte
                        </div>
                        <?php if (isset($errors['content'])): ?>
                            <span class="form-error"><?= $errors['content'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Image à la une -->
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-image"></i> Image à la une
                        </label>
                        <div class="file-upload-modern">
                            <input type="file" 
                                   name="featured_image" 
                                   id="featuredImage"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <label for="featuredImage" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Cliquez pour choisir une image</span>
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
                                <option value="published" <?= ($old['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                                    ✅ Publier
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
                            <i class="fas fa-paper-plane"></i> Publier l'article
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

                    <!-- Conseils -->
                    <div class="sidebar-card-form tips-card">
                        <h4><i class="fas fa-lightbulb"></i> Conseils</h4>
                        <ul class="tips-list">
                            <li>Utilisez un titre accrocheur</li>
                            <li>Ajoutez une belle image</li>
                            <li>Structurez avec des titres</li>
                            <li>Relisez avant de publier</li>
                        </ul>
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

/* Tips Card */
.tips-card {
    background: linear-gradient(135deg, #fff8e1, #ffe9b3);
    border: 2px solid #ffc107;
}

.tips-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-list li {
    padding: 10px 0;
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tips-list li::before {
    content: '✓';
    color: #28a745;
    font-weight: 700;
    font-size: 1.2rem;
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
// Update slug preview
function updateSlugPreview(title) {
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
    
    const preview = document.getElementById('slugPreview');
    if (slug) {
        preview.textContent = `URL: <?= BASE_URL ?>/blog/show/${slug}`;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// Character counter for excerpt
document.querySelector('textarea[name="excerpt"]')?.addEventListener('input', function() {
    document.getElementById('excerptCount').textContent = this.value.length;
});

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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';
});

// Initialiser TinyMCE
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#contentEditor',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
            'template', 'paste', 'textcolor', 'colorpicker'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | link image media | code preview | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
        branding: false,
        promotion: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        },
        paste_data_images: true,
        images_upload_handler: function (blobInfo, success, failure) {
            // Gérer l'upload d'images si nécessaire
            success(blobInfo.blobUri());
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

