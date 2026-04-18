/**
 * FORCE LA NAVIGATION MOBILE EN BAS
 * Script de secours pour forcer le positionnement via JavaScript
 */

(function() {
    'use strict';
    
    // Fonction pour forcer le positionnement en bas sur mobile
    function forceMobileNavToBottom() {
        // Vérifier si on est sur mobile
        if (window.innerWidth <= 768) {
            const navMenu = document.querySelector('.nav-menu') || document.getElementById('navMenu');
            
            if (navMenu) {
                // Forcer les styles via JavaScript
                navMenu.style.position = 'fixed';
                navMenu.style.bottom = '0';
                navMenu.style.top = 'auto';
                navMenu.style.left = '0';
                navMenu.style.right = '0';
                navMenu.style.width = '100%';
                navMenu.style.zIndex = '9999';
                navMenu.style.display = 'flex';
                navMenu.style.flexDirection = 'row';
                navMenu.style.justifyContent = 'space-around';
                navMenu.style.alignItems = 'center';
                navMenu.style.padding = '8px';
                navMenu.style.background = 'rgba(255, 255, 255, 0.98)';
                navMenu.style.boxShadow = '0 -2px 16px rgba(0, 0, 0, 0.08)';
                navMenu.style.borderTop = '0.5px solid rgba(0, 0, 0, 0.1)';
                
                console.log('✅ Navigation mobile forcée EN BAS');
            }
        } else {
            // Sur desktop, restaurer le comportement normal
            const navMenu = document.querySelector('.nav-menu') || document.getElementById('navMenu');
            
            if (navMenu) {
                navMenu.style.position = 'relative';
                navMenu.style.bottom = 'auto';
                navMenu.style.width = 'auto';
            }
        }
    }
    
    // Exécuter immédiatement
    forceMobileNavToBottom();
    
    // Exécuter quand le DOM est chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceMobileNavToBottom);
    }
    
    // Exécuter lors du redimensionnement de la fenêtre
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(forceMobileNavToBottom, 100);
    });
    
    // Vérifier périodiquement (au cas où)
    setInterval(function() {
        if (window.innerWidth <= 768) {
            const navMenu = document.querySelector('.nav-menu') || document.getElementById('navMenu');
            if (navMenu && navMenu.style.position !== 'fixed') {
                console.log('⚠️ Navigation détectée en mauvaise position, correction...');
                forceMobileNavToBottom();
            }
        }
    }, 1000);
    
})();

