// JS/COMMUNAUTE.js - Gestion de la page communauté

// ===== VARIABLES GLOBALES =====
let groupeActuelId = null;
let intervalRefresh = null;

// ===== QUAND LA PAGE EST CHARGÉE =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Page communauté chargée !');
    
    // Boutons "Rejoindre"
    document.querySelectorAll('.btn-rejoindre').forEach(btn => {
        btn.addEventListener('click', rejoindreGroupe);
    });
    
    // Boutons "Ouvrir le chat"
    document.querySelectorAll('.btn-ouvrir-chat').forEach(btn => {
        btn.addEventListener('click', ouvrirChat);
    });
    
    // Boutons "Membre" (quitter directement)
    document.querySelectorAll('.btn-quitter').forEach(btn => {
        btn.addEventListener('click', quitterGroupe);
    });
    
    // Formulaire d'envoi de message
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', envoyerMessage);
    }
    
    // Bouton quitter depuis le modal
    const btnQuitterModal = document.getElementById('btnQuitterGroupe');
    if (btnQuitterModal) {
        btnQuitterModal.addEventListener('click', function() {
            if (groupeActuelId) {
                quitterGroupe({ target: { dataset: { groupeId: groupeActuelId } } });
            }
        });
    }
});

// ===== FONCTION: REJOINDRE UN GROUPE =====
function rejoindreGroupe(e) {
    const groupeId = e.target.dataset.groupeId;
    console.log('🔵 Tentative de rejoindre le groupe:', groupeId);
    
    // Afficher un message de chargement
    e.target.textContent = 'Chargement...';
    e.target.disabled = true;
    
    // Envoyer la requête au serveur
    fetch('../INCLUDES/groupe_join.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'groupe_id=' + groupeId
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse serveur:', data);
        
        if (data.success) {
            // ✅ Succès !
            console.log('✅ Groupe rejoint !');
            
            // Recharger la page pour mettre à jour l'affichage
            location.reload();
        } else {
            // ❌ Erreur
            alert('Erreur : ' + data.message);
            e.target.textContent = '+ Rejoindre';
            e.target.disabled = false;
        }
    })
    .catch(error => {
        console.error('❌ Erreur:', error);
        alert('Une erreur est survenue. Réessaye plus tard.');
        e.target.textContent = '+ Rejoindre';
        e.target.disabled = false;
    });
}

// ===== FONCTION: QUITTER UN GROUPE =====
function quitterGroupe(e) {
    const groupeId = e.target.dataset.groupeId;
    
    if (!confirm('Es-tu sûr de vouloir quitter ce groupe ?')) {
        return;
    }
    
    console.log('🔴 Tentative de quitter le groupe:', groupeId);
    
    // Envoyer la requête au serveur
    fetch('../INCLUDES/groupe_leave.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'groupe_id=' + groupeId
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse serveur:', data);
        
        if (data.success) {
            // ✅ Succès !
            console.log('✅ Groupe quitté !');
            
            // Si on était dans le chat, le fermer
            if (groupeActuelId === groupeId) {
                fermerChat();
            }
            
            // Recharger la page
            location.reload();
        } else {
            // ❌ Erreur
            alert('Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('❌ Erreur:', error);
        alert('Une erreur est survenue.');
    });
}

// ===== FONCTION: OUVRIR LE CHAT (LA BULLE) =====
function ouvrirChat(e) {
    const groupeId = e.target.dataset.groupeId;
    groupeActuelId = groupeId;
    
    console.log('💬 Ouverture du chat pour le groupe:', groupeId);
    
    // Trouver le nom du groupe
    const card = e.target.closest('.groupe-card');
    const groupeNom = card.querySelector('h3').textContent;
    
    // Mettre à jour le modal
    document.getElementById('chatGroupeNom').textContent = groupeNom;
    document.getElementById('chatGroupeId').value = groupeId;
    
    // Afficher le modal et l'overlay
    document.getElementById('chatModal').classList.add('active');
    document.getElementById('modalOverlay').classList.add('active');
    
    // Charger les messages
    chargerMessages(groupeId);
    
    // Actualiser automatiquement les messages toutes les 5 secondes
    if (intervalRefresh) {
        clearInterval(intervalRefresh);
    }
    intervalRefresh = setInterval(() => {
        chargerMessages(groupeId);
    }, 5000); // 5000 ms = 5 secondes
}

// ===== FONCTION: FERMER LE CHAT =====
function fermerChat() {
    console.log('❌ Fermeture du chat');
    
    // Cacher le modal et l'overlay
    document.getElementById('chatModal').classList.remove('active');
    document.getElementById('modalOverlay').classList.remove('active');
    
    // Arrêter l'actualisation automatique
    if (intervalRefresh) {
        clearInterval(intervalRefresh);
        intervalRefresh = null;
    }
    
    groupeActuelId = null;
}

// ===== FONCTION: CHARGER LES MESSAGES =====
function chargerMessages(groupeId) {
    console.log('📥 Chargement des messages pour le groupe:', groupeId);
    
    const chatMessages = document.getElementById('chatMessages');
    
    // Envoyer la requête au serveur
    fetch('../INCLUDES/groupe_messages.php?groupe_id=' + groupeId)
        .then(response => response.json())
        .then(data => {
            console.log('Messages reçus:', data);
            
            if (data.success) {
                // Afficher les messages
                afficherMessages(data.messages);
            } else {
                chatMessages.innerHTML = '<div class="no-messages">Erreur : ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement messages:', error);
            chatMessages.innerHTML = '<div class="no-messages">Erreur de chargement</div>';
        });
}

// ===== FONCTION: AFFICHER LES MESSAGES =====
function afficherMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    
    if (messages.length === 0) {
        chatMessages.innerHTML = '<div class="no-messages">Aucun message pour le moment. Sois le premier à écrire !</div>';
        return;
    }
    
    // Générer le HTML des messages
    let html = '';
    messages.forEach(msg => {
        html += `
            <div class="message-item">
                <div class="message-header">
                    <span class="message-author">${escapeHtml(msg.pseudo)}</span>
                    <span class="message-time">${msg.heure}</span>
                </div>
                <div class="message-content">${escapeHtml(msg.contenu)}</div>
            </div>
        `;
    });
    
    chatMessages.innerHTML = html;
    
    // Scroller en bas automatiquement
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// ===== FONCTION: ENVOYER UN MESSAGE =====
function envoyerMessage(e) {
    e.preventDefault();
    
    const groupeId = document.getElementById('chatGroupeId').value;
    const messageInput = document.getElementById('chatInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        alert('Écris un message avant d\'envoyer !');
        return;
    }
    
    console.log('📤 Envoi du message:', message);
    
    // Désactiver le bouton pendant l'envoi
    const btnSubmit = e.target.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Envoi...';
    
    // Envoyer la requête
    fetch('../INCLUDES/groupe_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'groupe_id=' + groupeId + '&message=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse serveur:', data);
        
        if (data.success) {
            // ✅ Succès !
            console.log('✅ Message envoyé !');
            
            // Vider le champ
            messageInput.value = '';
            
            // Recharger les messages immédiatement
            chargerMessages(groupeId);
        } else {
            // ❌ Erreur
            alert('Erreur : ' + data.message);
        }
        
        // Réactiver le bouton
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Envoyer';
    })
    .catch(error => {
        console.error('❌ Erreur:', error);
        alert('Une erreur est survenue.');
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Envoyer';
    });
}

// ===== FONCTION UTILITAIRE: ÉCHAPPER HTML (sécurité) =====
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ===== FERMER LE CHAT AVEC LA TOUCHE ÉCHAP =====
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fermerChat();
    }
});