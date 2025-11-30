let groupeActuel = null;
let minuteur = null;
let discussionActuelle = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargee !');
    
    let boutonsRejoindre = document.querySelectorAll('.rejoindre-groupe');
    for (let i = 0; i < boutonsRejoindre.length; i++) {
        boutonsRejoindre[i].addEventListener('click', rejoindreGroupe);
    }
    
    let boutonsChat = document.querySelectorAll('.ouvrir-chat');
    for (let i = 0; i < boutonsChat.length; i++) {
        boutonsChat[i].addEventListener('click', ouvrirChat);
    }
    
    let boutonsQuitter = document.querySelectorAll('.quitter-groupe');
    for (let i = 0; i < boutonsQuitter.length; i++) {
        boutonsQuitter[i].addEventListener('click', quitterGroupe);
    }
    
    let boutonQuitterChat = document.querySelector('.quitter-depuis-chat');
    if (boutonQuitterChat) {
        boutonQuitterChat.addEventListener('click', function() {
            if (groupeActuel) {
                quitterGroupe({ target: { dataset: { groupeId: groupeActuel } } });
            }
        });
    }
    
    let formulaire = document.getElementById('formulaire-message');
    if (formulaire) {
        formulaire.addEventListener('submit', envoyerMessage);
    }
    
    let formCreer = document.getElementById('form-creer-discussion');
    if (formCreer) {
        formCreer.addEventListener('submit', creerDiscussion);
    }
    
    let formRepondre = document.getElementById('form-repondre-discussion');
    if (formRepondre) {
        formRepondre.addEventListener('submit', repondreDiscussion);
    }
    
    let fondSombre = document.getElementById('fond-sombre');
    if (fondSombre) {
        fondSombre.addEventListener('click', function() {
            fermerChat();
            fermerPopupCreerDiscussion();
            fermerDiscussion();
        });
    }
});

function afficherOnglet(nomOnglet) {
    let tousOnglets = document.querySelectorAll('.contenu-onglet');
    for (let i = 0; i < tousOnglets.length; i++) {
        tousOnglets[i].classList.remove('actif');
    }
    
    let tousBoutons = document.querySelectorAll('.onglet');
    for (let i = 0; i < tousBoutons.length; i++) {
        tousBoutons[i].classList.remove('actif');
    }
    
    if (nomOnglet === 'groupes') {
        document.getElementById('onglet-groupes').classList.add('actif');
        tousBoutons[0].classList.add('actif');
    } else if (nomOnglet === 'forum') {
        document.getElementById('onglet-forum').classList.add('actif');
        tousBoutons[1].classList.add('actif');
    }
}

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
        if (data.success) {
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
        if (data.success) {
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

function fermerChat() {
    document.getElementById('bulle-chat').classList.remove('ouverte');
    document.getElementById('fond-sombre').classList.remove('visible');
    
    if (minuteur) {
        clearInterval(minuteur);
        minuteur = null;
    }
    
    groupeActuel = null;
}

function chargerMessages(idGroupe) {
    let zoneMessages = document.getElementById('zone-messages');
    
    fetch('/INCLUDES/group_messages.php?groupe_id=' + idGroupe)
        .then(function(reponse) {
            if (!reponse.ok) {
                throw new Error('Erreur serveur');
            }
            return reponse.json();
        })
        .then(function(data) {
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

function afficherMessages(messages) {
    let zoneMessages = document.getElementById('zone-messages');
    
    if (!messages || messages.length === 0) {
        zoneMessages.innerHTML = '<p class="texte-centre">Aucun message.</p>';
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

function envoyerMessage(evenement) {
    evenement.preventDefault();
    
    let idGroupe = document.getElementById('id-groupe-actuel').value;
    let champMessage = document.getElementById('mon-message');
    let message = champMessage.value.trim();
    
    if (!message) {
        alert('Ecris un message !');
        return;
    }
    
    let bouton = evenement.target.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    
    fetch('/INCLUDES/group_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=' + idGroupe + '&message=' + encodeURIComponent(message)
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        if (data.success) {
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

function ouvrirPopupCreerDiscussion() {
    document.getElementById('popup-creer-discussion').classList.add('visible');
    document.getElementById('fond-sombre').classList.add('visible');
}

function fermerPopupCreerDiscussion() {
    document.getElementById('popup-creer-discussion').classList.remove('visible');
    document.getElementById('fond-sombre').classList.remove('visible');
    document.getElementById('form-creer-discussion').reset();
}

function creerDiscussion(evenement) {
    evenement.preventDefault();
    
    let titre = document.getElementById('discussion-titre').value.trim();
    let contenu = document.getElementById('discussion-contenu').value.trim();
    
    if (!titre || !contenu) {
        alert('Remplis tous les champs !');
        return;
    }
    
    let bouton = evenement.target.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Publication...';
    
    fetch('/INCLUDES/forum_create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'titre=' + encodeURIComponent(titre) + '&contenu=' + encodeURIComponent(contenu)
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
            bouton.disabled = false;
            bouton.textContent = 'Publier';
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur de connexion');
        bouton.disabled = false;
        bouton.textContent = 'Publier';
    });
}

function ouvrirDiscussion(idDiscussion) {
    discussionActuelle = idDiscussion;
    
    document.getElementById('popup-voir-discussion').classList.add('visible');
    document.getElementById('fond-sombre').classList.add('visible');
    
    chargerDiscussion(idDiscussion);
}

function fermerDiscussion() {
    document.getElementById('popup-voir-discussion').classList.remove('visible');
    document.getElementById('fond-sombre').classList.remove('visible');
    document.getElementById('form-repondre-discussion').reset();
    discussionActuelle = null;
}

function chargerDiscussion(idDiscussion) {
    fetch('/INCLUDES/forum_get.php?discussion_id=' + idDiscussion)
        .then(function(reponse) {
            return reponse.json();
        })
        .then(function(data) {
            if (data.success) {
                afficherDiscussion(data.discussion, data.reponses);
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(function(erreur) {
            console.error('Erreur:', erreur);
            alert('Erreur de chargement');
        });
}

function afficherDiscussion(discussion, reponses) {
    document.getElementById('discussion-titre-complet').textContent = discussion.titre;
    
    let htmlPost = '<div class="post-header">';
    htmlPost += '<span class="post-auteur">' + nettoyerTexte(discussion.auteur) + '</span>';
    htmlPost += '<span class="post-date">' + discussion.date + '</span>';
    htmlPost += '</div>';
    htmlPost += '<div class="post-contenu">' + nettoyerTexte(discussion.contenu) + '</div>';
    
    if (discussion.est_auteur) {
        htmlPost += '<div class="post-actions">';
        htmlPost += '<button class="bouton rouge" onclick="supprimerDiscussion(' + discussion.id + ')">Supprimer</button>';
        htmlPost += '</div>';
    }
    
    document.getElementById('discussion-post-original').innerHTML = htmlPost;
    
    let htmlReponses = '';
    if (reponses.length === 0) {
        htmlReponses = '<p class="texte-centre">Aucune reponse. Sois le premier !</p>';
    } else {
        for (let i = 0; i < reponses.length; i++) {
            let rep = reponses[i];
            htmlReponses += '<div class="reponse">';
            htmlReponses += '<div class="reponse-header">';
            htmlReponses += '<span class="reponse-auteur">' + nettoyerTexte(rep.auteur) + '</span>';
            htmlReponses += '<span class="reponse-date">' + rep.date + '</span>';
            htmlReponses += '</div>';
            htmlReponses += '<div class="reponse-contenu">' + nettoyerTexte(rep.contenu) + '</div>';
            
            if (rep.est_auteur) {
                htmlReponses += '<div class="reponse-actions">';
                htmlReponses += '<button class="bouton rouge" onclick="supprimerReponse(' + rep.id + ')">Supprimer</button>';
                htmlReponses += '</div>';
            }
            
            htmlReponses += '</div>';
        }
    }
    
    document.getElementById('discussion-reponses').innerHTML = htmlReponses;
}

function repondreDiscussion(evenement) {
    evenement.preventDefault();
    
    let contenu = document.getElementById('reponse-contenu').value.trim();
    
    if (!contenu) {
        alert('Ecris une reponse !');
        return;
    }
    
    let bouton = evenement.target.querySelector('button[type="submit"]');
    bouton.disabled = true;
    bouton.textContent = 'Envoi...';
    
    fetch('/INCLUDES/forum_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'discussion_id=' + discussionActuelle + '&contenu=' + encodeURIComponent(contenu)
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        if (data.success) {
            document.getElementById('reponse-contenu').value = '';
            chargerDiscussion(discussionActuelle);
        } else {
            alert('Erreur : ' + data.message);
        }
        
        bouton.disabled = false;
        bouton.textContent = 'Repondre';
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur d\'envoi');
        bouton.disabled = false;
        bouton.textContent = 'Repondre';
    });
}

function supprimerDiscussion(idDiscussion) {
    if (!confirm('Supprimer cette discussion ?')) {
        return;
    }
    
    fetch('/INCLUDES/forum_delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'discussion_id=' + idDiscussion
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur');
    });
}

function supprimerReponse(idReponse) {
    if (!confirm('Supprimer cette reponse ?')) {
        return;
    }
    
    fetch('/INCLUDES/forum_delete_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reponse_id=' + idReponse
    })
    .then(function(reponse) {
        return reponse.json();
    })
    .then(function(data) {
        if (data.success) {
            chargerDiscussion(discussionActuelle);
        } else {
            alert('Erreur : ' + data.message);
        }
    })
    .catch(function(erreur) {
        console.error('Erreur:', erreur);
        alert('Erreur');
    });
}

function nettoyerTexte(texte) {
    let div = document.createElement('div');
    div.textContent = texte;
    return div.innerHTML;
}

document.addEventListener('keydown', function(evenement) {
    if (evenement.key === 'Escape') {
        fermerChat();
        fermerPopupCreerDiscussion();
        fermerDiscussion();
    }
});