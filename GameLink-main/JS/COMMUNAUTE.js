// ========================================
// JAVASCRIPT PAGE COMMUNAUTE
// Version super simple pour debutant
// ========================================

// Variables globales (boites pour stocker des infos)
let groupeActuel = null;
let minuteur = null;

// Quand la page est chargee
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargee !');
    
    // Trouver tous les boutons Rejoindre
    let boutonsRejoindre = document.querySelectorAll('.rejoindre-groupe');
    for (let i = 0; i < boutonsRejoindre.length; i++) {
        boutonsRejoindre[i].addEventListener('click', rejoindreGroupe);
    }
    
    // Trouver tous les boutons Ouvrir le chat
    let boutonsChat = document.querySelectorAll('.ouvrir-chat');
    for (let i = 0; i < boutonsChat.length; i++) {
        boutonsChat[i].addEventListener('click', ouvrirChat);
    }
    
    // Trouver tous les boutons Quitter
    let boutonsQuitter = document.querySelectorAll('.quitter-groupe');
    for (let i = 0; i < boutonsQuitter.length; i++) {
        boutonsQuitter[i].addEventListener('click', quitterGroupe);
    }
    
    // Bouton Quitter dans la bulle
    let boutonQuitterChat = document.querySelector('.quitter-depuis-chat');
    if (boutonQuitterChat) {
        boutonQuitterChat.addEventListener('click', function() {
            if (groupeActuel) {
                quitterGroupe({ target: { dataset: { groupeId: groupeActuel } } });
            }
        });
    }
    
    // Formulaire pour envoyer un message
    let formulaire = document.getElementById('formulaire-message');
    if (formulaire) {
        formulaire.addEventListener('submit', envoyerMessage);
    }
});

// FONCTION : Rejoindre un groupe
function rejoindreGroupe(evenement) {
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    
    console.log('Rejoindre groupe', idGroupe);
    
    bouton.textContent = 'Chargement...';
    bouton.disabled = true;
    
    fetch('/INCLUDES/groupe_join.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        console.log('Reponse:', data);
        
        if (data.success) {
            console.log('Groupe rejoint !');
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
            bouton.textContent = '+ Rejoindre';
            bouton.disabled = false;
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion');
        bouton.textContent = '+ Rejoindre';
        bouton.disabled = false;
    });
}

// FONCTION : Quitter un groupe
function quitterGroupe(evenement) {
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    
    console.log('Quitter groupe', idGroupe);
    
    fetch('/INCLUDES/groupe_leave.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        console.log('Reponse:', data);
        
        if (data.success) {
            console.log('Groupe quitte !');
            
            if (groupeActuel === idGroupe) {
                fermerChat();
            }
            
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion');
    });
}

// FONCTION : Ouvrir le chat
function ouvrirChat(evenement) {
    let bouton = evenement.target;
    let idGroupe = bouton.dataset.groupeId;
    let nomGroupe = bouton.dataset.groupeNom;
    
    console.log('Ouverture chat pour', nomGroupe);
    
    groupeActuel = idGroupe;
    
    document.getElementById('nom-groupe-chat').textContent = nomGroupe;
    document.getElementById('id-groupe-actuel').value = idGroupe;
    
    document.getElementById('bulle-chat').classList.add('ouverte');
    document.getElementById('fond-sombre').classList.add('visible');
    
    chargerMessages(idGroupe);
    
    if (minuteur) {
        clearInterval(minuteur);
    }
    minuteur = setInterval(function() {
        chargerMessages(idGroupe);
    }, 5000);
}

// FONCTION : Fermer le chat
function fermerChat() {
    console.log('Fermeture chat');
    
    document.getElementById('bulle-chat').classList.remove('ouverte');
    document.getElementById('fond-sombre').classList.remove('visible');
    
    if (minuteur) {
        clearInterval(minuteur);
        minuteur = null;
    }
    
    groupeActuel = null;
}

// FONCTION : Charger les messages
function chargerMessages(idGroupe) {
    console.log('Chargement messages groupe', idGroupe);
    
    let zoneMessages = document.getElementById('zone-messages');
    
    fetch('/INCLUDES/group_messages.php?groupe_id=' + idGroupe)
        .then(function(reponse) {
            if (!reponse.ok) {
                throw new Error('Erreur serveur');
            }
            return reponse.json();
        })
        .then(function(data) {
            console.log('Messages recus:', data);
            
            if (data.success) {
                afficherMessages(data.messages);
            } else {
                zoneMessages.innerHTML = '<p class="texte-centre">' + data.message + '</p>';
            }
        })
        .catch(function(erreur) {
            console.error('Erreur:', erreur);
            zoneMessages.innerHTML = '<p class="texte-centre">Erreur de chargement</p>';
        });
}

// FONCTION : Afficher les messages
function afficherMessages(messages) {
    let zoneMessages = document.getElementById('zone-messages');
    
    if (!messages || messages.length === 0) {
        zoneMessages.innerHTML = '<p class="texte-centre">Aucun message. Sois le premier !</p>';
        return;
    }
    
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
    zoneMessages.scrollTop = zoneMessages.scrollHeight;
}

// FONCTION : Envoyer un message
function envoyerMessage(evenement) {
    evenement.preventDefault();
    
    let idGroupe = document.getElementById('id-groupe-actuel').value;
    let champMessage = document.getElementById('mon-message');
    let message = champMessage.value.trim();
    
    if (!message) {
        alert('Ecris un message avant d\'envoyer !');
        return;
    }
    
    console.log('Envoi message:', message);
    
    let bouton = evenement.target.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    
    fetch('/INCLUDES/group_message.php', {
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
        console.log('Reponse:', data);
        
        if (data.success) {
            console.log('Message envoye !');
            champMessage.value = '';
            chargerMessages(idGroupe);
        } else {
            alert('Erreur : ' + data.message);
        }
        
        bouton.disabled = false;
        bouton.textContent = 'Envoyer';
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur d\'envoi');
        bouton.disabled = false;
        bouton.textContent = 'Envoyer';
    });
}

// FONCTION : Nettoyer le texte (securite)
function nettoyerTexte(texte) {
    let div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

// Fermer avec la touche Echap
document.addEventListener('keydown', function(evenement) {
    if (evenement.key === 'Escape') {
        fermerChat();
    }
});