<?php
$pageTitle = 'Nouveau Message - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

$receiver = $receiver ?? null;
?>

<section class="compose-message-section">
    <div class="container">
        <div class="compose-container">
            <!-- Header -->
            <div class="compose-header">
                <h1>
                    <?php if (isset($reply_to) && $reply_to): ?>
                    <i class="fas fa-reply"></i> Répondre au Message
                    <?php else: ?>
                    <i class="fas fa-pen"></i> Nouveau Message
                    <?php endif; ?>
                </h1>
                <a href="<?= BASE_URL ?>/message/inbox" class="btn-back-inbox">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <?php if (isset($reply_to) && $reply_to && isset($receiver)): ?>
            <div class="reply-indicator">
                <i class="fas fa-info-circle"></i>
                <span>Vous répondez à un message de
                    <strong><?= htmlspecialchars(($receiver['prenom'] ?? '') . ' ' . ($receiver['nom'] ?? '')) ?></strong></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?= BASE_URL ?>/message/send" method="POST" class="compose-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">

                <div class="compose-card">
                    <!-- Destinataire -->
                    <div class="form-group-compose">
                        <label for="receiver_id">
                            <i class="fas fa-user"></i> Destinataire *
                        </label>
                        <?php if ($receiver): ?>
                        <div class="selected-receiver">
                            <div class="receiver-avatar">
                                <?php if (!empty($receiver['photo_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($receiver['photo_path']) ?>"
                                    alt="<?= htmlspecialchars($receiver['prenom'] . ' ' . $receiver['nom']) ?>">
                                <?php else: ?>
                                <div class="avatar-placeholder-compose">
                                    <?= strtoupper(substr($receiver['prenom'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="receiver-info">
                                <strong><?= htmlspecialchars($receiver['prenom'] . ' ' . $receiver['nom']) ?></strong>
                                <span><?= htmlspecialchars($receiver['university'] ?? '') ?></span>
                            </div>
                            <input type="hidden" name="receiver_id" value="<?= $receiver['id'] ?>">
                        </div>
                        <?php else: ?>
                        <div class="receiver-selector">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchUsers" placeholder="Rechercher un utilisateur..."
                                autocomplete="off">
                            <div class="users-dropdown" id="usersDropdown"></div>
                        </div>
                        <input type="hidden" name="receiver_id" id="receiverId" required>
                        <?php endif; ?>
                    </div>

                    <!-- Sujet -->
                    <div class="form-group-compose">
                        <label for="subject">
                            <i class="fas fa-heading"></i> Sujet *
                        </label>
                        <input type="text" id="subject" name="subject" class="form-input-compose"
                            placeholder="De quoi voulez-vous parler ?"
                            value="<?= htmlspecialchars($reply_subject ?? '') ?>" required>
                        <?php if (isset($reply_to) && $reply_to): ?>
                        <input type="hidden" name="reply_to" value="<?= $reply_to ?>">
                        <?php endif; ?>
                    </div>

                    <!-- Message -->
                    <div class="form-group-compose">
                        <label for="body">
                            <i class="fas fa-comment-alt"></i> Message *
                        </label>
                        <textarea id="body" name="body" class="form-textarea-compose" rows="12"
                            placeholder="Écrivez votre message ici..." required></textarea>
                        <div class="textarea-footer">
                            <span class="char-count" id="charCount">0 caractères</span>
                            <div class="formatting-tips">
                                <button type="button" class="tip-btn" onclick="insertText('**', '**')" title="Gras">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="tip-btn" onclick="insertText('*', '*')" title="Italique">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="tip-btn" onclick="insertText('`', '`')" title="Code">
                                    <i class="fas fa-code"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions-compose">
                        <button type="submit" class="btn-send-message">
                            <i class="fas fa-paper-plane"></i> Envoyer le message
                        </button>
                        <button type="button" class="btn-save-draft" onclick="saveDraft()">
                            <i class="fas fa-save"></i> Sauvegarder brouillon
                        </button>
                        <a href="<?= BASE_URL ?>/message/inbox" class="btn-cancel-compose">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </form>

            <!-- Tips Sidebar -->
            <div class="compose-tips">
                <div class="tips-card">
                    <h3><i class="fas fa-lightbulb"></i> Conseils</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> Soyez clair et concis</li>
                        <li><i class="fas fa-check"></i> Restez respectueux</li>
                        <li><i class="fas fa-check"></i> Vérifiez l'orthographe</li>
                        <li><i class="fas fa-check"></i> Répondez rapidement</li>
                    </ul>
                </div>

                <div class="tips-card">
                    <h3><i class="fas fa-shield-alt"></i> Sécurité</h3>
                    <p>Ne partagez jamais vos informations personnelles sensibles par message.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.compose-message-section {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.compose-container {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Header */
.compose-header {
    grid-column: 1 / -1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.compose-header h1 {
    margin: 0;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Reply Indicator */
.reply-indicator {
    grid-column: 1 / -1;
    padding: 15px 20px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
    border-left: 4px solid var(--primary-color);
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    color: #2c3e50;
    font-size: 0.95rem;
}

.reply-indicator i {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.reply-indicator strong {
    color: var(--primary-color);
}

.btn-back-inbox {
    padding: 12px 25px;
    background: white;
    color: var(--dark-color);
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.btn-back-inbox:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(-5px);
}

/* Form */
.compose-card {
    background: white;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.form-group-compose {
    margin-bottom: 30px;
}

.form-group-compose label {
    display: block;
    margin-bottom: 12px;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.selected-receiver {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-radius: 12px;
    border: 2px solid var(--primary-color);
}

.receiver-avatar img,
.avatar-placeholder-compose {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder-compose {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
}

.receiver-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.receiver-info strong {
    font-size: 1.1rem;
    color: var(--dark-color);
}

.receiver-info span {
    font-size: 0.9rem;
    color: #6c757d;
}

.receiver-selector {
    position: relative;
}

.receiver-selector i {
    position: absolute;
    left: 15px;
    top: 15px;
    color: #6c757d;
}

.receiver-selector input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
}

.receiver-selector input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.users-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    margin-top: 5px;
    max-height: 300px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.users-dropdown.show {
    display: block;
}

.user-option {
    padding: 12px 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.user-option:last-child {
    border-bottom: none;
}

.user-option:hover {
    background: linear-gradient(90deg, rgba(52, 152, 219, 0.08), rgba(46, 204, 113, 0.08));
    transform: translateX(3px);
}

.user-option:active {
    background: rgba(52, 152, 219, 0.15);
    transform: translateX(0);
}

.user-option.selected {
    background: linear-gradient(90deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.1));
    border-left: 3px solid var(--primary-color);
}

.form-input-compose {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-input-compose:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-textarea-compose {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1.05rem;
    resize: vertical;
    font-family: inherit;
    line-height: 1.7;
    transition: all 0.3s ease;
}

.form-textarea-compose:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.textarea-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.char-count {
    font-size: 0.85rem;
    color: #6c757d;
}

.formatting-tips {
    display: flex;
    gap: 5px;
}

.tip-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.3s ease;
}

.tip-btn:hover {
    background: var(--primary-color);
    color: white;
}

.form-actions-compose {
    display: flex;
    gap: 15px;
}

.btn-send-message {
    flex: 1;
    padding: 15px 30px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.05rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-send-message:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
}

.btn-save-draft {
    padding: 15px 25px;
    background: white;
    color: #6c757d;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-save-draft:hover {
    border-color: var(--warning-color);
    color: var(--warning-color);
}

.btn-cancel-compose {
    padding: 15px 25px;
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

.btn-cancel-compose:hover {
    border-color: var(--danger-color);
    color: var(--danger-color);
}

/* Tips Sidebar */
.compose-tips {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.tips-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.tips-card h3 {
    margin: 0 0 15px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.tips-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-card li {
    padding: 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.tips-card li i {
    color: var(--secondary-color);
}

.tips-card p {
    margin: 0;
    color: #6c757d;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 992px) {
    .compose-container {
        grid-template-columns: 1fr;
    }

    .compose-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}

@media (max-width: 768px) {
    .compose-message-section {
        padding: 30px 0 100px;
    }

    .compose-container {
        padding: 0 15px;
    }

    .compose-header {
        margin-bottom: 15px;
    }

    .compose-header h1 {
        font-size: 1.6rem;
    }

    .compose-card {
        padding: 20px;
    }

    .form-actions-compose {
        flex-direction: column;
    }

    .btn-send-message,
    .btn-save-draft,
    .btn-cancel-compose {
        width: 100%;
        justify-content: center;
    }

    .user-option {
        padding: 10px;
    }
}
</style>

<script>
// Character counter
const bodyTextarea = document.getElementById('body');
const charCount = document.getElementById('charCount');

if (bodyTextarea && charCount) {
    bodyTextarea.addEventListener('input', function() {
        charCount.textContent = `${this.value.length} caractères`;
    });
}

// Search users
let searchTimeout;

function searchUsers(query) {
    clearTimeout(searchTimeout);

    const dropdown = document.getElementById('usersDropdown');

    if (query.length < 2) {
        dropdown.classList.remove('show');
        return;
    }

    // Afficher un indicateur de chargement
    dropdown.innerHTML =
        '<div style="padding: 15px; text-align: center; color: #6c757d;"><i class="fas fa-spinner fa-spin"></i> Recherche en cours...</div>';
    dropdown.classList.add('show');

    searchTimeout = setTimeout(() => {
        fetch(`<?= BASE_URL ?>/message/searchUsers?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(users => {
                console.log('✅ Utilisateurs trouvés:', users.length);
                displayUserOptions(users);
            })
            .catch((error) => {
                console.error('❌ Erreur de recherche:', error);
                dropdown.innerHTML =
                    '<div style="padding: 15px; text-align: center; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Erreur de connexion. Réessayez.</div>';
                dropdown.classList.add('show');
            });
    }, 300);
}

// Attacher l'event listener au champ de recherche
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUsers');
    if (searchInput) {
        console.log('✅ Event listener attaché au champ de recherche');
        searchInput.addEventListener('input', function() {
            searchUsers(this.value);
        });
    } else {
        console.warn('⚠️ Champ de recherche #searchUsers non trouvé');
    }
});

function displayUserOptions(users) {
    const dropdown = document.getElementById('usersDropdown');
    dropdown.innerHTML = '';

    if (users.length === 0) {
        dropdown.innerHTML =
            '<div style="padding: 15px; text-align: center; color: #6c757d;"><i class="fas fa-search"></i> Aucun utilisateur trouvé</div>';
        dropdown.classList.add('show');
        return;
    }

    users.forEach(user => {
        const option = document.createElement('div');
        option.className = 'user-option';

        // Gérer l'avatar
        let avatarHTML = '';
        if (user.photo && user.photo.trim() !== '') {
            // Si le chemin ne commence pas par http ou /, ajouter BASE_URL
            const photoPath = user.photo.startsWith('http') || user.photo.startsWith('/') ?
                user.photo :
                `<?= BASE_URL ?>/${user.photo}`;
            avatarHTML =
                `<img src="${photoPath}" 
                             onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/images/default-avatar.png';"
                             style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">`;
        } else {
            // Avatar avec initiale
            const initial = user.prenom ? user.prenom.charAt(0).toUpperCase() : user.name.charAt(0)
                .toUpperCase();
            avatarHTML =
                `<div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #3498db, #2ecc71); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0;">${initial}</div>`;
        }

        option.innerHTML = `
            ${avatarHTML}
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: #2c3e50; font-size: 0.95rem;">${escapeHtml(user.name)}</div>
                ${user.university ? `<div style="font-size: 0.85rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(user.university)}</div>` : ''}
            </div>
            ${user.role === 'admin' ? '<span style="background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 600;">Admin</span>' : ''}
        `;
        option.onclick = () => selectUser(user);
        dropdown.appendChild(option);
    });

    dropdown.classList.add('show');
}

// Fonction pour échapper le HTML et éviter les XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function selectUser(user) {
    document.getElementById('receiverId').value = user.id;
    document.getElementById('searchUsers').value = user.name;
    document.getElementById('usersDropdown').classList.remove('show');

    // Mettre le focus sur le champ sujet pour une meilleure UX
    setTimeout(() => {
        document.getElementById('subject').focus();
    }, 100);
}

// Fermer le dropdown en cliquant en dehors
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('usersDropdown');
    const searchInput = document.getElementById('searchUsers');

    if (dropdown && searchInput &&
        !dropdown.contains(e.target) &&
        e.target !== searchInput) {
        dropdown.classList.remove('show');
    }
});

// Amélioration: Sélection avec les touches clavier (haut/bas/entrée)
document.getElementById('searchUsers')?.addEventListener('keydown', function(e) {
    const dropdown = document.getElementById('usersDropdown');
    const options = dropdown.querySelectorAll('.user-option');

    if (options.length === 0) return;

    let currentIndex = Array.from(options).findIndex(opt => opt.classList.contains('selected'));

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (currentIndex < options.length - 1) {
            if (currentIndex >= 0) options[currentIndex].classList.remove('selected');
            options[currentIndex + 1].classList.add('selected');
            options[currentIndex + 1].scrollIntoView({
                block: 'nearest'
            });
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (currentIndex > 0) {
            options[currentIndex].classList.remove('selected');
            options[currentIndex - 1].classList.add('selected');
            options[currentIndex - 1].scrollIntoView({
                block: 'nearest'
            });
        }
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (currentIndex >= 0) {
            options[currentIndex].click();
        }
    }
});

// Insert text formatting
function insertText(before, after) {
    const textarea = document.getElementById('body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const newText = before + (selectedText || 'texte') + after;

    textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
    textarea.focus();
    textarea.selectionStart = start + before.length;
    textarea.selectionEnd = start + before.length + (selectedText || 'texte').length;
}

// Save draft to localStorage
function saveDraft() {
    const draft = {
        receiver_id: document.getElementById('receiverId')?.value || '',
        subject: document.getElementById('subject').value,
        body: document.getElementById('body').value
    };

    localStorage.setItem('message_draft', JSON.stringify(draft));
    alert('✅ Brouillon sauvegardé');
}

// Load draft on page load
window.addEventListener('load', () => {
    const draft = localStorage.getItem('message_draft');
    if (draft) {
        const shouldRestore = confirm('Un brouillon est disponible. Voulez-vous le restaurer ?');
        if (shouldRestore) {
            const data = JSON.parse(draft);
            if (data.subject) document.getElementById('subject').value = data.subject;
            if (data.body) document.getElementById('body').value = data.body;
        }
    }
});

// Clear draft on send
document.querySelector('.compose-form')?.addEventListener('submit', () => {
    localStorage.removeItem('message_draft');
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>