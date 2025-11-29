<?php
// ==========================================
// FICHIER : API/newsletter_send.php
// BUT : Envoyer la newsletter (test ou à tous)
// ==========================================

session_start();

// 1) Sécurité : réservé aux admins
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// 2) Connexion à la base (pour récupérer les emails des joueurs si besoin)
require_once __DIR__ . '/../DATA/DBConfig.php';

// 3) Chargement de PHPMailer (version "sans Composer")
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Archive un mail envoyé dans le dossier "Envoyés" du compte OVH
 */



// 4) Récupération des données du formulaire
$subject    = trim($_POST['subject'] ?? '');
$title      = trim($_POST['title'] ?? '');
$body       = trim($_POST['body'] ?? '');
$testEmail  = trim($_POST['test_email'] ?? '');
$action     = $_POST['action'] ?? '';  // "test" ou "all"

$errors = [];

// Vérifications de base
if ($subject === '' || $title === '' || $body === '') {
    $errors[] = "Merci de remplir l'objet, le titre et le corps du mail.";
}

if (!in_array($action, ['test', 'all'], true)) {
    $errors[] = "Action de newsletter invalide.";
}

// Pour l'envoi de test, l'email de test est obligatoire
if ($action === 'test') {
    if ($testEmail === '' || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email de test invalide.";
    }
}

if ($errors) {
    $_SESSION['flash_newsletter'] = ['errors' => $errors];
    header('Location: ../PAGE/ADMIN.php?tab=edition');
    exit;
}

// 5) Fonction utilitaire pour créer/configurer un PHPMailer
function buildMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    // --- SMTP OVH ZIMBRA ---
    $mail->isSMTP();
    $mail->Host       = 'ssl0.ovh.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no_reply@gamelink.ovh'; 
    $mail->Password   = 'Gamelink-project25';   // ton mot de passe Zimbra
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port       = 465;

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    // From officiel OVH
    $mail->setFrom('no_reply@gamelink.ovh', 'GameLink Newsletter');
    $mail->Sender = 'no_reply@gamelink.ovh'; // important

    return $mail;
}


// 6) Construire le HTML de l'email
$htmlBody = '
  <html>
    <body style="font-family: Arial, sans-serif; background-color:#f4f4f4; padding:20px;">
      <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;padding:20px;">
        <h1 style="color:#333333;">' . htmlspecialchars($title) . '</h1>
        <p style="color:#555555;white-space:pre-line;">' . nl2br(htmlspecialchars($body)) . '</p>
        <hr style="margin-top:30px;margin-bottom:10px;border:none;border-top:1px solid #dddddd;">
        <p style="font-size:12px;color:#999999;">
          Vous recevez cet email car vous avez un compte sur GameLink.
        </p>
      </div>
    </body>
  </html>
';

// 7) Envoi
try {
    if ($action === 'test') {
        // -------- ENVOI D'UN EMAIL DE TEST --------
        $mail = buildMailer();
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->addAddress($testEmail);

        $mail->send();
         


        $_SESSION['flash_newsletter'] = [
            'success' => "Email de test envoyé à $testEmail"
        ];
    } else {
        // -------- ENVOI À TOUS LES JOUEURS --------
        // Tu peux affiner la requête (opt-in newsletter, statut actif, etc.)
        $stmt = $pdo->query("SELECT email FROM joueur WHERE email IS NOT NULL AND email <> ''");
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($emails)) {
            $_SESSION['flash_newsletter'] = [
                'errors' => ["Aucun email valide trouvé dans la base des joueurs."]
            ];
            header('Location: ../PAGE/ADMIN.php?tab=edition');
            exit;
        }

        $mail = buildMailer();
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        // Adresse principale “technique”
        $mail->addAddress('no-reply@gamelink.fr', 'GameLink');

        // Tous les joueurs en BCC (copie cachée)
        foreach ($emails as $em) {
            $mail->addBCC($em);
        }

        $mail->send();

        $_SESSION['flash_newsletter'] = [
            'success' => "Newsletter envoyée à " . count($emails) . " joueur(s)."
        ];
    }
} catch (Exception $e) {
    $_SESSION['flash_newsletter'] = [
        'errors' => [
            "Erreur lors de l'envoi de la newsletter : " . $e->getMessage()
        ]
    ];
}

// 8) Retour vers l'onglet Edition
header('Location: ../PAGE/ADMIN.php?tab=edition');
exit;
