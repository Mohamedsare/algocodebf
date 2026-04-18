/**
 * Fichier JavaScript principal pour AlgoCodeBF
 * Gestion des interactions et fonctionnalités côté client
 */

// Le bouton Connexion est maintenant dans le menu burger

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // Header auto-hide lors du scroll
    // ========================================
    const navbar = document.querySelector('.navbar');
    let lastScrollTop = 0;
    let scrollThreshold = 100; // Commencer à cacher après 100px de scroll
    let isScrolling = false;
    let isBurgerMenuOpen = false;
    
    window.addEventListener('scroll', function() {
        if (!isScrolling) {
            window.requestAnimationFrame(function() {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                // Ajouter classe 'scrolled' si on a scrollé
                if (currentScroll > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                // Ne pas cacher le header si le menu burger est ouvert
                if (isBurgerMenuOpen) {
                    navbar.classList.remove('hidden');
                    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
                    isScrolling = false;
                    return;
                }
                
                // Gérer le hide/show uniquement après le seuil
                if (currentScroll > scrollThreshold) {
                    if (currentScroll > lastScrollTop && Math.abs(currentScroll - lastScrollTop) > 10) {
                        // Scroll vers le bas - cacher le header (avec seuil de 10px pour éviter les micro-mouvements)
                        navbar.classList.add('hidden');
                    } else if (currentScroll < lastScrollTop) {
                        // Scroll vers le haut - montrer le header
                        navbar.classList.remove('hidden');
                    }
                } else {
                    // Toujours montrer le header en haut de page
                    navbar.classList.remove('hidden');
                }
                
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
                isScrolling = false;
            });
            
            isScrolling = true;
        }
    }, { passive: true });
    
    // ========================================
    // Navigation mobile - Dock iOS + Menu Burger
    // ========================================
    const navToggle = document.getElementById('navToggle');
    const burgerMenu = document.getElementById('burgerMenu');
    
    if (navToggle && burgerMenu) {
        // Toggle du menu burger
        navToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Toggle des classes
            const isActive = burgerMenu.classList.toggle('active');
            this.classList.toggle('active');
            
            // Mettre à jour l'état du menu burger
            isBurgerMenuOpen = isActive;
            
            // Toujours montrer le header quand le menu est ouvert
            if (isActive) {
                navbar.classList.remove('hidden');
            }
            
            // Effet de vibration tactile (si supporté)
            if ('vibrate' in navigator) {
                navigator.vibrate(20);
            }
            
            // Effet sonore visuel
            createRipple(e, this);
            
            // Empêcher le scroll du body quand le menu est ouvert
            if (isActive) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        // Fermer le menu en cliquant en dehors
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !burgerMenu.contains(e.target)) {
                if (burgerMenu.classList.contains('active')) {
                    burgerMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                    document.body.style.overflow = '';
                    isBurgerMenuOpen = false;
                }
            }
        });
        
        // Fermer le menu en appuyant sur Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && burgerMenu.classList.contains('active')) {
                burgerMenu.classList.remove('active');
                navToggle.classList.remove('active');
                document.body.style.overflow = '';
                isBurgerMenuOpen = false;
            }
        });
        
        // Fermer le menu après un clic sur un lien
        const burgerLinks = burgerMenu.querySelectorAll('a');
        burgerLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => {
                    burgerMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                    document.body.style.overflow = '';
                    isBurgerMenuOpen = false;
                }, 200);
            });
        });
    }
    
    // Fonction pour créer un effet de ripple
    function createRipple(event, element) {
        const ripple = document.createElement('span');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.4) 0%, transparent 70%);
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
            animation: rippleEffect 0.6s ease-out;
            z-index: 1000;
        `;
        
        element.style.position = 'relative';
        element.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    // Ajouter les animations CSS pour le ripple
    if (!document.querySelector('#ripple-animation-style')) {
        const style = document.createElement('style');
        style.id = 'ripple-animation-style';
        style.textContent = `
            @keyframes rippleEffect {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Gérer les dropdowns sur mobile (désactivé pour Dock iOS)
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    // Dans le Dock iOS, on laisse le lien fonctionner normalement
                    // Pas de toggle du dropdown
                }
            });
        }
    });
    
    // PAS DE MODIFICATION DU HTML - Les icônes sont gérées en CSS pur
    
    // Ajouter la classe 'active' à l'élément du menu actif
    function setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.nav-menu a');
        
        menuLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (currentPath.includes(linkPath) && linkPath !== '/') {
                link.classList.add('active');
            }
        });
    }
    
    // Appeler au chargement
    setActiveMenuItem();
    
    // ========================================
    // Overlay de recherche
    // ========================================
    const searchToggle = document.getElementById('searchToggle');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');
    const searchInput = document.getElementById('searchInput');
    
    if (searchToggle && searchOverlay && searchClose) {
        // Ouvrir l'overlay de recherche
        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            // Focus sur l'input après l'animation
            setTimeout(() => {
                searchInput.focus();
            }, 400);
        });
        
        // Fermer l'overlay avec le bouton
        searchClose.addEventListener('click', function() {
            searchOverlay.classList.remove('active');
        });
        
        // Fermer l'overlay avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
            }
        });
        
        // Fermer l'overlay en cliquant en dehors
        searchOverlay.addEventListener('click', function(e) {
            if (e.target === searchOverlay) {
                searchOverlay.classList.remove('active');
            }
        });
    }
    
    // ========================================
    // Fermer les alertes automatiquement
    // ========================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // ========================================
    // Toggle Like (AJAX)
    // ========================================
    const likeButtons = document.querySelectorAll('.btn-like');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const type = this.dataset.type;
            const id = this.dataset.id;
            
            fetch(`${window.location.origin}/AlgoCodeBF/forum/toggleLike`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `type=${type}&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'interface
                    this.classList.toggle('liked');
                    const count = this.querySelector('.like-count');
                    if (count) {
                        const currentCount = parseInt(count.textContent);
                        count.textContent = this.classList.contains('liked') ? 
                            currentCount + 1 : currentCount - 1;
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    });
    
    // ========================================
    // Confirmation avant suppression
    // ========================================
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });
    
    // ========================================
    // Prévisualisation d'image avant upload
    // ========================================
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById(input.dataset.preview);
                    if (preview) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // ========================================
    // Validation des formulaires
    // ========================================
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
            }
        });
    });
    
    // ========================================
    // Compteur de caractères pour textarea
    // ========================================
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        counter.style.textAlign = 'right';
        counter.style.fontSize = '14px';
        counter.style.color = '#999';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} caractères restants`;
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
    
    // ========================================
    // Smooth scroll pour les ancres
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // ========================================
    // Auto-resize textarea
    // ========================================
    const autoResizeTextareas = document.querySelectorAll('textarea[data-autoresize]');
    autoResizeTextareas.forEach(textarea => {
        function resize() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        textarea.addEventListener('input', resize);
        resize();
    });
    
    // ========================================
    // Toggle password visibility
    // ========================================
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = document.querySelector(this.dataset.target);
            if (input) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            }
        });
    });
    
    // ========================================
    // Recherche en temps réel
    // ========================================
    const searchInputs = document.querySelectorAll('input[data-live-search]');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Implémenter la recherche AJAX ici
                console.log('Recherche:', this.value);
            }, 500);
        });
    });
    
    // ========================================
    // Modal simple
    // ========================================
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    
    // Fermer le modal en cliquant en dehors
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // ========================================
    // Copy to clipboard
    // ========================================
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copié dans le presse-papiers!');
        }).catch(err => {
            console.error('Erreur de copie:', err);
        });
    };
    
    // ========================================
    // Lazy loading des images
    // ========================================
    const lazyImages = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback pour les navigateurs ne supportant pas IntersectionObserver
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
    
});

// ========================================
// Fonctions utilitaires globales
// ========================================

/**
 * Formater une date relative (il y a X minutes/heures/jours)
 */
function timeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    
    const intervals = {
        année: 31536000,
        mois: 2592000,
        semaine: 604800,
        jour: 86400,
        heure: 3600,
        minute: 60,
        seconde: 1
    };
    
    for (const [name, value] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / value);
        if (interval >= 1) {
            return `Il y a ${interval} ${name}${interval > 1 ? 's' : ''}`;
        }
    }
    
    return 'À l\'instant';
}

/**
 * Debounce function pour optimiser les appels
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Notification toast simple
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 25px;
        background-color: ${type === 'success' ? '#2ecc71' : type === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/* ========================================
   Hero Carousel Functionality
   ======================================== */
let currentSlide = 0;
let autoSlideInterval;

// Fonction pour changer de slide
function moveSlide(direction) {
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (slides.length === 0) return;
    
    // Retirer la classe active de la slide actuelle
    slides[currentSlide].classList.remove('active');
    indicators[currentSlide].classList.remove('active');
    
    // Calculer le nouvel index
    currentSlide = (currentSlide + direction + slides.length) % slides.length;
    
    // Ajouter la classe active à la nouvelle slide
    slides[currentSlide].classList.add('active');
    indicators[currentSlide].classList.add('active');
    
    // Réinitialiser le timer auto-slide
    resetAutoSlide();
}

// Fonction pour aller à une slide spécifique
function goToSlide(index) {
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    if (slides.length === 0) return;
    
    // Retirer la classe active de la slide actuelle
    slides[currentSlide].classList.remove('active');
    indicators[currentSlide].classList.remove('active');
    
    // Mettre à jour l'index
    currentSlide = index;
    
    // Ajouter la classe active à la nouvelle slide
    slides[currentSlide].classList.add('active');
    indicators[currentSlide].classList.add('active');
    
    // Réinitialiser le timer auto-slide
    resetAutoSlide();
}

// Fonction pour démarrer le défilement automatique
function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
        moveSlide(1);
    }, 5000); // Change de slide toutes les 5 secondes
}

// Fonction pour réinitialiser le timer auto-slide
function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
}

// Démarrer le carousel au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.hero-carousel');
    if (carousel) {
        startAutoSlide();
        
        // Pause au survol
        carousel.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        
        // Reprendre au départ de la souris
        carousel.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
        
        // Support du swipe sur mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        carousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                moveSlide(1); // Swipe left
            }
            if (touchEndX > touchStartX + 50) {
                moveSlide(-1); // Swipe right
            }
        }
    }
});

/* ========================================
   Stats Counter Animation
   ======================================== */
function animateCounter(element, target, duration = 1500) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Observer pour déclencher l'animation au scroll
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statNumbers = entry.target.querySelectorAll('.stat-number');
            statNumbers.forEach(num => {
                if (!num.classList.contains('animated')) {
                    const target = parseInt(num.textContent);
                    num.classList.add('animated');
                    animateCounter(num, target);
                }
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

// Observer la section stats
document.addEventListener('DOMContentLoaded', () => {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        statsObserver.observe(statsSection);
    }
});

