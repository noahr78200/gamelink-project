// =====================================================
// JAVASCRIPT DE LA PAGE COMMUNAUTÉ - VERSION SIMPLE
// =====================================================
// 
// Ce fichier fait marcher tous les boutons !
// 
// =====================================================

// ===== VARIABLES =====
// (Des boîtes pour stocker des informations)

let groupeActuel = null;  // Le groupe où je discute actuellement
let minuteur = null;       // Pour recharger les messages automatiquement

// ===== QUAND LA PAGE EST CHARGÉE =====
// Attendre que tout soit prêt avant de faire marcher les boutons

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ La page est chargée !');
    
    // Trouver tous les boutons "Rejoindre" et leur dire quoi faire quand on clique
    let boutonsRejoindre = document.querySelectorAll('.rejoindre-groupe');
    boutonsRejoindre.forEach(function(bouton) {
        bouton.addEventListener('click', rejoindreGroupe);
    });
    
    // Trouver tous les boutons "Ouvrir le chat"
    let boutonsChat = document.querySelectorAll('.ouvrir-chat');
    boutonsChat.forEach(function(bouton) {
        bouton.addEventListener('click', ouvrirChat);
    });
    
    // Trouver tous les boutons "Quitter"
    let boutonsQuitter = document.querySelectorAll('.quitter-groupe');
    boutonsQuitter.forEach(function(bouton) {
        bouton.addEventListener('click', quitterGroupe);
    });
    
    // Le bouton "Quitter" dans la bulle de chat
    let boutonQuitterChat = document.querySelector('.quitter-depuis-chat');
    if (boutonQuitterChat) {
        boutonQuitterChat.addEventListener('click', function() {
            if (groupeActuel) {
                quitterGroupe({ target: { dataset: { groupeId: groupeActuel } } });
            }
        });
    }
    
    // Le formulaire pour envoyer un message
    let formulaire = document.getElementById('formulaire-message');
    if (formulaire) {
        formulaire.addEventListener('submit', envoyerMessage);
    }
});

// ===== FONCTION: REJOINDRE UN GROUPE =====
// Quand tu cliques sur "Rejoindre"

function rejoindreGroupe(evenement) {
    // Trouver quel groupe tu veux rejoindre
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    
    console.log('🔵 Je veux rejoindre le groupe numéro', idGroupe);
    
    // Changer le texte du bouton pendant le chargement
    bouton.textContent = 'Chargement...';
    bouton.disabled = true;  // Désactiver le bouton
    
    // Envoyer une demande au serveur
    fetch('../INCLUDES/groupe_join.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe
    })
    .then(function(reponse) {
        // Transformer la réponse en JSON (format compréhensible)
        return reponse.json();
    })
    .then(function(data) {
        console.log('Réponse du serveur:', data);
        
        if (data.success) {
            // ✅ Ça a marché !
            console.log('✅ Groupe rejoint !');
            location.reload();  // Recharger la page
        } else {
            // ❌ Ça n'a pas marché
            alert('Erreur : ' + data.message);
            bouton.textContent = '+ Rejoindre';
            bouton.disabled = false;
        }
    })
    .catch(function(erreur) {
        // S'il y a un problème de connexion
        console.error('❌ Erreur:', erreur);
        alert('Impossible de se connecter au serveur');
        bouton.textContent = '+ Rejoindre';
        bouton.disabled = false;
    });
}

// ===== FONCTION: QUITTER UN GROUPE =====
// Quand tu cliques sur "Quitter"

function quitterGroupe(evenement) {
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    
    console.log('🔴 Je veux quitter le groupe numéro', idGroupe);
    
    // PAS DE CONFIRMATION - On quitte directement
    
    // Envoyer la demande au serveur
    fetch('../INCLUDES/groupe_leave.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        console.log('Réponse du serveur:', data);
        
        if (data.success) {
            console.log('✅ Groupe quitté !');
            
            // Si on était dans le chat, le fermer
            if (groupeActuel === idGroupe) {
                fermerChat();
            }
            
            location.reload();  // Recharger la page
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(function(erreur) {
        console.error('❌ Erreur:', erreur);
        alert('Impossible de se connecter au serveur');
    });
}

// ===== FONCTION: OUVRIR LE CHAT =====
// Quand tu cliques sur "Ouvrir le chat"

function ouvrirChat(evenement) {
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    let nomGroupe = bouton.dataset.groupeNom;
    
    console.log('💬 Ouverture du chat pour', nomGroupe);
    
    // Sauvegarder le groupe actuel
    groupeActuel = idGroupe;
    
    // Mettre le nom du groupe dans la bulle
    document.getElementById('nom-groupe-chat').textContent = nomGroupe;
    document.getElementById('id-groupe-actuel').value = idGroupe;
    
    // Afficher la bulle et le fond sombre
    document.getElementById('bulle-chat').classList.add('ouverte');
    document.getElementById('fond-sombre').classList.add('visible');
    
    // Charger les messages
    chargerMessages(idGroupe);
    
    // Recharger les messages automatiquement toutes les 5 secondes
    if (minuteur) {
        clearInterval(minuteur);  // Arrêter l'ancien minuteur
    }
    minuteur = setInterval(function() {
        chargerMessages(idGroupe);
    }, 5000);  // 5000 millisecondes = 5 secondes
}

// ===== FONCTION: FERMER LE CHAT =====
// Quand tu cliques sur le X ou sur le fond sombre

function fermerChat() {
    console.log('❌ Fermeture du chat');
    
    // Cacher la bulle et le fond sombre
    document.getElementById('bulle-chat').classList.remove('ouverte');
    document.getElementById('fond-sombre').classList.remove('visible');
    
    // Arrêter le minuteur
    if (minuteur) {
        clearInterval(minuteur);
        minuteur = null;
    }
    
    groupeActuel = null;
}

// ===== FONCTION: CHARGER LES MESSAGES =====
// Va chercher les messages sur le serveur

function chargerMessages(idGroupe) {
    console.log('📥 Chargement des messages du groupe', idGroupe);
    
    let zoneMessages = document.getElementById('zone-messages');
    
    // Demander les messages au serveur
    fetch('../INCLUDES/groupe_messages.php?groupe_id=' + idGroupe)
        .then(function(reponse) {
            if (!reponse.ok) {
                throw new Error('Erreur serveur');
            }
            return reponse.json();
        })
        .then(function(data) {
            console.log('Messages reçus:', data);
            
            if (data.success) {
                // ✅ Afficher les messages
                afficherMessages(data.messages);
            } else {
                // ❌ Erreur
                zoneMessages.innerHTML = '<p class="texte-centre">⚠️ ' + data.message + '</p>';
            }
        })
        .catch(function(erreur) {
            console.error('❌ Erreur:', erreur);
            zoneMessages.innerHTML = '<p class="texte-centre">❌ Impossible de charger les messages</p>';
        });
}

// ===== FONCTION: AFFICHER LES MESSAGES =====
// Met les messages dans la bulle

function afficherMessages(messages) {
    let zoneMessages = document.getElementById('zone-messages');
    
    // S'il n'y a pas de messages
    if (!messages || messages.length === 0) {
        zoneMessages.innerHTML = '<p class="texte-centre">📭 Aucun message pour le moment.<br>Sois le premier à écrire !</p>';
        return;
    }
    
    // Créer le HTML pour chaque message
    let html = '';
    for (let i = 0; i < messages.length; i++) {
        let msg = messages[i];
        html += '<div class="message">';
        html += '  <div class="message-haut">';
        html += '    <span class="message-auteur">' + nettoyerTexte(msg.pseudo) + '</span>';
        html += '    <span class="message-heure">' + nettoyerTexte(msg.heure) + '</span>';
        html += '  </div>';
        html += '  <div class="message-texte">' + nettoyerTexte(msg.contenu) + '</div>';
        html += '</div>';
    }
    
    zoneMessages.innerHTML = html;
    
    // Scroller en bas pour voir le dernier message
    zoneMessages.scrollTop = zoneMessages.scrollHeight;
}

// ===== FONCTION: ENVOYER UN MESSAGE =====
// Quand tu cliques sur "Envoyer"

function envoyerMessage(evenement) {
    evenement.preventDefault();  // Empêcher la page de recharger
    
    let idGroupe = document.getElementById('id-groupe-actuel').value;
    let champMessage = document.getElementById('mon-message');
    let message = champMessage.value.trim();  // Enlever les espaces avant/après
    
    // Vérifier que le message n'est pas vide
    if (!message) {
        alert('Écris un message avant d\'envoyer !');
        return;
    }
    
    console.log('📤 Envoi du message:', message);
    
    // Trouver le bouton et le désactiver
    let bouton = evenement.target.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    
    // Envoyer le message au serveur
    fetch('../INCLUDES/groupe_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe + '&message=' + encodeURIComponent(message)
    })
    .then(function(reponse) {
        if (!reponse.ok) {
            throw new Error('Erreur serveur');
        }
        return reponse.json();
    })
    .then(function(data) {
        console.log('Réponse:', data);
        
        if (data.success) {
            // ✅ Message envoyé !
            console.log('✅ Message envoyé !');
            champMessage.value = '';  // Vider le champ
            chargerMessages(idGroupe);  // Recharger les messages
        } else {
            // ❌ Erreur
            alert('Erreur : ' + data.message);
        }
        
        // Réactiver le bouton
        bouton.disabled = false;
        bouton.textContent = 'Envoyer';
    })
    .catch(function(erreur) {
        console.error('❌ Erreur:', erreur);
        alert('Impossible d\'envoyer le message');
        bouton.disabled = false;
        bouton.textContent = 'Envoyer';
    });
}

// ===== FONCTION: NETTOYER LE TEXTE =====
// Éviter les problèmes de sécurité (enlever les balises HTML)

function nettoyerTexte(texte) {
    let div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

// ===== FERMER AVEC LA TOUCHE ÉCHAP =====
// Pratique pour fermer rapidement !

document.addEventListener('keydown', function(evenement) {
    if (evenement.key === 'Escape') {
        fermerChat();
    }
});