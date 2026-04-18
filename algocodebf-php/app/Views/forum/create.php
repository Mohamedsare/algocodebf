<?php
$pageTitle = 'Nouvelle Discussion - Forum AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Récupérer les anciennes valeurs et erreurs
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>

<section class="create-post-section">
    <div class="container">
        <div class="create-post-container">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Nouvelle Discussion</h1>
                <p>Partagez vos idées, posez vos questions et échangez avec la communauté</p>
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

            <form action="<?= BASE_URL ?>/forum/create" method="POST" class="create-post-form"
                enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Titre de la discussion *
                    </label>
                    <input type="text" id="title" name="title" class="form-control"
                        placeholder="Ex: Comment débuter en Python ?"
                        value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
                    <small class="form-hint">Soyez clair et précis dans votre titre</small>
                </div>

                <div class="form-group">
                    <label for="category">
                        <i class="fas fa-tag"></i> Catégorie *
                    </label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Choisir une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"
                            <?= ($old['category'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="body">
                        <i class="fas fa-align-left"></i> Contenu *
                    </label>
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" data-action="bold" title="Gras">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="italic" title="Italique">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="underline" title="Souligné">
                            <i class="fas fa-underline"></i>
                        </button>
                        <div class="toolbar-divider"></div>
                        <button type="button" class="toolbar-btn" data-action="heading" title="Titre">
                            <i class="fas fa-heading"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="list" title="Liste">
                            <i class="fas fa-list-ul"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="code" title="Code">
                            <i class="fas fa-code"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="link" title="Lien">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                    <textarea id="body" name="body" class="form-control editor-textarea" rows="15"
                        placeholder="Décrivez votre problème, partagez vos idées..."
                        required><?= htmlspecialchars($old['body'] ?? '') ?></textarea>
                    <small class="form-hint">Minimum 20 caractères. Markdown supporté.</small>
                </div>

                <div class="form-group">
                    <label for="tags">
                        <i class="fas fa-tags"></i> Tags (optionnel)
                    </label>
                    <input type="text" id="tags" name="tags" class="form-control"
                        placeholder="Ex: python, débutant, tutoriel"
                        value="<?= htmlspecialchars($old['tags'] ?? '') ?>">
                    <small class="form-hint">Séparez les tags par des virgules</small>
                </div>

                <div class="form-group">
                    <label for="attachments">
                        <i class="fas fa-paperclip"></i> Pièces jointes (optionnel)
                    </label>
                    <div class="file-upload-area">
                        <input type="file" id="attachments" name="attachments[]" class="file-input" multiple
                            accept="image/*,.pdf,.doc,.docx,.zip">
                        <label for="attachments" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Cliquez pour ajouter des fichiers</span>
                            <small>Images, PDF, Documents (Max 5MB par fichier)</small>
                        </label>
                        <div id="file-list" class="file-list"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Publier la discussion
                    </button>
                    <a href="<?= BASE_URL ?>/forum/index" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>

            <!-- Preview Section -->
            <div class="preview-section">
                <h3><i class="fas fa-eye"></i> Aperçu</h3>
                <div id="preview-content" class="preview-content">
                    <p class="text-muted">L'aperçu s'affichera ici...</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.create-post-section {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.create-post-container {
    max-width: 900px;
    margin: 0 auto;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.page-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

.create-post-form {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 30px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-hint {
    display: block;
    margin-top: 8px;
    color: #6c757d;
    font-size: 0.875rem;
}

/* Editor Toolbar */
.editor-toolbar {
    display: flex;
    gap: 5px;
    padding: 10px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    flex-wrap: wrap;
}

.toolbar-btn {
    width: 36px;
    height: 36px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: #495057;
}

.toolbar-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.toolbar-divider {
    width: 1px;
    background: #dee2e6;
    margin: 0 5px;
}

.editor-textarea {
    border-radius: 0 0 8px 8px;
    font-family: 'Courier New', monospace;
    resize: vertical;
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

.file-list {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.file-details {
    display: flex;
    flex-direction: column;
}

.file-name {
    font-weight: 600;
    color: var(--dark-color);
}

.file-size {
    font-size: 0.8rem;
    color: #6c757d;
}

.file-remove {
    background: none;
    border: none;
    color: var(--danger-color);
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.file-remove:hover {
    background: rgba(231, 76, 60, 0.1);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 40px;
    justify-content: center;
}

/* Preview Section */
.preview-section {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.preview-section h3 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.preview-content {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    min-height: 200px;
    line-height: 1.8;
}

.preview-content h1,
.preview-content h2,
.preview-content h3 {
    margin-top: 20px;
    margin-bottom: 10px;
}

.preview-content code {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.preview-content pre {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
}

/* Alert */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
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
@media (max-width: 768px) {
    .create-post-form {
        padding: 25px 20px;
    }

    .page-header h1 {
        font-size: 1.8rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// File upload handling
const fileInput = document.getElementById('attachments');
const fileList = document.getElementById('file-list');
let selectedFiles = [];

fileInput.addEventListener('change', function(e) {
    selectedFiles = Array.from(e.target.files);
    displayFiles();
});

function displayFiles() {
    fileList.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <div class="file-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="file-details">
                    <span class="file-name">${file.name}</span>
                    <span class="file-size">${formatFileSize(file.size)}</span>
                </div>
            </div>
            <button type="button" class="file-remove" onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    displayFiles();

    // Update file input
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Live preview
const bodyTextarea = document.getElementById('body');
const previewContent = document.getElementById('preview-content');

bodyTextarea.addEventListener('input', function() {
    const content = this.value;
    if (content.trim()) {
        // Simple markdown to HTML conversion
        let html = content
            .replace(/^### (.*$)/gim, '<h3>$1</h3>')
            .replace(/^## (.*$)/gim, '<h2>$1</h2>')
            .replace(/^# (.*$)/gim, '<h1>$1</h1>')
            .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/gim, '<em>$1</em>')
            .replace(/`(.*?)`/gim, '<code>$1</code>')
            .replace(/\n/gim, '<br>');
        previewContent.innerHTML = html;
    } else {
        previewContent.innerHTML = '<p class="text-muted">L\'aperçu s\'affichera ici...</p>';
    }
});

// Editor toolbar functionality
document.querySelectorAll('.toolbar-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.dataset.action;
        const textarea = document.getElementById('body');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        let newText = '';

        switch (action) {
            case 'bold':
                newText = `**${selectedText || 'texte en gras'}**`;
                break;
            case 'italic':
                newText = `*${selectedText || 'texte en italique'}*`;
                break;
            case 'heading':
                newText = `\n## ${selectedText || 'Titre'}\n`;
                break;
            case 'list':
                newText = `\n- ${selectedText || 'Élément de liste'}\n`;
                break;
            case 'code':
                newText = `\`${selectedText || 'code'}\``;
                break;
            case 'link':
                newText = `[${selectedText || 'texte du lien'}](url)`;
                break;
        }

        textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
        textarea.focus();
        textarea.selectionStart = start;
        textarea.selectionEnd = start + newText.length;

        // Trigger preview update
        bodyTextarea.dispatchEvent(new Event('input'));
    });
});

// Form validation
document.querySelector('.create-post-form').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category').value;
    const body = document.getElementById('body').value.trim();

    console.log('Form submission:', {
        title,
        category,
        body
    }); // Debug

    if (!category) {
        e.preventDefault();
        alert('Veuillez choisir une catégorie');
        document.getElementById('category').focus();
        return false;
    }

    if (title.length < 5) {
        e.preventDefault();
        alert('Le titre doit contenir au moins 5 caractères');
        document.getElementById('title').focus();
        return false;
    }

    if (body.length < 20) {
        e.preventDefault();
        alert('Le contenu doit contenir au moins 20 caractères');
        document.getElementById('body').focus();
        return false;
    }

    console.log('Form validation passed, submitting...'); // Debug
    return true;
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>