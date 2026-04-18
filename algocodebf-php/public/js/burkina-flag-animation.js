/**
 * Animation du Drapeau Burkinabè Flottant 🇧🇫
 * Effet inspirant et patriotique
 */

document.addEventListener('DOMContentLoaded', function() {
    const ctaSection = document.querySelector('.cta-section');
    
    if (!ctaSection) return;
    
    // Créer le canvas pour l'animation du drapeau
    const canvas = document.createElement('canvas');
    canvas.className = 'flag-canvas';
    canvas.style.position = 'absolute';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.pointerEvents = 'none';
    canvas.style.zIndex = '1';
    ctaSection.insertBefore(canvas, ctaSection.firstChild);
    
    const ctx = canvas.getContext('2d');
    let animationId;
    
    // Redimensionner le canvas
    function resizeCanvas() {
        canvas.width = ctaSection.offsetWidth;
        canvas.height = ctaSection.offsetHeight;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Particules d'étoiles scintillantes
    class Star {
        constructor() {
            this.reset();
        }
        
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 1;
            this.speedX = (Math.random() - 0.5) * 0.5;
            this.speedY = (Math.random() - 0.5) * 0.5;
            this.opacity = Math.random();
            this.fadeSpeed = (Math.random() * 0.02) + 0.01;
            this.growing = Math.random() > 0.5;
        }
        
        update() {
            this.x += this.speedX;
            this.y += this.speedY;
            
            if (this.growing) {
                this.opacity += this.fadeSpeed;
                if (this.opacity >= 1) this.growing = false;
            } else {
                this.opacity -= this.fadeSpeed;
                if (this.opacity <= 0) this.reset();
            }
            
            if (this.x < 0 || this.x > canvas.width || 
                this.y < 0 || this.y > canvas.height) {
                this.reset();
            }
        }
        
        draw() {
            ctx.save();
            ctx.globalAlpha = this.opacity * 0.6;
            ctx.fillStyle = '#FFD100';
            ctx.shadowBlur = 10;
            ctx.shadowColor = '#FFD100';
            
            // Dessiner une étoile
            ctx.beginPath();
            for (let i = 0; i < 5; i++) {
                const angle = (Math.PI * 2 * i) / 5 - Math.PI / 2;
                const x = this.x + Math.cos(angle) * this.size;
                const y = this.y + Math.sin(angle) * this.size;
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            ctx.closePath();
            ctx.fill();
            ctx.restore();
        }
    }
    
    // Créer des étoiles
    const stars = [];
    const starCount = window.innerWidth < 768 ? 30 : 50;
    for (let i = 0; i < starCount; i++) {
        stars.push(new Star());
    }
    
    // Effet d'ondulation du drapeau
    let time = 0;
    
    function drawWaves() {
        ctx.save();
        
        // Dessiner des lignes ondulées pour simuler le mouvement du drapeau
        const waveCount = 8;
        const amplitude = 15;
        const frequency = 0.02;
        
        for (let i = 0; i < waveCount; i++) {
            ctx.beginPath();
            ctx.strokeStyle = `rgba(255, 255, 255, ${0.1 - i * 0.01})`;
            ctx.lineWidth = 2;
            
            const yOffset = (canvas.height / waveCount) * i;
            
            for (let x = 0; x < canvas.width; x += 5) {
                const y = yOffset + Math.sin(x * frequency + time + i * 0.5) * amplitude;
                if (x === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }
            
            ctx.stroke();
        }
        
        ctx.restore();
    }
    
    // Particules dorées qui montent
    class GoldenParticle {
        constructor() {
            this.reset();
        }
        
        reset() {
            this.x = Math.random() * canvas.width;
            this.y = canvas.height + 10;
            this.size = Math.random() * 3 + 1;
            this.speedY = -(Math.random() * 2 + 1);
            this.speedX = (Math.random() - 0.5) * 0.5;
            this.opacity = Math.random() * 0.5 + 0.5;
        }
        
        update() {
            this.y += this.speedY;
            this.x += this.speedX;
            this.opacity -= 0.005;
            
            if (this.y < -10 || this.opacity <= 0) {
                this.reset();
            }
        }
        
        draw() {
            ctx.save();
            ctx.globalAlpha = this.opacity;
            ctx.fillStyle = '#FFD100';
            ctx.shadowBlur = 5;
            ctx.shadowColor = '#FFD100';
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    }
    
    // Créer des particules dorées
    const goldenParticles = [];
    const particleCount = window.innerWidth < 768 ? 20 : 40;
    for (let i = 0; i < particleCount; i++) {
        goldenParticles.push(new GoldenParticle());
    }
    
    // Animation principale
    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        time += 0.05;
        
        // Dessiner les ondulations
        drawWaves();
        
        // Mettre à jour et dessiner les étoiles
        stars.forEach(star => {
            star.update();
            star.draw();
        });
        
        // Mettre à jour et dessiner les particules dorées
        goldenParticles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        animationId = requestAnimationFrame(animate);
    }
    
    // Démarrer l'animation
    animate();
    
    // Effet de balancement sur le contenu
    const ctaContent = document.querySelector('.cta-content');
    if (ctaContent) {
        let contentTime = 0;
        
        function floatContent() {
            contentTime += 0.02;
            const translateY = Math.sin(contentTime) * 5;
            ctaContent.style.transform = `translateY(${translateY}px)`;
            requestAnimationFrame(floatContent);
        }
        
        floatContent();
    }
    
    // Effet parallax au scroll
    let ticking = false;
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        const ctaOffset = ctaSection.offsetTop;
        const ctaHeight = ctaSection.offsetHeight;
        
        if (scrolled + window.innerHeight > ctaOffset && 
            scrolled < ctaOffset + ctaHeight) {
            const parallaxValue = (scrolled - ctaOffset) * 0.3;
            canvas.style.transform = `translateY(${parallaxValue}px)`;
        }
        
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(updateParallax);
            ticking = true;
        }
    });
    
    // Effet au survol du bouton
    const ctaButton = ctaSection.querySelector('.btn');
    if (ctaButton) {
        ctaButton.addEventListener('mouseenter', function() {
            // Créer une explosion d'étoiles
            for (let i = 0; i < 10; i++) {
                const star = new Star();
                const rect = ctaButton.getBoundingClientRect();
                const ctaRect = ctaSection.getBoundingClientRect();
                star.x = rect.left - ctaRect.left + rect.width / 2;
                star.y = rect.top - ctaRect.top + rect.height / 2;
                star.speedX = (Math.random() - 0.5) * 5;
                star.speedY = (Math.random() - 0.5) * 5;
                stars.push(star);
            }
        });
    }
    
    // Nettoyer lors de la destruction
    window.addEventListener('beforeunload', function() {
        if (animationId) {
            cancelAnimationFrame(animationId);
        }
    });
});

// Animation de pulsation pour l'étoile centrale CSS
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes flag-wave {
            0%, 100% {
                transform: perspective(1000px) rotateY(0deg);
            }
            25% {
                transform: perspective(1000px) rotateY(2deg);
            }
            75% {
                transform: perspective(1000px) rotateY(-2deg);
            }
        }
        
        .cta-section {
            animation: flag-wave 6s ease-in-out infinite;
        }
        
        @keyframes star-glow {
            0%, 100% {
                filter: drop-shadow(0 0 20px #FFD100);
                transform: translate(-50%, -50%) rotate(0deg) scale(1);
            }
            50% {
                filter: drop-shadow(0 0 40px #FFD100);
                transform: translate(-50%, -50%) rotate(180deg) scale(1.15);
            }
        }
        
        .cta-section::before {
            animation: star-glow 8s ease-in-out infinite, pulse-star 4s ease-in-out infinite;
        }
    `;
    document.head.appendChild(style);
});

console.log('🇧🇫 Animation du drapeau burkinabè activée !');

