<?php
// ==========================================
// FICHIER : captcha_manager.php
// BUT : Interface de gestion captcha (à inclure dans ADMIN.php)
// ==========================================

// Définir où se trouve le fichier JSON
const JSON_FILE_CAPTCHA = __DIR__ . '/../PAGE/captcha_bank.json';

// ====================
// FONCTIONS
// ====================

function load_bank_captcha() {
  if (!file_exists(JSON_FILE_CAPTCHA)) return [];
  $txt = file_get_contents(JSON_FILE_CAPTCHA);
  $arr = json_decode($txt, true);
  return is_array($arr) ? $arr : [];
}

function save_bank_captcha($arr) {
  file_put_contents(JSON_FILE_CAPTCHA, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Charger les questions
$bank = load_bank_captcha();
$msg = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'] ?? '';
  
  // ACTION : Ajouter une question
  if ($action === 'add') {
    $q = trim($_POST['q'] ?? '');
    $a = trim($_POST['a'] ?? '');
    
    if ($q !== '' && $a !== '') {
      $bank[] = ['q' => $q, 'a' => $a, 'enabled' => true];
      save_bank_captcha($bank);
      $msg = "✅ Question ajoutée avec succès !";
    } else {
      $msg = "⚠️ Merci de remplir la question ET la réponse.";
    }
  } 
  // ACTION : Supprimer une question
  elseif ($action === 'delete') {
    $i = (int)($_POST['idx'] ?? -1);
    if (isset($bank[$i])) {
      array_splice($bank, $i, 1);
      save_bank_captcha($bank);
      $msg = "🗑️ Question supprimée.";
    }
  } 
  // ACTION : Activer/désactiver une question
  elseif ($action === 'toggle') {
    $i = (int)($_POST['idx'] ?? -1);
    if (isset($bank[$i])) {
      $bank[$i]['enabled'] = !$bank[$i]['enabled'];
      save_bank_captcha($bank);
      $msg = "🔄 Statut modifié.";
    }
  }
  
  // Recharger les questions après modification
  $bank = load_bank_captcha();
}
?>

<!-- Contenu de l'onglet Captcha -->
<section class="admin-surface">
  <div class="card" style="padding: 30px;">
    
    <!-- Titre -->
    <h1 style="color: #667eea; margin-bottom: 20px; font-size: 28px;">
      🔒 Gestion des questions Captcha
    </h1>

    <!-- Message de succès/erreur -->
    <?php if ($msg): ?>
      <div style="background: #51cf66; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <!-- Boîte d'information -->
    <div style="background: #e7f5ff; border-left: 4px solid #667eea; padding: 15px; margin-bottom: 30px; border-radius: 5px;">
      <strong>ℹ️ Information :</strong> Pour accepter plusieurs réponses valides, séparez-les par le caractère <code>|</code><br>
      <strong>Exemple :</strong> <code>paris|Paris|PARIS</code>
    </div>

    <!-- Formulaire pour ajouter une question -->
    <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 40px;">
      <h2 style="color: #333; margin: 0 0 20px 0; font-size: 20px;">➕ Ajouter une question</h2>
      <form method="post" action="?tab=captcha">
        <input type="hidden" name="action" value="add">
        
        <div style="margin-bottom: 20px;">
          <label for="question" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">
            Question
          </label>
          <input type="text" 
                 id="question" 
                 name="q" 
                 placeholder="Ex: Quelle est la capitale de la France ?" 
                 required
                 style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
        </div>
        
        <div style="margin-bottom: 20px;">
          <label for="answer" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">
            Réponse(s) acceptée(s)
          </label>
          <input type="text" 
                 id="answer" 
                 name="a" 
                 placeholder="Ex: paris (ou paris|Paris pour plusieurs variantes)" 
                 required
                 style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
        </div>
        
        <button type="submit" 
                style="background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 16px;">
          Ajouter la question
        </button>
      </form>
    </div>

    <!-- Liste des questions existantes -->
    <h2 style="color: #333; margin: 40px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #667eea; font-size: 20px;">
      📋 Questions existantes (<?= count($bank) ?>)
    </h2>
    
    <?php if (empty($bank)): ?>
      <p style="text-align: center; color: #999; padding: 60px 0; font-size: 18px;">
        Aucune question enregistrée pour le moment.
      </p>
    <?php else: ?>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
          <thead>
            <tr>
              <th style="padding: 15px; text-align: left; background: #667eea; color: white; border-bottom: 1px solid #ddd; width: 50px;">#</th>
              <th style="padding: 15px; text-align: left; background: #667eea; color: white; border-bottom: 1px solid #ddd;">Question</th>
              <th style="padding: 15px; text-align: left; background: #667eea; color: white; border-bottom: 1px solid #ddd; width: 200px;">Réponse(s)</th>
              <th style="padding: 15px; text-align: left; background: #667eea; color: white; border-bottom: 1px solid #ddd; width: 100px;">Statut</th>
              <th style="padding: 15px; text-align: left; background: #667eea; color: white; border-bottom: 1px solid #ddd; width: 220px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bank as $i => $row): ?>
              <tr style="transition: background 0.2s;">
                <td style="padding: 15px; border-bottom: 1px solid #ddd;"><?= $i + 1 ?></td>
                <td style="padding: 15px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($row['q']) ?></td>
                <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                  <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 14px;">
                    <?= htmlspecialchars($row['a']) ?>
                  </code>
                </td>
                <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                  <?php if (!empty($row['enabled'])): ?>
                    <span style="color: #51cf66; font-weight: bold;">✓ Active</span>
                  <?php else: ?>
                    <span style="color: #ff6b6b; font-weight: bold;">✗ Désactivée</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 15px; border-bottom: 1px solid #ddd;">
                  <!-- Bouton Activer/Désactiver -->
                  <form method="post" action="?tab=captcha" style="display:inline;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="idx" value="<?= $i ?>">
                    <button type="submit" 
                            style="background: #ffa500; color: white; padding: 8px 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin-right: 5px; transition: background 0.2s;">
                      <?= !empty($row['enabled']) ? 'Désactiver' : 'Activer' ?>
                    </button>
                  </form>
                  
                  <!-- Bouton Supprimer -->
                  <form method="post" action="?tab=captcha" style="display:inline;" onsubmit="return confirm('Supprimer cette question ?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="idx" value="<?= $i ?>">
                    <button type="submit" 
                            style="background: #ff6b6b; color: white; padding: 8px 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                      Supprimer
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</section>

<style>
  /* Effets au survol pour le tableau */
  table tbody tr:hover {
    background: #f8f9fa !important;
  }
  
  /* Effets au survol pour les boutons */
  button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }
  
  /* Style pour les inputs au focus */
  input[type="text"]:focus {
    outline: none;
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
</style>