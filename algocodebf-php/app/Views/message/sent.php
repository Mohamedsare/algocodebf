<?php
$pageTitle = 'Messages Envoyés - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Préparer les données par défaut
$messages = $messages ?? [];
?>

<section class="messaging-section">
    <div class="container-fluid">
        <div class="messaging-wrapper">
            <!-- Sidebar - Liste des messages envoyés -->
            <aside class="messages-sidebar">
                <div class="sidebar-header-msg">
                    <h2><i class="fas fa-paper-plane"></i> Messages Envoyés</h2>
                    <a href="<?= BASE_URL ?>/message/compose" class="btn-new-msg">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>

                <!-- Tabs -->
                <div class="msg-tabs">
                    <a href="<?= BASE_URL ?>/message/inbox" class="msg-tab">
                        <i class="fas fa-inbox"></i> Reçus
                    </a>
                    <a href="<?= BASE_URL ?>/message/sent" class="msg-tab active">
                        <i class="fas fa-paper-plane"></i> Envoyés
                    </a>
                </div>

                <!-- Search -->
                <div class="search-msg">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           id="searchMessages" 
                           placeholder="Rechercher un message..."
                           onkeyup="searchMessages()">
                </div>

                <!-- Messages List -->
                <div class="messages-list">
                    <?php if (empty($messages)): ?>
                        <div class="no-messages">
                            <i class="fas fa-paper-plane"></i>
                            <h4>Aucun message envoyé</h4>
                            <p>Vous n'avez envoyé aucun message pour le moment</p>
                            <a href="<?= BASE_URL ?>/message/compose" class="btn-compose-msg">
                                <i class="fas fa-pen"></i> Nouveau message
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item"
                                 data-message-id="<?= $msg['id'] ?>">
                                <div class="msg-avatar">
                                    <?php if (!empty($msg['receiver_photo'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($msg['receiver_photo']) ?>" 
                                             alt="<?= htmlspecialchars($msg['receiver_name']) ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-msg">
                                            <?= strtoupper(substr($msg['receiver_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="msg-content">
                                    <div class="msg-header">
                                        <h4 class="msg-sender">À: <?= htmlspecialchars($msg['receiver_name'] ?? 'Utilisateur') ?></h4>
                                        <span class="msg-time"><?= timeAgo($msg['created_at']) ?></span>
                                    </div>
                                    <p class="msg-subject"><?= htmlspecialchars($msg['subject'] ?? 'Sans sujet') ?></p>
                                    <p class="msg-preview">
                                        <?= htmlspecialchars(substr($msg['body'], 0, 80)) ?>...
                                    </p>
                                </div>
                                
                                <div class="msg-actions-inline">
                                    <button class="btn-view-msg" onclick="openMessageModal(<?= $msg['id'] ?>)" title="Voir le message">
                                        <i class="fas fa-eye"></i> Voir
                                    </button>
                                    <?php if ($msg['is_read'] ?? false): ?>
                                        <span class="read-status-badge" title="Lu">
                                            <i class="fas fa-check-double"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Main - Message View -->
            <main class="messages-main">
                <div class="message-placeholder" id="messagePlaceholder">
                    <div class="placeholder-content">
                        <i class="fas fa-paper-plane"></i>
                        <h3>Messages envoyés</h3>
                        <p>Cliquez sur "Voir" dans un message pour afficher son contenu</p>
                        <a href="<?= BASE_URL ?>/message/compose" class="btn btn-primary btn-lg">
                            <i class="fas fa-pen"></i> Nouveau Message
                        </a>
                    </div>
                </div>

                <div class="message-view" id="messageView" style="display: none;">
                    <div class="message-view-header">
                        <div class="back-btn-mobile" onclick="closeMobileView()">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        <div class="msg-view-sender">
                            <img src="" alt="" id="viewReceiverPhoto" class="sender-avatar">
                            <div class="sender-info">
                                <h3 id="viewReceiverName"></h3>
                                <span id="viewReceiverEmail"></span>
                            </div>
                        </div>
                        <div class="msg-view-actions">
                            <button class="btn-icon-msg" onclick="deleteMessage()" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="message-view-body">
                        <div class="msg-view-subject">
                            <h2 id="viewSubject"></h2>
                            <span class="msg-view-date" id="viewDate"></span>
                        </div>
                        <div class="msg-view-content" id="viewContent">
                            <!-- Message content will be loaded here -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</section>

<!-- Modal pour afficher le message -->
<div class="message-modal-overlay" id="messageModal" style="display: none;">
    <div class="message-modal-container">
        <div class="message-modal-header">
            <h3 id="modalSubject">Chargement...</h3>
            <button class="btn-close-modal" onclick="closeMessageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="message-modal-body">
            <!-- Sender/Receiver Info -->
            <div class="modal-sender-info">
                <div class="modal-sender-avatar" id="modalSenderAvatar">
                    <div class="avatar-placeholder-modal">U</div>
                </div>
                <div class="modal-sender-details">
                    <h4 id="modalSenderName">Chargement...</h4>
                    <p id="modalSenderEmail">-</p>
                    <p class="modal-date"><i class="fas fa-clock"></i> <span id="modalDate">-</span></p>
                </div>
            </div>
            
            <!-- Message Content -->
            <div class="modal-message-content" id="modalContent">
                <p><i class="fas fa-spinner fa-spin"></i> Chargement du message...</p>
            </div>
        </div>
        
        <div class="message-modal-footer">
            <button class="btn-modal-delete" onclick="deleteMessageFromModal()">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<style>
.messaging-section {
    padding: 30px 0 60px;
    background: #f8f9fa;
}

.container-fluid {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.messaging-wrapper {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 0;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
    min-height: calc(100vh - 180px);
}

/* Sidebar */
.messages-sidebar {
    border-right: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    background: #fafbfc;
}

.sidebar-header-msg {
    padding: 25px;
    background: white;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header-msg h2 {
    margin: 0;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.btn-new-msg {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.btn-new-msg:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

.msg-tabs {
    display: flex;
    padding: 15px;
    gap: 10px;
    background: white;
    border-bottom: 1px solid #e9ecef;
}

.msg-tab {
    flex: 1;
    padding: 10px 15px;
    background: transparent;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.msg-tab:hover {
    background: rgba(52, 152, 219, 0.08);
    color: var(--primary-color);
}

.msg-tab.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.search-msg {
    padding: 15px;
    background: white;
    border-bottom: 1px solid #e9ecef;
    position: relative;
}

.search-msg i {
    position: absolute;
    left: 30px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.search-msg input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #e9ecef;
    border-radius: 20px;
    background: #f8f9fa;
    font-size: 0.9rem;
}

.search-msg input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
}

/* Messages List */
.messages-list {
    flex: 1;
    overflow-y: auto;
}

.message-item {
    display: flex;
    gap: 12px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    border-bottom: 1px solid #f0f0f0;
    background: white;
}

.message-item:hover {
    background: #f8f9fa;
}

.message-item.active {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(46, 204, 113, 0.05));
    border-left: 4px solid var(--primary-color);
}

.msg-avatar img,
.avatar-placeholder-msg {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder-msg {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.msg-content {
    flex: 1;
    min-width: 0;
}

.msg-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.msg-sender {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark-color);
}

.msg-time {
    font-size: 0.8rem;
    color: #6c757d;
}

.msg-subject {
    margin: 0 0 5px;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--dark-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.msg-preview {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.read-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: 10px;
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.read-status-badge i {
    color: var(--primary-color);
}

.no-messages {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-messages i {
    font-size: 4rem;
    margin-bottom: 15px;
    opacity: 0.3;
}

.no-messages h4 {
    margin-bottom: 8px;
    color: var(--dark-color);
}

.btn-compose-msg {
    margin-top: 20px;
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 20px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-compose-msg:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* Main Message View */
.messages-main {
    display: flex;
    flex-direction: column;
    background: white;
}

.message-placeholder {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.placeholder-content {
    text-align: center;
    color: #6c757d;
    max-width: 400px;
}

.placeholder-content i {
    font-size: 6rem;
    margin-bottom: 25px;
    opacity: 0.2;
    color: var(--primary-color);
}

.placeholder-content h3 {
    margin-bottom: 10px;
    color: var(--dark-color);
    font-size: 1.5rem;
}

.placeholder-content p {
    margin-bottom: 30px;
    font-size: 1.05rem;
}

/* Message View */
.message-view {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.message-view-header {
    padding: 20px 30px;
    background: white;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 20px;
}

.back-btn-mobile {
    display: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.msg-view-sender {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 15px;
}

.sender-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f0f0f0;
}

.sender-info h3 {
    margin: 0 0 5px;
    font-size: 1.2rem;
}

.sender-info span {
    color: #6c757d;
    font-size: 0.9rem;
}

.msg-view-actions {
    display: flex;
    gap: 10px;
}

.btn-icon-msg {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.3s ease;
}

.btn-icon-msg:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.08);
}

.message-view-body {
    flex: 1;
    overflow-y: auto;
    padding: 30px;
}

.msg-view-subject {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.msg-view-subject h2 {
    margin: 0 0 10px;
    font-size: 1.8rem;
    color: var(--dark-color);
}

.msg-view-date {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.msg-view-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--dark-color);
}

/* Responsive */
@media (max-width: 992px) {
    .messaging-wrapper {
        grid-template-columns: 1fr;
        min-height: calc(100vh - 160px);
    }
    
    .messages-sidebar {
        display: none;
    }
    
    .messages-sidebar.mobile-show {
        display: flex;
    }
    
    .back-btn-mobile {
        display: flex;
    }
}

@media (max-width: 768px) {
    .message-view-header {
        padding: 15px;
    }
    
    .message-view-body {
        padding: 20px 15px;
    }
    
    .msg-view-subject h2 {
        font-size: 1.4rem;
    }
}

/* Modal Message (même style que inbox) */
.message-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.message-modal-container {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 700px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-modal-header {
    padding: 25px 30px;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.message-modal-header h3 {
    margin: 0;
    color: var(--dark-color);
    font-size: 1.4rem;
    flex: 1;
}

.btn-close-modal {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.btn-close-modal:hover {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
    transform: rotate(90deg);
}

.message-modal-body {
    padding: 30px;
    overflow-y: auto;
    flex: 1;
}

.modal-sender-info {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.modal-sender-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--primary-color);
    flex-shrink: 0;
}

.modal-sender-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder-modal {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
}

.modal-sender-details h4 {
    margin: 0 0 5px;
    color: var(--dark-color);
    font-size: 1.2rem;
}

.modal-sender-details p {
    margin: 0;
    color: #6c757d;
    font-size: 0.95rem;
}

.modal-date {
    margin-top: 8px !important;
    display: flex;
    align-items: center;
    gap: 6px;
}

.modal-message-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    line-height: 1.8;
    color: #2c3e50;
    font-size: 1rem;
    margin-bottom: 20px;
    white-space: pre-wrap;
}

.message-modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #e9ecef;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn-modal-delete {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    color: #6c757d;
}

.btn-modal-delete:hover {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
}

.btn-view-msg {
    padding: 8px 16px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-view-msg:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

.btn-view-msg i {
    font-size: 0.95rem;
}

.msg-actions-inline {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Responsive Modal */
@media (max-width: 768px) {
    .message-modal-container {
        max-width: 100%;
        max-height: 95vh;
        border-radius: 15px;
    }
    
    .message-modal-header {
        padding: 20px;
    }
    
    .message-modal-header h3 {
        font-size: 1.1rem;
    }
    
    .message-modal-body {
        padding: 20px;
    }
    
    .message-modal-footer {
        padding: 15px 20px;
    }
    
    .btn-modal-delete {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
let currentMessageId = null;
let currentModalMessageData = null;

// Ouvrir le modal avec le message
function openMessageModal(messageId) {
    currentMessageId = messageId;
    const modal = document.getElementById('messageModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    loadMessageInModal(messageId);
}

// Fermer le modal
function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    currentMessageId = null;
    currentModalMessageData = null;
}

// Fermer le modal si on clique en dehors
document.addEventListener('click', function(e) {
    const modal = document.getElementById('messageModal');
    if (e.target === modal) {
        closeMessageModal();
    }
});

// Charger le message dans le modal
function loadMessageInModal(messageId) {
    fetch(`<?= BASE_URL ?>/message/show/${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentModalMessageData = data;
                displayMessageInModal(data);
            } else {
                alert('Erreur: ' + (data.message || 'Impossible de charger le message'));
                closeMessageModal();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du message');
            closeMessageModal();
        });
}

// Afficher le message dans le modal
function displayMessageInModal(data) {
    document.getElementById('modalSubject').textContent = data.subject;
    
    const avatarContainer = document.getElementById('modalSenderAvatar');
    if (data.sender_photo) {
        avatarContainer.innerHTML = `<img src="<?= BASE_URL ?>/${data.sender_photo}" alt="${data.sender_name}">`;
    } else {
        const initial = data.sender_name.charAt(0).toUpperCase();
        avatarContainer.innerHTML = `<div class="avatar-placeholder-modal">${initial}</div>`;
    }
    
    document.getElementById('modalSenderName').textContent = data.sender_name;
    document.getElementById('modalSenderEmail').textContent = data.sender_email;
    document.getElementById('modalDate').textContent = data.created_at;
    document.getElementById('modalContent').innerHTML = data.body.replace(/\n/g, '<br>');
}

// Supprimer le message depuis le modal
function deleteMessageFromModal() {
    if (!currentMessageId) return;
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?= BASE_URL ?>/message/delete/${currentMessageId}`;
    document.body.appendChild(form);
    form.submit();
}

function searchMessages() {
    const query = document.getElementById('searchMessages').value.toLowerCase();
    document.querySelectorAll('.message-item').forEach(item => {
        const content = item.textContent.toLowerCase();
        if (content.includes(query)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; 
?>

