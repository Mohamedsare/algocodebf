<?php
$pageTitle = 'Membres - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Hero Section -->
<section class="members-hero">
    <div class="container">
        <div class="hero-content">
            <h1><i class="fas fa-users"></i> Communauté AlgoCodeBF</h1>
            <p>Découvrez et connectez-vous avec les informaticiens du Burkina Faso</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="members-stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_members'] ?? 0 ?></h3>
                    <p>Membres Actifs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['universities'] ?? 0 ?></h3>
                    <p>Universités</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-code"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['skills_count'] ?? 0 ?></h3>
                    <p>Compétences</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['new_this_month'] ?? 0 ?></h3>
                    <p>Nouveaux ce Mois</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search & Filter Section -->
<section class="members-content-section">
    <div class="container">
        <div class="search-filter-panel">
            <div class="search-bar-large">
                <i class="fas fa-search"></i>
                <input type="text" id="searchMembers"
                    placeholder="Rechercher un membre par nom, compétence, université..." autocomplete="off">
            </div>

            <div class="filters-row">
                <div class="filter-item">
                    <label><i class="fas fa-university"></i> Université</label>
                    <select id="universityFilter">
                        <option value="">Toutes</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>

                <div class="filter-item">
                    <label><i class="fas fa-map-marker-alt"></i> Ville</label>
                    <select id="cityFilter">
                        <option value="">Toutes</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>

                <div class="filter-item">
                    <label><i class="fas fa-code"></i> Compétence</label>
                    <select id="skillFilter">
                        <option value="">Toutes</option>
                        <!-- Options chargées dynamiquement -->
                    </select>
                </div>

                <div class="filter-item">
                    <label><i class="fas fa-sort"></i> Trier par</label>
                    <select id="sortFilter">
                        <option value="recent">Plus récents</option>
                        <option value="name">Nom (A-Z)</option>
                        <option value="contributions">Plus actifs</option>
                        <option value="reputation">Meilleure réputation</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="loading-indicator" id="loadingIndicator" style="display: none;">
            <div class="spinner"></div>
            <p>Chargement des membres...</p>
        </div>

        <!-- Members Grid -->
        <div class="members-grid" id="membersGrid">
            <!-- Contenu chargé dynamiquement -->
        </div>

        <!-- Pagination -->
        <div class="pagination" id="paginationContainer" style="display: none;">
            <!-- Pagination chargée dynamiquement -->
        </div>

    </div>
</section>

<style>
.members-hero {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.members-hero::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.members-stats-section {
    padding: 60px 0;
    background: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.stat-card {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    padding: 30px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info h3 {
    font-size: 2rem;
    margin: 0;
    color: var(--dark-color);
}

.stat-info p {
    margin: 5px 0 0;
    color: #6c757d;
}

.members-content-section {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

.search-filter-panel {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 40px;
}

.search-bar-large {
    position: relative;
    margin-bottom: 25px;
}

.search-bar-large i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1.2rem;
}

.search-bar-large input {
    width: 100%;
    padding: 15px 20px 15px 55px;
    border: 2px solid #e9ecef;
    border-radius: 30px;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.search-bar-large input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.filter-item label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
}

.filter-item select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-item select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.member-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
}

.member-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.member-banner {
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    position: relative;
}

.online-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--secondary-color);
    display: flex;
    align-items: center;
    gap: 5px;
}

.online-badge i {
    animation: pulse 2s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.5;
    }
}

.member-avatar {
    width: 100px;
    height: 100px;
    margin: -50px auto 0;
    position: relative;
}

.member-avatar img,
.avatar-placeholder-large {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
}

.avatar-placeholder-large {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
}

.member-badge-icon {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 30px;
    height: 30px;
    background: #ffd700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 3px solid white;
    font-size: 0.9rem;
}

.member-info {
    padding: 20px;
    text-align: center;
}

.member-name {
    margin: 10px 0 5px;
    font-size: 1.2rem;
}

.member-name a {
    color: var(--dark-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.member-name a:hover {
    color: var(--primary-color);
}

.member-title {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.member-location {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.member-skills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin: 15px 0;
}

.skill-tag {
    padding: 5px 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
}

.skill-tag.more {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

.member-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin: 20px 0;
    padding: 15px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-item-member {
    text-align: center;
}

.stat-item-member strong {
    display: block;
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 3px;
}

.stat-item-member span {
    font-size: 0.75rem;
    color: #6c757d;
}

.member-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-view-profile {
    flex: 1;
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-view-profile:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn-contact {
    width: 45px;
    height: 45px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    transition: all 0.3s ease;
}

.btn-contact:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.no-members {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.no-members i {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 50px;
    flex-wrap: wrap;
}

.page-link {
    padding: 10px 15px;
    border: 2px solid #e9ecef;
    background: white;
    border-radius: 8px;
    text-decoration: none;
    color: var(--dark-color);
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-link:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.page-link.active {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-color: transparent;
}

/* Loading Indicator */
.loading-indicator {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border-radius: 15px;
    margin-bottom: 30px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.loading-indicator p {
    color: #6c757d;
    margin: 0;
    font-size: 1rem;
}

/* No Results */
.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
}

.no-results i {
    font-size: 4rem;
    color: #e9ecef;
    margin-bottom: 20px;
}

.no-results h3 {
    color: var(--dark-color);
    margin-bottom: 10px;
}

.no-results p {
    color: #6c757d;
    margin-bottom: 20px;
}

/* Filter Loading States */
.filter-item select.loading {
    opacity: 0.6;
    pointer-events: none;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }

    .members-grid {
        grid-template-columns: 1fr;
    }

    .filters-row {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .stat-card {
        padding: 20px 16px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }

    .stat-info h3 {
        font-size: 1.5rem;
    }

    .stat-info p {
        font-size: 0.85rem;
    }
}
</style>

<script>
// Variables globales
let currentPage = 1;
let isLoading = false;
let searchTimeout;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadFilterOptions();
    loadMembers();
    setupEventListeners();
});

// Configuration des événements
function setupEventListeners() {
    // Recherche avec debounce
    document.getElementById('searchMembers').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadMembers();
        }, 500);
    });

    // Filtres
    document.getElementById('universityFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers();
    });

    document.getElementById('cityFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers();
    });

    document.getElementById('skillFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers();
    });

    document.getElementById('sortFilter').addEventListener('change', function() {
        currentPage = 1;
        loadMembers();
    });
}

// Charger les options de filtres
async function loadFilterOptions() {
    try {
        const response = await fetch('<?= BASE_URL ?>/user/getFilterOptions', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            populateSelect('universityFilter', data.universities);
            populateSelect('cityFilter', data.cities);
            populateSelect('skillFilter', data.skills);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des options de filtres:', error);
    }
}

// Peupler un select avec des options
function populateSelect(selectId, options) {
    const select = document.getElementById(selectId);

    // Garder la première option (Toutes)
    const firstOption = select.firstElementChild;
    select.innerHTML = '';
    select.appendChild(firstOption);

    // Ajouter les nouvelles options
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option;
        optionElement.textContent = option;
        select.appendChild(optionElement);
    });
}

// Charger les membres avec filtres
async function loadMembers() {
    if (isLoading) return;

    isLoading = true;
    showLoading();

    try {
        const params = new URLSearchParams({
            search: document.getElementById('searchMembers').value,
            university: document.getElementById('universityFilter').value,
            city: document.getElementById('cityFilter').value,
            skill: document.getElementById('skillFilter').value,
            sort: document.getElementById('sortFilter').value,
            page: currentPage
        });

        const response = await fetch(`<?= BASE_URL ?>/user/filterMembers?${params}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            displayMembers(data.members);
            displayPagination(data.pagination);
        } else {
            showError('Erreur lors du chargement des membres: ' + (data.error || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Erreur lors du chargement des membres:', error);
        showError('Erreur de connexion. Veuillez réessayer.');
    } finally {
        isLoading = false;
        hideLoading();
    }
}

// Afficher les membres
function displayMembers(members) {
    const grid = document.getElementById('membersGrid');

    if (members.length === 0) {
        grid.innerHTML = `
            <div class="no-results">
                <i class="fas fa-users"></i>
                <h3>Aucun membre trouvé</h3>
                <p>Aucun membre ne correspond à vos critères de recherche.</p>
                <button onclick="clearFilters()" class="btn btn-primary">
                    <i class="fas fa-times"></i> Effacer les filtres
                </button>
            </div>
        `;
        return;
    }

    grid.innerHTML = members.map(member => createMemberCard(member)).join('');
}

// Créer une carte de membre
function createMemberCard(member) {
    const skills = member.top_skills || [];
    const skillsHtml = skills.slice(0, 3).map(skill =>
        `<span class="skill-tag">${escapeHtml(skill)}</span>`
    ).join('');

    const moreSkills = skills.length > 3 ?
        `<span class="skill-tag more">+${skills.length - 3}</span>` : '';

    const badgesHtml = member.badges && member.badges.length > 0 ?
        `<div class="member-badge-icon" title="${escapeHtml(member.badges[0].name)}">
            <i class="fas fa-certificate"></i>
        </div>` : '';

    const onlineBadge = member.is_online ?
        `<span class="online-badge"><i class="fas fa-circle"></i> En ligne</span>` : '';

    const photoHtml = member.photo ?
        `<img src="${escapeHtml('<?= BASE_URL ?>/' + member.photo)}" alt="${escapeHtml(member.name)}">` :
        `<div class="avatar-placeholder-large">${escapeHtml(member.name.charAt(0).toUpperCase())}</div>`;

    return `
        <div class="member-card">
            <div class="member-banner">
                ${onlineBadge}
            </div>
            
            <div class="member-avatar">
                ${photoHtml}
                ${badgesHtml}
            </div>
            
            <div class="member-info">
                <h3 class="member-name">
                    <a href="${escapeHtml('<?= BASE_URL ?>/user/profile/' + member.id)}">
                        ${escapeHtml(member.name)}
                    </a>
                </h3>
                
                <p class="member-title">
                    ${escapeHtml(member.faculty || 'Membre')}
                </p>
                
                <div class="member-location">
                    <i class="fas fa-university"></i>
                    <span>${escapeHtml(member.university || 'Non spécifié')}</span>
                </div>
                
                <div class="member-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${escapeHtml(member.city || 'Non spécifié')}</span>
                </div>
                
                <div class="member-skills">
                    ${skillsHtml}
                    ${moreSkills}
                </div>
                
                <div class="member-stats">
                    <div class="stat-item-member">
                        <strong>${member.posts_count || 0}</strong>
                        <span>Posts</span>
                    </div>
                    <div class="stat-item-member">
                        <strong>${member.projects_count || 0}</strong>
                        <span>Projets</span>
                    </div>
                    <div class="stat-item-member">
                        <strong>${member.reputation || 0}</strong>
                        <span>Points</span>
                    </div>
                </div>
                
                <div class="member-actions">
                    <a href="${escapeHtml('<?= BASE_URL ?>/user/profile/' + member.id)}" 
                       class="btn-view-profile">
                        <i class="fas fa-eye"></i> Voir le Profil
                    </a>
                    <button class="btn-contact" onclick="contactMember(${member.id})">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Afficher la pagination
function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');

    if (pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';

    let paginationHtml = '';

    // Bouton précédent
    if (pagination.current_page > 1) {
        paginationHtml += `
            <button class="page-link" onclick="changePage(${pagination.current_page - 1})">
                <i class="fas fa-chevron-left"></i> Précédent
            </button>
        `;
    }

    // Pages
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

    if (startPage > 1) {
        paginationHtml += `<button class="page-link" onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            paginationHtml += `<span class="page-ellipsis">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        paginationHtml += `
            <button class="page-link ${activeClass}" onclick="changePage(${i})">
                ${i}
            </button>
        `;
    }

    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            paginationHtml += `<span class="page-ellipsis">...</span>`;
        }
        paginationHtml +=
            `<button class="page-link" onclick="changePage(${pagination.total_pages})">${pagination.total_pages}</button>`;
    }

    // Bouton suivant
    if (pagination.current_page < pagination.total_pages) {
        paginationHtml += `
            <button class="page-link" onclick="changePage(${pagination.current_page + 1})">
                Suivant <i class="fas fa-chevron-right"></i>
            </button>
        `;
    }

    container.innerHTML = paginationHtml;
}

// Changer de page
function changePage(page) {
    currentPage = page;
    loadMembers();

    // Scroll vers le haut de la grille
    document.getElementById('membersGrid').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Effacer tous les filtres
function clearFilters() {
    document.getElementById('searchMembers').value = '';
    document.getElementById('universityFilter').value = '';
    document.getElementById('cityFilter').value = '';
    document.getElementById('skillFilter').value = '';
    document.getElementById('sortFilter').value = 'recent';

    currentPage = 1;
    loadMembers();
}

// Afficher le loading
function showLoading() {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('membersGrid').style.opacity = '0.5';
}

// Masquer le loading
function hideLoading() {
    document.getElementById('loadingIndicator').style.display = 'none';
    document.getElementById('membersGrid').style.opacity = '1';
}

// Afficher une erreur
function showError(message) {
    const grid = document.getElementById('membersGrid');
    grid.innerHTML = `
        <div class="no-results">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Erreur</h3>
            <p>${escapeHtml(message)}</p>
            <button onclick="loadMembers()" class="btn btn-primary">
                <i class="fas fa-refresh"></i> Réessayer
            </button>
        </div>
    `;
}

// Échapper le HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Contacter un membre
function contactMember(userId) {
    window.location.href = `<?= BASE_URL ?>/message/compose?receiver=${userId}`;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>