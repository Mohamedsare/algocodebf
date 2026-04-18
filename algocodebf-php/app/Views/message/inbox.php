<?php
$pageTitle = 'Messagerie - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';

// Préparer les données par défaut
$messages = $messages ?? [];
$unread_count = $unread_count ?? 0;
?>

<section class="messaging-section">
    <div class="container-fluid">
        <div class="messaging-wrapper">
            <!-- Sidebar - Liste des conversations -->
            <aside class="messages-sidebar">
                <div class="sidebar-header-msg">
                    <h2><i class="fas fa-envelope"></i> Messagerie</h2>
                    <a href="<?= BASE_URL ?>/message/compose" class="btn-new-msg">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>

                <!-- Tabs -->
                <div class="msg-tabs">
                    <a href="<?= BASE_URL ?>/message/inbox" class="msg-tab active">
                        <i class="fas fa-inbox"></i> 
                        Reçus
                        <?php if ($unread_count > 0): ?>
                            <span class="badge-count"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= BASE_URL ?>/message/sent" class="msg-tab">
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
                            <i class="fas fa-inbox"></i>
                            <h4>Aucun message</h4>
                            <p>Votre boîte de réception est vide</p>
                            <a href="<?= BASE_URL ?>/message/compose" class="btn-compose-msg">
                                <i class="fas fa-pen"></i> Nouveau message
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item <?= !($msg['is_read'] ?? true) ? 'unread' : '' ?>"
                                 data-message-id="<?= $msg['id'] ?>">
                                <div class="msg-avatar">
                                    <?php if (!empty($msg['sender_photo'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($msg['sender_photo']) ?>" 
                                             alt="<?= htmlspecialchars($msg['sender_name']) ?>">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-msg">
                                            <?= strtoupper(substr($msg['sender_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="msg-content">
                                    <div class="msg-header">
                                        <h4 class="msg-sender"><?= htmlspecialchars($msg['sender_name'] ?? 'Utilisateur') ?></h4>
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
                                </div>
                                
                                <?php if (!($msg['is_read'] ?? true)): ?>
                                    <div class="unread-dot"></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Main - Message View -->
            <main class="messages-main">
                <div class="message-placeholder" id="messagePlaceholder">
                    <div class="placeholder-content">
                        <i class="fas fa-envelope-open"></i>
                        <h3>Bienvenue dans votre messagerie</h3>
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
                            <img src="" alt="" id="viewSenderPhoto" class="sender-avatar">
                            <div class="sender-info">
                                <h3 id="viewSenderName"></h3>
                                <span id="viewSenderEmail"></span>
                            </div>
                        </div>
                        <div class="msg-view-actions">
                            <button class="btn-icon-msg" onclick="replyMessage()" title="Répondre">
                                <i class="fas fa-reply"></i>
                            </button>
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
                        
                        <!-- Actions pour les demandes de projet -->
                        <div class="message-actions" id="messageActions" style="display: none;">
                            <div class="action-card">
                                <h4><i class="fas fa-hand-pointer"></i> Actions requises</h4>
                                <p id="actionDescription"></p>
                                <div class="action-buttons">
                                    <button class="btn-action-accept" id="btnAcceptAction">
                                        <i class="fas fa-check"></i> Accepter
                                    </button>
                                    <button class="btn-action-reject" id="btnRejectAction">
                                        <i class="fas fa-times"></i> Refuser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Form (hidden by default) -->
                    <div class="reply-form" id="replyForm" style="display: none;">
                        <div class="reply-form-header">
                            <h4><i class="fas fa-reply"></i> Répondre</h4>
                            <button class="btn-close-reply" onclick="closeReplyForm()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form action="<?= BASE_URL ?>/message/send" method="POST">
                            <input type="hidden" name="receiver_id" id="replyReceiverId">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <textarea name="body" 
                                      class="reply-textarea" 
                                      placeholder="Tapez votre réponse..." 
                                      rows="4" 
                                      required></textarea>
                            <div class="reply-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Envoyer
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="closeReplyForm()">
                                    Annuler
                                </button>
                            </div>
                        </form>
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
            <!-- Sender Info -->
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
            
            <!-- Actions pour les demandes de projet -->
            <div class="modal-actions" id="modalActions" style="display: none;">
                <div class="modal-action-card">
                    <h4><i class="fas fa-hand-pointer"></i> Actions requises</h4>
                    <p id="modalActionDescription"></p>
                    <div class="modal-action-buttons">
                        <button class="btn-modal-accept" id="btnModalAccept">
                            <i class="fas fa-check"></i> Accepter
                        </button>
                        <button class="btn-modal-reject" id="btnModalReject">
                            <i class="fas fa-times"></i> Refuser
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="message-modal-footer">
            <button class="btn-modal-reply" onclick="replyToMessage()">
                <i class="fas fa-reply"></i> Répondre
            </button>
            <button class="btn-modal-delete" onclick="deleteMessageFromModal()">
                <i class="fas fa-trash"></i> Supprimer
            </button>
        </div>
    </div>
</div>

<style>
/* ===================================
   MOBILE FIRST DESIGN - BASE STYLES
   =================================== */

.messaging-section {
    padding: 10px 0 100px; /* Mobile first : moins de padding, espace pour nav */
    background: #f8f9fa;
    min-height: 100vh;
}

.container-fluid {
    max-width: 100%;
    margin: 0 auto;
    padding: 0; /* Mobile first : pas de padding latéral */
}

.messaging-wrapper {
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 0; /* Mobile first : pas de border radius */
    overflow: hidden;
    box-shadow: none; /* Mobile first : pas d'ombre */
    min-height: calc(100vh - 110px);
}

/* ===================================
   SIDEBAR - MOBILE FIRST
   =================================== */

.messages-sidebar {
    border-right: none; /* Mobile first : pas de bordure */
    display: flex;
    flex-direction: column;
    background: white;
    width: 100%;
    height: 100%;
}

.sidebar-header-msg {
    padding: 16px 16px; /* Mobile first : padding optimal pour touch */
    background: white;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.sidebar-header-msg h2 {
    margin: 0;
    font-size: 1.25rem; /* Mobile first : taille lisible mais pas trop grande */
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--dark-color);
    font-weight: 700;
}

/* ===================================
   BOUTONS - MOBILE FIRST (min 48x48px)
   =================================== */

.btn-new-msg {
    width: 48px; /* Mobile first : zone tactile optimale */
    height: 48px;
    min-width: 48px;
    min-height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 8px rgba(200, 16, 46, 0.3);
    flex-shrink: 0;
}

.btn-new-msg:active {
    transform: scale(0.95); /* Mobile first : feedback tactile */
}

/* ===================================
   ONGLETS - MOBILE FIRST
   =================================== */

.msg-tabs {
    display: flex;
    padding: 12px;
    gap: 8px;
    background: white;
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 60px;
    z-index: 9;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.msg-tab {
    flex: 1;
    padding: 12px 16px; /* Mobile first : padding généreux */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    background: transparent;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
}

.msg-tab:active {
    transform: scale(0.98); /* Mobile first : feedback tactile */
}

.msg-tab.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 2px 8px rgba(200, 16, 46, 0.2);
}

.badge-count {
    background: #ff5252;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
}

/* ===================================
   RECHERCHE - MOBILE FIRST
   =================================== */

.search-msg {
    padding: 12px 16px;
    background: white;
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 120px;
    z-index: 8;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.search-msg i {
    position: absolute;
    left: 28px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1rem;
}

.search-msg input {
    width: 100%;
    padding: 14px 16px 14px 44px; /* Mobile first : padding généreux */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    border: 2px solid #e9ecef;
    border-radius: 24px;
    background: #f8f9fa;
    font-size: 1rem; /* Mobile first : 16px minimum pour éviter le zoom sur iOS */
    transition: all 0.2s ease;
}

.search-msg input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: white;
    box-shadow: 0 2px 12px rgba(200, 16, 46, 0.1);
}

.search-msg input::placeholder {
    color: #adb5bd;
}

/* ===================================
   LISTE DE MESSAGES - MOBILE FIRST
   =================================== */

.messages-list {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch; /* Mobile first : smooth scrolling iOS */
}

.message-item {
    display: flex;
    gap: 14px;
    padding: 16px; /* Mobile first : padding généreux */
    min-height: 88px; /* Mobile first : hauteur confortable */
    cursor: pointer;
    transition: background 0.2s ease;
    position: relative;
    border-bottom: 1px solid #f0f0f0;
    background: white;
    -webkit-tap-highlight-color: rgba(200, 16, 46, 0.1); /* Mobile first : feedback visuel au tap */
}

.message-item:active {
    background: rgba(200, 16, 46, 0.05); /* Mobile first : feedback tactile */
}

.message-item.unread {
    background: rgba(200, 16, 46, 0.02);
    border-left: 4px solid var(--primary-color);
    padding-left: 12px;
}

.message-item.active {
    background: linear-gradient(135deg, rgba(200, 16, 46, 0.08), rgba(0, 106, 78, 0.05));
    border-left: 4px solid var(--primary-color);
    padding-left: 12px;
}

.msg-avatar {
    flex-shrink: 0;
}

.msg-avatar img,
.avatar-placeholder-msg {
    width: 56px; /* Mobile first : avatar plus grand pour meilleure visibilité */
    height: 56px;
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
    font-size: 1.3rem;
}

.msg-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
}

.msg-actions-inline {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.btn-view-msg {
    padding: 10px 18px; /* Mobile first : padding généreux */
    min-width: 80px;
    min-height: 42px; /* Mobile first : zone tactile proche de 48px */
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white !important;
    border: none;
    border-radius: 21px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(200, 16, 46, 0.2);
}

.btn-view-msg:active {
    transform: scale(0.96); /* Mobile first : feedback tactile */
}

.btn-view-msg i {
    font-size: 0.9rem;
}

/* ===================================
   TEXTES DES MESSAGES - MOBILE FIRST
   =================================== */

.msg-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.msg-sender {
    margin: 0;
    font-size: 1.05rem; /* Mobile first : taille lisible */
    font-weight: 600;
    color: var(--dark-color);
    line-height: 1.3;
}

.msg-time {
    font-size: 0.85rem;
    color: #868e96;
    flex-shrink: 0;
    margin-left: 8px;
}

.msg-subject {
    margin: 0 0 4px;
    font-size: 0.95rem; /* Mobile first : taille lisible */
    font-weight: 600;
    color: var(--dark-color);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-item.unread .msg-subject {
    color: var(--primary-color);
    font-weight: 700;
}

.msg-preview {
    margin: 0;
    font-size: 0.9rem; /* Mobile first : taille lisible */
    color: #6c757d;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Mobile first : afficher 2 lignes */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-dot {
    width: 10px;
    height: 10px;
    background: var(--primary-color);
    border-radius: 50%;
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
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

/* ===================================
   VUE DU MESSAGE - MOBILE FIRST
   =================================== */

.message-view-header {
    padding: 16px; /* Mobile first : padding compact */
    background: white;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
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
    gap: 8px;
    flex-shrink: 0;
}

.btn-icon-msg {
    width: 48px; /* Mobile first : zone tactile optimale */
    height: 48px;
    min-width: 48px;
    min-height: 48px;
    border-radius: 50%;
    border: none;
    background: rgba(200, 16, 46, 0.08);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    transition: all 0.2s ease;
}

.btn-icon-msg:active {
    transform: scale(0.92); /* Mobile first : feedback tactile */
    background: rgba(200, 16, 46, 0.15);
}

.message-view-body {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch; /* Mobile first : smooth scrolling iOS */
    padding: 20px 16px; /* Mobile first : padding optimal */
}

.msg-view-subject {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f0f0;
}

.msg-view-subject h2 {
    margin: 0 0 10px;
    font-size: 1.4rem; /* Mobile first : taille lisible */
    line-height: 1.3;
    color: var(--dark-color);
    font-weight: 700;
}

.msg-view-date {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.msg-view-content {
    font-size: 1.05rem; /* Mobile first : taille lisible (16.8px) */
    line-height: 1.7;
    color: var(--dark-color);
    word-break: break-word;
}

/* Reply Form */
.reply-form {
    padding: 25px 30px;
    background: #f8f9fa;
    border-top: 2px solid #e9ecef;
}

.reply-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.reply-form-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-close-reply {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #6c757d;
    cursor: pointer;
    transition: color 0.3s ease;
}

.btn-close-reply:hover {
    color: var(--danger-color);
}

.reply-textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    resize: vertical;
    margin-bottom: 15px;
    font-family: inherit;
}

.reply-textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.reply-actions {
    display: flex;
    gap: 10px;
}

/* Message Actions */
.message-actions {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #e9ecef;
}

.action-card {
    background: linear-gradient(135deg, #fff8e1, #ffe9b3);
    border: 2px solid #ffc107;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
}

.action-card h4 {
    margin: 0 0 15px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 1.2rem;
}

.action-card p {
    margin: 0 0 20px;
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.6;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-action-accept,
.btn-action-reject {
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-action-accept {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.btn-action-accept:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
}

.btn-action-reject {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-action-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(220, 53, 69, 0.4);
}

.action-card.action-accepted {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border-color: #28a745;
}

.action-card.action-rejected {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border-color: #dc3545;
}

.action-card.action-accepted i,
.action-card.action-rejected i {
    font-size: 3rem;
    margin-bottom: 15px;
}

.action-card.action-accepted i {
    color: #28a745;
}

.action-card.action-rejected i {
    color: #dc3545;
}

.action-card.action-accepted p,
.action-card.action-rejected p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

/* Modal Message */
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

.modal-actions {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 2px solid #e9ecef;
}

.modal-action-card {
    background: linear-gradient(135deg, #fff8e1, #ffe9b3);
    border: 2px solid #ffc107;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
}

.modal-action-card h4 {
    margin: 0 0 15px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 1.2rem;
}

.modal-action-card p {
    margin: 0 0 20px;
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.6;
}

.modal-action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-modal-accept,
.btn-modal-reject {
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-modal-accept {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.btn-modal-accept:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
}

.btn-modal-reject {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-modal-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(220, 53, 69, 0.4);
}

.message-modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #e9ecef;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

.btn-modal-reply,
.btn-modal-delete {
    padding: 14px 20px; /* Mobile first : padding généreux */
    min-height: 48px; /* Mobile first : zone tactile optimale */
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.btn-modal-reply {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white !important;
    box-shadow: 0 2px 8px rgba(200, 16, 46, 0.2);
}

.btn-modal-reply:active {
    transform: scale(0.97); /* Mobile first : feedback tactile */
}

.btn-modal-delete {
    background: rgba(220, 53, 69, 0.1);
    border: 2px solid rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.btn-modal-delete:active {
    transform: scale(0.97); /* Mobile first : feedback tactile */
    background: rgba(220, 53, 69, 0.15);
}

/* ===================================
   MODAL RESPONSIVE - MOBILE OPTIMISÉ
   =================================== */

@media (max-width: 768px) {
    .message-modal-overlay {
        padding: 0; /* Mobile first : plein écran */
    }
    
    .message-modal-container {
        max-width: 100%;
        max-height: 100vh; /* Mobile first : plein écran */
        border-radius: 0; /* Mobile first : pas de bordure */
        height: 100vh;
    }
    
    .message-modal-header {
        padding: 16px;
        position: sticky;
        top: 0;
        z-index: 10;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .message-modal-header h3 {
        font-size: 1.15rem;
        line-height: 1.3;
    }
    
    .btn-close-modal {
        width: 48px;
        height: 48px;
        min-width: 48px;
        min-height: 48px;
    }
    
    .message-modal-body {
        padding: 16px;
    }
    
    .modal-sender-avatar {
        width: 56px;
        height: 56px;
    }
    
    .modal-message-content {
        font-size: 1.05rem;
        line-height: 1.7;
    }
    
    .modal-action-buttons {
        flex-direction: column;
        gap: 12px;
    }
    
    .btn-modal-accept,
    .btn-modal-reject {
        width: 100%;
        justify-content: center;
        min-height: 48px;
    }
    
    .message-modal-footer {
        padding: 16px;
        flex-direction: column;
        gap: 12px;
        position: sticky;
        bottom: 0;
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .btn-modal-reply,
    .btn-modal-delete {
        width: 100%;
        justify-content: center;
    }
}

/* Desktop enhancements */
@media (min-width: 992px) {
    .message-view-header {
        padding: 20px 30px;
        position: static;
        flex-wrap: nowrap;
    }
    
    .message-view-body {
        padding: 30px;
    }
    
    .msg-view-subject h2 {
        font-size: 1.8rem;
    }
    
    .btn-icon-msg:hover {
        background: var(--primary-color);
        color: white;
        transform: scale(1.05);
    }
}

/* ===================================
   GESTION MOBILE DE L'AFFICHAGE
   =================================== */

/* Par défaut (mobile), afficher UNIQUEMENT la sidebar */
.messages-sidebar {
    display: flex !important;
    width: 100%;
}

.messages-main {
    display: none !important;
    width: 100%;
}

/* Quand on ouvre un message sur mobile */
.messages-sidebar.mobile-hide {
    display: none !important;
}

.messages-main.mobile-show {
    display: flex !important;
}

.back-btn-mobile {
    display: flex !important;
    width: 48px;
    height: 48px;
    min-width: 48px;
    min-height: 48px;
    border-radius: 50%;
    background: rgba(200, 16, 46, 0.1);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--primary-color);
}

.back-btn-mobile:active {
    transform: scale(0.95);
    background: rgba(200, 16, 46, 0.2);
}

/* ===================================
   RESPONSIVE - DESKTOP (992px+)
   =================================== */

@media (min-width: 992px) {
    .messaging-section {
        padding: 30px 0 60px;
    }
    
    .container-fluid {
        max-width: 1400px;
        padding: 0 20px;
    }
    
    .messaging-wrapper {
        display: grid;
        grid-template-columns: 380px 1fr;
        border-radius: 20px;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
        min-height: calc(100vh - 180px);
    }
    
    .messages-sidebar {
        display: flex !important;
        width: auto;
        border-right: 1px solid #e9ecef;
        background: #fafbfc;
    }
    
    .messages-main {
        display: flex !important;
        width: auto;
    }
    
    .sidebar-header-msg {
        padding: 25px;
        background: white;
        position: static;
    }
    
    .sidebar-header-msg h2 {
        font-size: 1.4rem;
    }
    
    .msg-tabs {
        padding: 15px;
        position: static;
    }
    
    .search-msg {
        padding: 15px;
        position: static;
    }
    
    .message-item {
        gap: 12px;
        padding: 18px;
        min-height: auto;
    }
    
    .message-item:hover {
        background: #f8f9fa;
    }
    
    .msg-avatar img,
    .avatar-placeholder-msg {
        width: 50px;
        height: 50px;
    }
    
    .msg-sender {
        font-size: 1rem;
    }
    
    .msg-subject {
        font-size: 0.9rem;
    }
    
    .msg-preview {
        font-size: 0.85rem;
        -webkit-line-clamp: 1;
    }
    
    .btn-view-msg:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(200, 16, 46, 0.3);
    }
    
    .back-btn-mobile {
        display: none !important;
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
    document.body.style.overflow = 'hidden'; // Empêcher le scroll
    
    // Sur mobile (<992px), gérer l'affichage différemment
    if (window.innerWidth <= 992) {
        // Cacher la sidebar et afficher la main view
        const sidebar = document.querySelector('.messages-sidebar');
        const mainView = document.querySelector('.messages-main');
        
        if (sidebar && mainView) {
            sidebar.classList.add('mobile-hide');
            mainView.classList.add('mobile-show');
        }
        
        // Fermer le modal (on utilise la main view à la place)
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        // Charger le message dans la main view
        loadMessageInMainView(messageId);
    } else {
        // Sur desktop, utiliser le modal normalement
        loadMessageInModal(messageId);
    }
}

// Fermer le modal
function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    modal.style.display = 'none';
    document.body.style.overflow = ''; // Restaurer le scroll
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
    // Sujet
    document.getElementById('modalSubject').textContent = data.subject;
    
    // Avatar de l'expéditeur
    const avatarContainer = document.getElementById('modalSenderAvatar');
    if (data.sender_photo) {
        avatarContainer.innerHTML = `<img src="<?= BASE_URL ?>/${data.sender_photo}" alt="${data.sender_name}">`;
    } else {
        const initial = data.sender_name.charAt(0).toUpperCase();
        avatarContainer.innerHTML = `<div class="avatar-placeholder-modal">${initial}</div>`;
    }
    
    // Infos expéditeur
    document.getElementById('modalSenderName').textContent = data.sender_name;
    document.getElementById('modalSenderEmail').textContent = data.sender_email;
    document.getElementById('modalDate').textContent = data.created_at;
    
    // Contenu du message
    document.getElementById('modalContent').innerHTML = data.body.replace(/\n/g, '<br>');
    
    // Gérer les actions si c'est une demande de projet
    const modalActions = document.getElementById('modalActions');
    if (data.action_type === 'project_join_request' && data.action_status === 'pending') {
        const actionData = JSON.parse(data.action_data || '{}');
        document.getElementById('modalActionDescription').textContent = 
            `${data.sender_name} souhaite rejoindre votre projet. Voulez-vous accepter cette demande ?`;
        
        // Configurer les boutons
        const btnAccept = document.getElementById('btnModalAccept');
        const btnReject = document.getElementById('btnModalReject');
        
        btnAccept.onclick = () => handleModalAction('accept', actionData, data.id);
        btnReject.onclick = () => handleModalAction('reject', actionData, data.id);
        
        modalActions.style.display = 'block';
    } else if (data.action_type && data.action_status !== 'pending') {
        // Afficher le statut final
        modalActions.innerHTML = `
            <div class="modal-action-card" style="background: ${data.action_status === 'accepted' ? '#d4edda' : '#f8d7da'}; border-color: ${data.action_status === 'accepted' ? '#28a745' : '#dc3545'};">
                <h4><i class="fas fa-${data.action_status === 'accepted' ? 'check-circle' : 'times-circle'}" style="color: ${data.action_status === 'accepted' ? '#28a745' : '#dc3545'}"></i></h4>
                <p style="margin: 0; font-weight: 600;">Cette demande a été ${data.action_status === 'accepted' ? 'acceptée' : 'refusée'}.</p>
            </div>
        `;
        modalActions.style.display = 'block';
    } else {
        modalActions.style.display = 'none';
    }
}

// Gérer les actions (accepter/refuser)
function handleModalAction(action, actionData, messageId) {
    const endpoint = action === 'accept' ? 'acceptJoinRequest' : 'rejectJoinRequest';
    const confirmMsg = action === 'accept' ? 
        'Voulez-vous accepter ce membre dans votre projet ?' : 
        'Voulez-vous refuser cette demande ?';
    
    if (!confirm(confirmMsg)) return;
    
    let reason = '';
    if (action === 'reject') {
        reason = prompt('Raison du refus (optionnel):') || '';
    }
    
    const formData = new FormData();
    formData.append('project_id', actionData.project_id);
    formData.append('user_id', actionData.user_id);
    if (reason) formData.append('reason', reason);
    
    fetch(`<?= BASE_URL ?>/project/${endpoint}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le statut du message
            fetch(`<?= BASE_URL ?>/message/updateActionStatus/${messageId}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({status: action === 'accept' ? 'accepted' : 'rejected'})
            });
            
            // Afficher le succès dans le modal
            const modalActions = document.getElementById('modalActions');
            const color = action === 'accept' ? '#28a745' : '#dc3545';
            const bgColor = action === 'accept' ? '#d4edda' : '#f8d7da';
            modalActions.innerHTML = `
                <div class="modal-action-card" style="background: ${bgColor}; border-color: ${color};">
                    <h4><i class="fas fa-${action === 'accept' ? 'check-circle' : 'times-circle'}" style="color: ${color}"></i></h4>
                    <p style="margin: 0; font-weight: 600;">Demande ${action === 'accept' ? 'acceptée' : 'refusée'} avec succès!</p>
                </div>
            `;
            
            // Notification
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'Erreur', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du traitement de la demande', 'error');
    });
}

// Répondre au message
function replyToMessage() {
    if (!currentModalMessageData) return;
    
    console.log('📧 Réponse au message:', currentModalMessageData);
    
    // Préparer le sujet de la réponse
    let replySubject = currentModalMessageData.subject || 'Votre message';
    if (!replySubject.startsWith('Re:')) {
        replySubject = 'Re: ' + replySubject;
    }
    
    // Créer l'URL avec les paramètres
    const params = new URLSearchParams({
        receiver: currentModalMessageData.sender_id,
        subject: replySubject,
        reply_to: currentModalMessageData.id
    });
    
    closeMessageModal();
    
    // Rediriger vers la page compose avec les paramètres
    window.location.href = `<?= BASE_URL ?>/message/compose?${params.toString()}`;
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

// Fonctions utilitaires
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        padding: 15px 25px;
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        border: 2px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        animation: slideInRight 0.3s ease;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 4000);
}

function openNewMessage() {
    window.location.href = '<?= BASE_URL ?>/message/compose';
}

function closeMobileView() {
    // Sur mobile, revenir à la liste des messages
    const sidebar = document.querySelector('.messages-sidebar');
    const mainView = document.querySelector('.messages-main');
    
    if (sidebar && mainView) {
        sidebar.classList.remove('mobile-hide');
        mainView.classList.remove('mobile-show');
    }
    
    // Cacher la vue du message
    document.getElementById('messageView').style.display = 'none';
    document.getElementById('messagePlaceholder').style.display = 'flex';
    
    // Retirer l'état actif de tous les messages
    document.querySelectorAll('.message-item').forEach(item => {
        item.classList.remove('active');
    });
    
    currentMessageId = null;
}

// Charger le message dans la vue principale (pour mobile)
function loadMessageInMainView(messageId) {
    fetch(`<?= BASE_URL ?>/message/show/${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cacher le placeholder
                document.getElementById('messagePlaceholder').style.display = 'none';
                
                // Afficher la vue du message
                const messageView = document.getElementById('messageView');
                messageView.style.display = 'flex';
                
                // Remplir les données
                document.getElementById('viewSenderName').textContent = data.sender_name;
                document.getElementById('viewSenderEmail').textContent = data.sender_email || '';
                document.getElementById('viewSubject').textContent = data.subject;
                document.getElementById('viewDate').textContent = data.created_at;
                document.getElementById('viewContent').innerHTML = data.body.replace(/\n/g, '<br>');
                
                // Avatar
                const senderPhoto = document.getElementById('viewSenderPhoto');
                if (data.sender_photo) {
                    senderPhoto.src = `<?= BASE_URL ?>/${data.sender_photo}`;
                    senderPhoto.alt = data.sender_name;
                } else {
                    senderPhoto.src = '';
                    senderPhoto.alt = '';
                }
                
                // Marquer le message comme actif
                document.querySelectorAll('.message-item').forEach(item => {
                    item.classList.remove('active');
                    if (parseInt(item.dataset.messageId) === parseInt(messageId)) {
                        item.classList.add('active');
                    }
                });
                
                // Gérer les actions si nécessaire
                const messageActions = document.getElementById('messageActions');
                if (data.action_type === 'project_join_request' && data.action_status === 'pending') {
                    // Configuration des actions...
                    messageActions.style.display = 'block';
                } else {
                    messageActions.style.display = 'none';
                }
                
                // Stocker les données pour la réponse
                currentModalMessageData = data;
            } else {
                alert('Erreur: ' + (data.message || 'Impossible de charger le message'));
                closeMobileView();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du message');
            closeMobileView();
        });
}

// Répondre au message depuis la vue principale
function replyMessage() {
    if (!currentModalMessageData) {
        alert('Aucun message sélectionné');
        return;
    }
    
    // Préparer le sujet de la réponse
    let replySubject = currentModalMessageData.subject || 'Votre message';
    if (!replySubject.startsWith('Re:')) {
        replySubject = 'Re: ' + replySubject;
    }
    
    // Créer l'URL avec les paramètres
    const params = new URLSearchParams({
        receiver: currentModalMessageData.sender_id,
        subject: replySubject,
        reply_to: currentModalMessageData.id
    });
    
    // Rediriger vers la page compose avec les paramètres
    window.location.href = `<?= BASE_URL ?>/message/compose?${params.toString()}`;
}

// Supprimer le message depuis la vue principale
function deleteMessage() {
    if (!currentMessageId) {
        alert('Aucun message sélectionné');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce message ?')) {
        return;
    }
    
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

// Helper function
function timeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'À l\'instant';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' min';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' h';
    if (seconds < 2592000) return Math.floor(seconds / 86400) + ' j';
    if (seconds < 31536000) return Math.floor(seconds / 2592000) + ' mois';
    return Math.floor(seconds / 31536000) + ' an' + (Math.floor(seconds / 31536000) > 1 ? 's' : '');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; 
?>
