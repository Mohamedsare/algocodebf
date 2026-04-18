<?php
$pageTitle = ($post['title'] ?? 'Discussion') . ' - Forum AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="discussion-section">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-nav">
            <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= BASE_URL ?>/forum/index"><i class="fas fa-comments"></i> Forum</a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($post['category']) ?></span>
        </div>

        <div class="discussion-layout">
            <!-- Main Discussion -->
            <div class="discussion-main">
                <!-- Post Card -->
                <article class="post-card">
                    <div class="post-header">
                        <div class="post-meta">
                            <span class="category-badge">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($post['category']) ?>
                            </span>
                            <span class="post-time">
                                <i class="fas fa-clock"></i> <?= timeAgo($post['created_at']) ?>
                            </span>
                            <span class="post-views">
                                <i class="fas fa-eye"></i> <?= $post['views'] ?? 0 ?> vues
                            </span>
                        </div>
                    </div>

                    <h1 class="post-title"><?= cleanAndSecure($post['title']) ?></h1>

                    <div class="post-author-section">
                        <div class="author-info">
                            <a href="<?= BASE_URL ?>/user/profile/<?= $post['author_id'] ?>" class="author-avatar">
                                <?php if (!empty($post['photo_path'])): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['photo_path']) ?>" 
                                         alt="<?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <?= strtoupper(substr($post['prenom'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="author-details">
                                <a href="<?= BASE_URL ?>/user/profile/<?= $post['author_id'] ?>" class="author-name">
                                    <?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?>
                                </a>
                                <span class="author-university"><?= htmlspecialchars($post['university'] ?? 'Étudiant') ?></span>
                            </div>
                        </div>

                        <div class="post-actions">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                                <a href="<?= BASE_URL ?>/forum/edit/<?= $post['id'] ?>" class="btn-action" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn-action" onclick="reportPost(<?= $post['id'] ?>)" title="Signaler">
                                    <i class="fas fa-flag"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="post-body">
                        <?= nl2br(cleanAndSecure($post['body'])) ?>
                    </div>

                    <!-- Pièces jointes -->
                    <?php if (!empty($attachments)): ?>
                        <div class="attachments-section">
                            <h3><i class="fas fa-paperclip"></i> Pièces jointes</h3>
                            <div class="attachments-grid">
                                <?php foreach ($attachments as $attachment): ?>
                                    <?php
                                    $fileIcon = 'fa-file';
                                    $iconColor = '#6c757d';
                                    
                                    if (strpos($attachment['mime_type'], 'pdf') !== false) {
                                        $fileIcon = 'fa-file-pdf';
                                        $iconColor = '#e74c3c';
                                    } elseif (strpos($attachment['mime_type'], 'image') !== false) {
                                        $fileIcon = 'fa-file-image';
                                        $iconColor = '#3498db';
                                    } elseif (strpos($attachment['mime_type'], 'word') !== false) {
                                        $fileIcon = 'fa-file-word';
                                        $iconColor = '#2980b9';
                                    } elseif (strpos($attachment['mime_type'], 'zip') !== false) {
                                        $fileIcon = 'fa-file-archive';
                                        $iconColor = '#f39c12';
                                    }
                                    
                                    $fileSize = $attachment['file_size'];
                                    $sizeFormatted = $fileSize < 1024 ? $fileSize . ' B' : 
                                                    ($fileSize < 1048576 ? round($fileSize / 1024, 2) . ' KB' : 
                                                    round($fileSize / 1048576, 2) . ' MB');
                                    ?>
                                    
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($attachment['file_path']) ?>" 
                                       class="attachment-card" 
                                       download="<?= htmlspecialchars($attachment['original_name']) ?>"
                                       target="_blank">
                                        <div class="attachment-icon" style="color: <?= $iconColor ?>">
                                            <i class="fas <?= $fileIcon ?> fa-3x"></i>
                                        </div>
                                        <div class="attachment-info">
                                            <h4><?= htmlspecialchars($attachment['original_name']) ?></h4>
                                            <p><?= $sizeFormatted ?></p>
                                        </div>
                                        <div class="attachment-download">
                                            <i class="fas fa-download"></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="post-footer">
                        <div class="post-stats">
                            <button class="btn-like <?= $has_liked ? 'liked' : '' ?>" 
                                    onclick="toggleLike('post', <?= $post['id'] ?>)">
                                <i class="<?= $has_liked ? 'fas' : 'far' ?> fa-heart"></i>
                                <span id="likes-count-post-<?= $post['id'] ?>"><?= $post['likes_count'] ?? 0 ?></span>
                            </button>
                            <span class="stat-item">
                                <i class="fas fa-comments"></i> <?= $post['comments_count'] ?? 0 ?> réponses
                            </span>
                        </div>

                        <div class="post-share">
                            <button class="btn-share" onclick="sharePost()">
                                <i class="fas fa-share-alt"></i> Partager
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h2 class="comments-title">
                        <i class="fas fa-comments"></i> 
                        <?= count($comments) ?> Réponse<?= count($comments) > 1 ? 's' : '' ?>
                    </h2>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Add Comment Form -->
                        <div class="add-comment-box">
                            <form action="<?= BASE_URL ?>/forum/addComment/<?= $post['id'] ?>" method="POST" class="comment-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                
                                <div class="comment-avatar">
                                    <?php if (!empty($_SESSION['user_photo'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['user_photo']) ?>" alt="Vous">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="comment-input-wrapper">
                                    <textarea name="body" 
                                              class="comment-textarea" 
                                              placeholder="Ajouter une réponse..." 
                                              rows="3" 
                                              required></textarea>
                                    <div class="comment-actions">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Publier
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-lock"></i>
                            <p>
                                <a href="<?= BASE_URL ?>/auth/login">Connectez-vous</a> pour répondre à cette discussion
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                            <div class="no-comments">
                                <i class="fas fa-comment-slash"></i>
                                <p>Aucune réponse pour le moment.</p>
                                <p>Soyez le premier à répondre !</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item" id="comment-<?= $comment['id'] ?>">
                                    <div class="comment-avatar">
                                        <a href="<?= BASE_URL ?>/user/profile/<?= $comment['author_id'] ?>">
                                            <?php if (!empty($comment['photo_path'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($comment['photo_path']) ?>" 
                                                     alt="<?= cleanAndSecure($comment['prenom'] . ' ' . $comment['nom']) ?>">
                                            <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?= strtoupper(substr($comment['prenom'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    </div>

                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <a href="<?= BASE_URL ?>/user/profile/<?= $comment['author_id'] ?>" class="comment-author">
                                                <?= cleanAndSecure($comment['prenom'] . ' ' . $comment['nom']) ?>
                                            </a>
                                            <span class="comment-time">
                                                <i class="fas fa-clock"></i> <?= timeAgo($comment['created_at']) ?>
                                            </span>
                                        </div>

                                        <div class="comment-body">
                                            <?= nl2br(cleanAndSecure($comment['body'])) ?>
                                        </div>

                                        <div class="comment-footer">
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <button class="btn-like-comment" 
                                                        onclick="toggleLike('comment', <?= $comment['id'] ?>)">
                                                    <i class="far fa-heart"></i>
                                                    <span id="likes-count-comment-<?= $comment['id'] ?>">
                                                        <?= $comment['likes_count'] ?? 0 ?>
                                                    </span>
                                                </button>
                                                <button class="btn-reply" onclick="replyToComment(<?= $comment['id'] ?>)">
                                                    <i class="fas fa-reply"></i> Répondre
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="discussion-sidebar">
                <!-- Quick Stats -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-chart-bar"></i> Statistiques</h3>
                    <div class="stats-list">
                        <div class="stat-row">
                            <span class="stat-label"><i class="fas fa-eye"></i> Vues</span>
                            <span class="stat-value"><?= $post['views'] ?? 0 ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><i class="fas fa-heart"></i> Likes</span>
                            <span class="stat-value"><?= $post['likes_count'] ?? 0 ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><i class="fas fa-comments"></i> Réponses</span>
                            <span class="stat-value"><?= $post['comments_count'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- Author Card -->
                <div class="sidebar-card author-card">
                    <h3><i class="fas fa-user"></i> Auteur</h3>
                    <div class="author-profile">
                        <a href="<?= BASE_URL ?>/user/profile/<?= $post['author_id'] ?>">
                            <?php if (!empty($post['photo_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($post['photo_path']) ?>" 
                                     alt="<?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?>"
                                     class="author-photo">
                            <?php else: ?>
                                <div class="avatar-placeholder-large">
                                    <?= strtoupper(substr($post['prenom'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </a>
                        <a href="<?= BASE_URL ?>/user/profile/<?= $post['author_id'] ?>" class="author-name-link">
                            <?= cleanAndSecure($post['prenom'] . ' ' . $post['nom']) ?>
                        </a>
                        <p class="author-bio"><?= htmlspecialchars($post['university'] ?? '') ?></p>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $post['author_id']): ?>
                            <a href="<?= BASE_URL ?>/message/compose/<?= $post['author_id'] ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-envelope"></i> Envoyer un message
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Related Discussions -->
                <div class="sidebar-card">
                    <h3><i class="fas fa-list"></i> Discussions similaires</h3>
                    <div class="related-posts">
                        <p class="text-muted">Bientôt disponible...</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.discussion-section {
    padding: 40px 0 80px;
    background: #f8f9fa;
}

/* Breadcrumb */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 30px;
    padding: 15px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.breadcrumb-nav a {
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.breadcrumb-nav a:hover {
    color: var(--secondary-color);
}

.breadcrumb-nav i.fa-chevron-right {
    color: #6c757d;
    font-size: 0.8rem;
}

/* Layout */
.discussion-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

/* Post Card */
.post-card {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.post-header {
    margin-bottom: 20px;
}

.post-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 15px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.post-time, .post-views {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6c757d;
    font-size: 0.9rem;
}

.post-title {
    font-size: 2rem;
    margin: 20px 0 25px;
    color: var(--dark-color);
    line-height: 1.3;
}

.post-author-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-top: 2px solid #f0f0f0;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 30px;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.author-avatar img,
.avatar-placeholder {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-placeholder {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}

.author-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.author-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--dark-color);
    text-decoration: none;
}

.author-name:hover {
    color: var(--primary-color);
}

.author-university {
    color: #6c757d;
    font-size: 0.9rem;
}

.post-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-action:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.post-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--dark-color);
    margin-bottom: 30px;
}

.post-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

.post-stats {
    display: flex;
    gap: 20px;
    align-items: center;
}

.btn-like {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    color: var(--dark-color);
    transition: all 0.3s ease;
}

.btn-like:hover {
    background: rgba(231, 76, 60, 0.1);
    border-color: #e74c3c;
    color: #e74c3c;
}

.btn-like.liked {
    background: rgba(231, 76, 60, 0.1);
    border-color: #e74c3c;
    color: #e74c3c;
}

.btn-like i {
    font-size: 1.2rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6c757d;
}

.btn-share {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-share:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

/* Comments Section */
.comments-section {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
}

.comments-title {
    margin: 0 0 30px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Add Comment */
.add-comment-box {
    margin-bottom: 40px;
}

.comment-form {
    display: flex;
    gap: 15px;
}

.comment-avatar img,
.comment-avatar .avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.comment-input-wrapper {
    flex: 1;
}

.comment-textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    resize: vertical;
    font-family: inherit;
    margin-bottom: 10px;
}

.comment-textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.comment-actions {
    display: flex;
    justify-content: flex-end;
}

/* Login Prompt */
.login-prompt {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-radius: 15px;
    margin-bottom: 30px;
}

.login-prompt i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
}

.login-prompt a {
    color: var(--primary-color);
    font-weight: 600;
}

/* Comments List */
.no-comments {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-comments i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.3;
}

.comment-item {
    display: flex;
    gap: 15px;
    padding: 25px 0;
    border-bottom: 1px solid #f0f0f0;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.comment-author {
    font-weight: 600;
    color: var(--dark-color);
    text-decoration: none;
}

.comment-author:hover {
    color: var(--primary-color);
}

.comment-time {
    color: #6c757d;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.comment-body {
    margin-bottom: 12px;
    line-height: 1.6;
    color: var(--dark-color);
}

.comment-footer {
    display: flex;
    gap: 15px;
}

.btn-like-comment, .btn-reply {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 5px 10px;
    border-radius: 6px;
}

.btn-like-comment:hover {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.btn-reply:hover {
    background: rgba(52, 152, 219, 0.1);
    color: var(--primary-color);
}

/* Sidebar */
.discussion-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.sidebar-card h3 {
    margin: 0 0 20px;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
}

.stat-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-weight: 600;
}

.stat-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Author Card */
.author-profile {
    text-align: center;
}

.author-photo,
.avatar-placeholder-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 15px;
    display: block;
}

.avatar-placeholder-large {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 700;
}

.author-name-link {
    display: block;
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 8px;
    color: var(--dark-color);
    text-decoration: none;
}

.author-name-link:hover {
    color: var(--primary-color);
}

.author-bio {
    color: #6c757d;
    margin-bottom: 20px;
}

/* Attachments Section */
.attachments-section {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 2px solid #f0f0f0;
}

.attachments-section h3 {
    margin: 0 0 20px;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--dark-color);
}

.attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.attachment-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border: 2px solid #e9ecef;
    border-radius: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.attachment-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.attachment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: var(--primary-color);
}

.attachment-card:hover::before {
    transform: scaleY(1);
}

.attachment-icon {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.attachment-card:hover .attachment-icon {
    transform: scale(1.1);
}

.attachment-info {
    flex: 1;
    min-width: 0;
}

.attachment-info h4 {
    margin: 0 0 5px;
    font-size: 1rem;
    color: var(--dark-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attachment-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
}

.attachment-download {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.attachment-card:hover .attachment-download {
    transform: rotate(360deg);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

/* Responsive */
@media (max-width: 992px) {
    .discussion-layout {
        grid-template-columns: 1fr;
    }
    
    .discussion-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .post-card, .comments-section {
        padding: 20px;
    }
    
    .post-title {
        font-size: 1.5rem;
    }
    
    .comment-form {
        flex-direction: column;
    }
}
</style>

<script>
function toggleLike(type, id) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        window.location.href = '<?= BASE_URL ?>/auth/login';
        return;
    <?php else: ?>
    fetch('<?= BASE_URL ?>/forum/toggleLike', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `type=${type}&id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
    <?php endif; ?>
}

function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: <?= json_encode($post['title']) ?>,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Lien copié dans le presse-papier !');
    }
}

function reportPost(postId) {
    if (confirm('Voulez-vous vraiment signaler ce post ?')) {
        const reason = prompt('Raison du signalement :');
        if (reason) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= BASE_URL ?>/forum/report';
            
            const fields = {
                type: 'post',
                id: postId,
                reason: reason,
                csrf_token: '<?= $csrf_token ?>'
            };
            
            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

function replyToComment(commentId) {
    const textarea = document.querySelector('.comment-textarea');
    textarea.focus();
    textarea.scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

