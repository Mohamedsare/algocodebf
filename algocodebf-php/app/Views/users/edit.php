<?php
$pageTitle = 'Modifier mon profil - AlgoCodeBF';
require_once __DIR__ . '/../layouts/header.php';
?>

<section class="edit-profile-section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header-edit">
            <div class="header-content">
                <h1><i class="fas fa-user-edit"></i> Modifier mon Profil</h1>
                <p>Mettez à jour vos informations et personnalisez votre profil</p>
            </div>
            <a href="<?= BASE_URL ?>/user/profile" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour au profil
            </a>
        </div>

        <form action="<?= BASE_URL ?>/user/edit" method="POST" enctype="multipart/form-data" class="edit-profile-form">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
            
            <div class="edit-grid">
                <!-- Left Column - Photo & CV -->
                <div class="edit-sidebar">
                    <!-- Photo de profil -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-camera"></i>
                            <h3>Photo de Profil</h3>
                        </div>
                        <div class="card-body-edit">
                            <div class="photo-upload-area">
                                <div class="current-photo">
                                    <?php if (!empty($user['photo_path'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['photo_path'] ?? '') ?>" 
                                             alt="Photo actuelle" id="photoPreview">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-edit" id="photoPreview">
                                            <?= strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" 
                                       id="photoInput" 
                                       name="photo" 
                                       accept="image/*" 
                                       class="file-input-hidden"
                                       onchange="previewPhoto(this)">
                                <label for="photoInput" class="btn-upload">
                                    <i class="fas fa-upload"></i> Changer la photo
                                </label>
                                <small class="upload-hint">JPG, PNG ou GIF. Max 5MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- CV Upload -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-file-pdf"></i>
                            <h3>Curriculum Vitae</h3>
                        </div>
                        <div class="card-body-edit">
                            <?php if (!empty($user['cv_path'])): ?>
                                <div class="current-cv">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="cv-info">
                                        <strong>CV actuel</strong>
                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($user['cv_path'] ?? '') ?>" 
                                           target="_blank">
                                            Voir le CV
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" 
                                   id="cvInput" 
                                   name="cv" 
                                   accept=".pdf" 
                                   class="file-input-hidden">
                            <label for="cvInput" class="btn-upload">
                                <i class="fas fa-upload"></i> 
                                <?= !empty($user['cv_path']) ? 'Remplacer le CV' : 'Ajouter un CV' ?>
                            </label>
                            <small class="upload-hint">PDF uniquement. Max 2MB</small>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Informations -->
                <div class="edit-main">
                    <!-- Informations personnelles -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-user"></i>
                            <h3>Informations Personnelles</h3>
                        </div>
                        <div class="card-body-edit">
                            <div class="form-row-edit">
                                <div class="form-group-edit">
                                    <label for="prenom">Prénom *</label>
                                    <input type="text" 
                                           id="prenom" 
                                           name="prenom" 
                                           class="form-input-edit disabled-input"
                                           value="<?= htmlspecialchars($user['prenom'] ?? '') ?>"
                                           readonly>
                                    <small class="form-hint">Non modifiable</small>
                                </div>
                                <div class="form-group-edit">
                                    <label for="nom">Nom *</label>
                                    <input type="text" 
                                           id="nom" 
                                           name="nom" 
                                           class="form-input-edit disabled-input"
                                           value="<?= htmlspecialchars($user['nom'] ?? '') ?>"
                                           readonly>
                                    <small class="form-hint">Non modifiable</small>
                                </div>
                            </div>

                            <div class="form-group-edit">
                                <label for="email">Email *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-input-edit disabled-input"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       readonly>
                                <small class="form-hint">Non modifiable</small>
                            </div>

                            <div class="form-group-edit">
                                <label for="phone">Téléphone *</label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-input-edit disabled-input"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       readonly>
                                <small class="form-hint">Non modifiable</small>
                            </div>
                        </div>
                    </div>

                    <!-- Informations académiques -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Informations Académiques</h3>
                        </div>
                        <div class="card-body-edit">
                            <div class="form-group-edit">
                                <label for="university">Université / École *</label>
                                <input type="text" 
                                       id="university" 
                                       name="university" 
                                       class="form-input-edit"
                                       value="<?= htmlspecialchars($user['university'] ?? '') ?>"
                                       required>
                            </div>

                            <div class="form-group-edit">
                                <label for="faculty">Filière / Spécialité *</label>
                                <input type="text" 
                                       id="faculty" 
                                       name="faculty" 
                                       class="form-input-edit"
                                       value="<?= htmlspecialchars($user['faculty'] ?? '') ?>"
                                       required>
                            </div>

                            <div class="form-group-edit">
                                <label for="city">Ville *</label>
                                <select id="city" name="city" class="form-input-edit" required>
                                    <option value="">Sélectionnez une ville</option>
                                    <option value="Ouagadougou" <?= $user['city'] === 'Ouagadougou' ? 'selected' : '' ?>>Ouagadougou</option>
                                    <option value="Bobo-Dioulasso" <?= $user['city'] === 'Bobo-Dioulasso' ? 'selected' : '' ?>>Bobo-Dioulasso</option>
                                    <option value="Koudougou" <?= $user['city'] === 'Koudougou' ? 'selected' : '' ?>>Koudougou</option>
                                    <option value="Ouahigouya" <?= $user['city'] === 'Ouahigouya' ? 'selected' : '' ?>>Ouahigouya</option>
                                    <option value="Banfora" <?= $user['city'] === 'Banfora' ? 'selected' : '' ?>>Banfora</option>
                                    <option value="Dédougou" <?= $user['city'] === 'Dédougou' ? 'selected' : '' ?>>Dédougou</option>
                                    <option value="Kaya" <?= $user['city'] === 'Kaya' ? 'selected' : '' ?>>Kaya</option>
                                    <option value="Autre" <?= $user['city'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-quote-right"></i>
                            <h3>Biographie</h3>
                        </div>
                        <div class="card-body-edit">
                            <div class="form-group-edit">
                                <label for="bio">À propos de vous</label>
                                <textarea id="bio" 
                                          name="bio" 
                                          class="form-textarea-edit" 
                                          rows="6"
                                          maxlength="500"
                                          placeholder="Parlez de vous, vos passions, vos objectifs..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                <small class="form-hint" id="bioCounter">500 caractères restants</small>
                            </div>
                        </div>
                    </div>

                    <!-- Compétences -->
                    <div class="edit-card">
                        <div class="card-header-edit">
                            <i class="fas fa-code"></i>
                            <h3>Mes Compétences</h3>
                        </div>
                        <div class="card-body-edit">
                            <p class="info-text">
                                <i class="fas fa-info-circle"></i>
                                Sélectionnez vos compétences et indiquez votre niveau pour chacune
                            </p>
                            <div class="skills-selector">
                                <?php if (!empty($all_skills)): ?>
                                    <?php
                                    $userSkillIds = [];
                                    foreach ($user_skills ?? [] as $us) {
                                        if (isset($us['skill_id'])) {
                                            $userSkillIds[] = $us['skill_id'];
                                        }
                                    }
                                    $categories = array_unique(array_column($all_skills, 'category'));
                                    ?>
                                    
                                    <?php foreach ($categories as $category): ?>
                                        <div class="skill-category-group">
                                            <h4 class="category-title">
                                                <i class="fas fa-folder"></i> <?= htmlspecialchars($category) ?>
                                            </h4>
                                            <div class="skills-checkboxes">
                                                <?php foreach ($all_skills as $skill): ?>
                                                    <?php if ($skill['category'] === $category): ?>
                                                        <?php
                                                        $isSelected = in_array($skill['id'], $userSkillIds);
                                                        $userSkill = null;
                                                        foreach ($user_skills ?? [] as $us) {
                                                            if (($us['skill_id'] ?? null) == $skill['id']) {
                                                                $userSkill = $us;
                                                                break;
                                                            }
                                                        }
                                                        ?>
                                                        <div class="skill-checkbox-wrapper">
                                                            <input type="checkbox" 
                                                                   id="skill_<?= $skill['id'] ?>" 
                                                                   name="skills[<?= $skill['id'] ?>][selected]" 
                                                                   value="1"
                                                                   <?= $isSelected ? 'checked' : '' ?>
                                                                   onchange="toggleSkillLevel(<?= $skill['id'] ?>)">
                                                            <label for="skill_<?= $skill['id'] ?>" class="skill-label">
                                                                <?= htmlspecialchars($skill['name']) ?>
                                                            </label>
                                                            <select name="skills[<?= $skill['id'] ?>][level]" 
                                                                    id="level_<?= $skill['id'] ?>"
                                                                    class="skill-level-select"
                                                                    <?= !$isSelected ? 'disabled' : '' ?>>
                                                                <option value="débutant" <?= ($userSkill['level'] ?? '') === 'débutant' ? 'selected' : '' ?>>Débutant</option>
                                                                <option value="intermédiaire" <?= ($userSkill['level'] ?? 'intermédiaire') === 'intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                                                                <option value="avancé" <?= ($userSkill['level'] ?? '') === 'avancé' ? 'selected' : '' ?>>Avancé</option>
                                                                <option value="expert" <?= ($userSkill['level'] ?? '') === 'expert' ? 'selected' : '' ?>>Expert</option>
                                                            </select>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Aucune compétence disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions-edit">
                        <button type="submit" class="btn-save-changes">
                            <i class="fas fa-check-circle"></i> Enregistrer les modifications
                        </button>
                        <a href="<?= BASE_URL ?>/user/profile" class="btn-cancel-changes">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
.edit-profile-section {
    padding: 60px 0 80px;
    background: #f8f9fa;
}

/* Page Header */
.page-header-edit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}

.header-content h1 {
    margin: 0 0 10px;
    font-size: 2rem;
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-content p {
    margin: 0;
    color: #6c757d;
    font-size: 1.05rem;
}

.btn-back {
    padding: 12px 25px;
    background: #f8f9fa;
    color: var(--dark-color);
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(-5px);
}

/* Form Grid */
.edit-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 30px;
}

/* Cards */
.edit-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    margin-bottom: 25px;
    transition: all 0.3s ease;
}

.edit-card:hover {
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
}

.card-header-edit {
    padding: 20px 25px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.05), rgba(46, 204, 113, 0.05));
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header-edit i {
    font-size: 1.3rem;
    color: var(--primary-color);
}

.card-header-edit h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-body-edit {
    padding: 25px;
}

/* Photo Upload */
.photo-upload-area {
    text-align: center;
}

.current-photo {
    margin-bottom: 20px;
}

.current-photo img,
.avatar-placeholder-edit {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #f0f0f0;
}

.avatar-placeholder-edit {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
}

.file-input-hidden {
    display: none;
}

.btn-upload {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px 25px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.btn-upload:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
}

.upload-hint {
    display: block;
    color: #6c757d;
    font-size: 0.85rem;
}

.current-cv {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
}

.current-cv i {
    font-size: 2rem;
    color: #e74c3c;
}

.cv-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.cv-info strong {
    color: var(--dark-color);
}

.cv-info a {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
}

/* Form Elements */
.form-row-edit {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group-edit {
    margin-bottom: 25px;
}

.form-group-edit label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: var(--dark-color);
    font-size: 0.95rem;
}

.form-input-edit,
.form-textarea-edit {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-input-edit:focus,
.form-textarea-edit:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.disabled-input {
    background: #f8f9fa !important;
    cursor: not-allowed;
    color: #6c757d;
}

.form-textarea-edit {
    resize: vertical;
}

.form-hint {
    display: block;
    margin-top: 6px;
    color: #6c757d;
    font-size: 0.85rem;
}

.info-text {
    background: rgba(52, 152, 219, 0.05);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-text i {
    color: var(--primary-color);
}

/* Skills Selector */
.skills-selector {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.skill-category-group {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    background: #fafbfc;
    transition: all 0.3s ease;
}

.skill-category-group:hover {
    border-color: var(--primary-color);
}

.category-title {
    margin: 0 0 15px;
    font-size: 1rem;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.skills-checkboxes {
    display: grid;
    gap: 10px;
}

.skill-checkbox-wrapper {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: white;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.skill-checkbox-wrapper:hover {
    background: rgba(52, 152, 219, 0.05);
}

.skill-checkbox-wrapper input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: var(--primary-color);
}

.skill-label {
    font-weight: 500;
    color: var(--dark-color);
    cursor: pointer;
}

.skill-level-select {
    padding: 8px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 140px;
}

.skill-level-select:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f8f9fa;
}

.skill-level-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Form Actions */
.form-actions-edit {
    display: flex;
    gap: 15px;
}

.btn-save-changes {
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

.btn-save-changes:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
}

.btn-cancel-changes {
    padding: 15px 30px;
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

.btn-cancel-changes:hover {
    border-color: var(--danger-color);
    color: var(--danger-color);
}

/* Responsive */
@media (max-width: 1200px) {
    .edit-grid {
        grid-template-columns: 320px 1fr;
    }
}

@media (max-width: 992px) {
    .edit-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header-edit {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .header-content h1 {
        font-size: 1.5rem;
        justify-content: center;
    }
    
    .form-row-edit {
        grid-template-columns: 1fr;
    }
    
    .form-actions-edit {
        flex-direction: column;
    }
}
</style>

<script>
// Preview photo before upload
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photoPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                preview.outerHTML = `<img src="${e.target.result}" alt="Preview" id="photoPreview" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0;">`;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Toggle skill level select
function toggleSkillLevel(skillId) {
    const checkbox = document.getElementById(`skill_${skillId}`);
    const select = document.getElementById(`level_${skillId}`);
    select.disabled = !checkbox.checked;
}

// Character counter for bio
const bioTextarea = document.getElementById('bio');
const bioCounter = document.getElementById('bioCounter');

if (bioTextarea && bioCounter) {
    bioTextarea.addEventListener('input', function() {
        const remaining = 500 - this.value.length;
        bioCounter.textContent = `${remaining} caractères restants`;
        bioCounter.style.color = remaining < 50 ? 'var(--danger-color)' : '#6c757d';
    });
}

// Form validation
document.querySelector('.edit-profile-form').addEventListener('submit', function(e) {
    const university = document.getElementById('university').value.trim();
    const faculty = document.getElementById('faculty').value.trim();
    const city = document.getElementById('city').value;
    
    if (!university || !faculty || !city) {
        e.preventDefault();
        alert('Veuillez remplir tous les champs obligatoires');
        return false;
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
