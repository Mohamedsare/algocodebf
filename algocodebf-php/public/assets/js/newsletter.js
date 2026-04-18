/**
 * Gestion des abonnements newsletter
 */

// Définir BASE_URL si non défini (pour compatibilité)
if (typeof BASE_URL === 'undefined') {
    var BASE_URL = window.BASE_URL || '/AlgoCodeBF/public';
}

// Fonction générique pour s'abonner à la newsletter
window.subscribeNewsletter = function(event) {
    event.preventDefault();
    
    const form = event.target;
    const emailInput = form.querySelector('input[type="email"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const email = emailInput.value.trim();
    
    if (!email || !validateEmail(email)) {
        showNewsletterMessage('❌ Veuillez entrer une adresse email valide', 'error', form);
        return;
    }
    
    // Désactiver le bouton pendant la requête
    const originalBtnContent = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Envoyer la requête
    fetch(BASE_URL + '/newsletter/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNewsletterMessage(data.message, 'success', form);
            emailInput.value = ''; // Vider le champ
        } else {
            showNewsletterMessage(data.message, 'error', form);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNewsletterMessage('❌ Une erreur est survenue. Veuillez réessayer.', 'error', form);
    })
    .finally(() => {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnContent;
    });
}

// Alias pour la version ultra (blog/index.php)
window.subscribeNewsletterUltra = function(event) {
    window.subscribeNewsletter(event);
};

// Valider une adresse email
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Afficher un message de confirmation/erreur
function showNewsletterMessage(message, type, form) {
    // Supprimer les anciens messages
    var oldMessages = form.querySelectorAll('.newsletter-message');
    oldMessages.forEach(function(msg) { msg.remove(); });
    
    // Créer le nouveau message
    var messageDiv = document.createElement('div');
    messageDiv.className = 'newsletter-message newsletter-' + type;
    messageDiv.innerHTML = message;
    
    // Styles inline pour le message
    messageDiv.style.cssText = `
        margin-top: 12px;
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: center;
        animation: slideIn 0.3s ease;
        ${type === 'success' 
            ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' 
            : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'}
    `;
    
    // Ajouter le message après le formulaire
    form.appendChild(messageDiv);
    
    // Supprimer le message après 5 secondes
    setTimeout(function() {
        messageDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(function() { messageDiv.remove(); }, 300);
    }, 5000);
}

// Ajouter les animations CSS si elles n'existent pas déjà
if (!document.querySelector('#newsletter-animations')) {
    var style = document.createElement('style');
    style.id = 'newsletter-animations';
    style.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
        
        .newsletter-message {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    `;
    document.head.appendChild(style);
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Newsletter script: Initialisation...');
    console.log('🔍 BASE_URL:', BASE_URL);
    console.log('🔍 subscribeNewsletter:', typeof window.subscribeNewsletter);
    console.log('🔍 subscribeNewsletterUltra:', typeof window.subscribeNewsletterUltra);
    
    // Attacher les gestionnaires d'événements à tous les formulaires newsletter
    var newsletterForms = document.querySelectorAll('.newsletter-form, .newsletter-form-inline, .newsletter-form-ultra');
    console.log('🔍 Formulaires newsletter trouvés:', newsletterForms.length);
    
    newsletterForms.forEach(function(form) {
        // Si le formulaire n'a pas déjà un gestionnaire onsubmit
        if (!form.hasAttribute('onsubmit')) {
            form.addEventListener('submit', window.subscribeNewsletter);
            console.log('✅ Gestionnaire ajouté à un formulaire');
        }
    });
});

console.log('✅ Newsletter script chargé');

