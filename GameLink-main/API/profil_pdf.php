

<?php
session_start();

require_once __DIR__ . '/../DATA/DBConfig.php';
require_once __DIR__ . '/../INCLUDES/avatar_svg.php';

if (!isset($_SESSION['user_id'])) {
    die("Non connecté.");
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT pseudo, email, bio, avatar_config FROM joueur WHERE id_joueur = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable.");
}

$wkhtmltopdf = '/usr/local/bin/wkhtmltopdf';

$html  = "<html><head><meta charset='utf-8'>";
$html .= "<style>
body { font-family: Arial; padding: 20px; }
h1 { color: #333; }
p { font-size: 14px; }
.avatar { margin-bottom: 20px; }
</style></head><body>";

$html .= "<h1>Profil GameLink</h1>";
$html .= "<div class='avatar'>" . generateAvatarSVG($user['avatar_config'], 120) . "</div>";
$html .= "<p><strong>Pseudo :</strong> " . htmlspecialchars($user['pseudo']) . "</p>";
$html .= "<p><strong>Email :</strong> " . htmlspecialchars($user['email']) . "</p>";
$html .= "<p><strong>Bio :</strong><br>" . nl2br(htmlspecialchars($user['bio'])) . "</p>";

$html .= "</body></html>";

$tmpHtml = tempnam(sys_get_temp_dir(), 'html_') . ".html";
$tmpPdf  = tempnam(sys_get_temp_dir(), 'pdf_') . ".pdf";

file_put_contents($tmpHtml, $html);

$cmd = "$wkhtmltopdf $tmpHtml $tmpPdf";
exec($cmd);

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=profil_gamelink.pdf");
header("Content-Length: " . filesize($tmpPdf));

readfile($tmpPdf);

unlink($tmpHtml);
unlink($tmpPdf);

exit;
?>