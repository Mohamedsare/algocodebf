/**
 * Bouton Retour en Haut 🇧🇫
 * Animation patriotique avec effet de progression
 */

document.addEventListener('DOMContentLoaded', function() {
    const scrollButton = document.getElementById('scrollToTop');
    
    if (!scrollButton) return;
    
    // Créer l'anneau de progression SVG
    const progressRing = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    progressRing.classList.add('progress-ring');
    progressRing.setAttribute('viewBox', '0 0 66 66');
    
    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.classList.add('progress-ring-circle');
    circle.setAttribute('cx', '33');
    circle.setAttribute('cy', '33');
    circle.setAttribute('r', '30');
    
    progressRing.appendChild(circle);
    scrollButton.insertBefore(progressRing, scrollButton.firstChild);
    
    const circumference = 2 * Math.PI * 30; // rayon = 30
    circle.style.strokeDasharray = `${circumference} ${circumference}`;
    circle.style.strokeDashoffset = circumference;
    
    let isScrolling = false;
    
    // Fonction pour mettre à jour le bouton selon le scroll
    function updateScrollButton() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const documentHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrollPercentage = (scrollTop / documentHeight) * 100;
        
        // Afficher le bouton après 300px de scroll
        if (scrollTop > 300) {
            scrollButton.classList.add('visible');
        } else {
            scrollButton.classList.remove('visible');
        }
        
        // Mettre à jour la progression de l'anneau
        const offset = circumference - (scrollPercentage / 100) * circumference;
        circle.style.strokeDashoffset = offset;
        
        isScrolling = false;
    }
    
    // Optimiser les performances avec requestAnimationFrame
    function handleScroll() {
        if (!isScrolling) {
            window.requestAnimationFrame(updateScrollButton);
            isScrolling = true;
        }
    }
    
    // Écouter le scroll
    window.addEventListener('scroll', handleScroll, { passive: true });
    
    // Action au clic : scroll smooth vers le haut
    scrollButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Ajouter une classe pour l'animation de clic
        scrollButton.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            scrollButton.style.transform = '';
        }, 150);
        
        // Scroll smooth vers le haut
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        
        // Créer un effet d'étoiles qui montent
        createStarBurst();
    });
    
    // Fonction pour créer un effet d'étoiles qui montent
    function createStarBurst() {
        const buttonRect = scrollButton.getBoundingClientRect();
        const colors = ['#C8102E', '#006A4E', '#FFD100'];
        
        for (let i = 0; i < 8; i++) {
            const star = document.createElement('div');
            star.innerHTML = '★';
            star.style.position = 'fixed';
            star.style.left = buttonRect.left + buttonRect.width / 2 + 'px';
            star.style.top = buttonRect.top + buttonRect.height / 2 + 'px';
            star.style.color = colors[i % 3];
            star.style.fontSize = (Math.random() * 10 + 15) + 'px';
            star.style.pointerEvents = 'none';
            star.style.zIndex = '1000';
            star.style.transition = 'all 0.8s ease-out';
            star.style.opacity = '1';
            
            document.body.appendChild(star);
            
            // Animation aléatoire
            setTimeout(() => {
                const angle = (Math.PI * 2 * i) / 8;
                const distance = 80 + Math.random() * 40;
                const x = Math.cos(angle) * distance;
                const y = Math.sin(angle) * distance - 50;
                
                star.style.transform = `translate(${x}px, ${y}px) rotate(${Math.random() * 360}deg)`;
                star.style.opacity = '0';
            }, 50);
            
            // Supprimer après l'animation
            setTimeout(() => {
                star.remove();
            }, 900);
        }
    }
    
    // Effet de vibration au survol (mobile)
    if ('vibrate' in navigator) {
        scrollButton.addEventListener('mouseenter', function() {
            navigator.vibrate(10);
        });
    }
    
    // Ajouter un effet de pulse périodique quand visible
    let pulseInterval;
    
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                if (scrollButton.classList.contains('visible')) {
                    // Pulse toutes les 5 secondes
                    pulseInterval = setInterval(() => {
                        scrollButton.style.animation = 'pulse-burkinabe 0.6s ease-in-out';
                        setTimeout(() => {
                            scrollButton.style.animation = '';
                        }, 600);
                    }, 5000);
                } else {
                    clearInterval(pulseInterval);
                }
            }
        });
    });
    
    observer.observe(scrollButton, { attributes: true });
    
    // Ajouter les keyframes pour l'animation pulse
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse-burkinabe {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 0 30px rgba(200, 16, 46, 0.6);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Initialiser
    updateScrollButton();
    
    console.log('🇧🇫 Bouton "Retour en haut" activé avec style patriotique !');
});

// Alternative pour les navigateurs qui ne supportent pas smooth scroll
if (!('scrollBehavior' in document.documentElement.style)) {
    // Polyfill pour smooth scroll
    window.scrollTo = function(options) {
        if (typeof options === 'object') {
            const start = window.pageYOffset;
            const target = options.top || 0;
            const duration = 500;
            const startTime = performance.now();
            
            function smoothScroll(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeInOutQuad = progress < 0.5 
                    ? 2 * progress * progress 
                    : 1 - Math.pow(-2 * progress + 2, 2) / 2;
                
                window.scrollTo(0, start + (target - start) * easeInOutQuad);
                
                if (progress < 1) {
                    requestAnimationFrame(smoothScroll);
                }
            }
            
            requestAnimationFrame(smoothScroll);
        }
    };
}

