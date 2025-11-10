<!----------------------------------RATIING----------------------------------------------->
<?php
session_start();
$_SESSION['user_id'] = 1;
require __DIR__ . '/../DBConfig.php';
header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$game_id = (int)($_POST['game_id'] ?? 0);
$rating  = (int)($_POST['rating']  ?? -1);

if ($user_id <= 0 || $game_id <= 0 || $rating < 0 || $rating > 100) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad params']); exit;
}

$pdo->prepare("
  INSERT INTO ratings (user_id, game_id, rating) VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE rating=VALUES(rating), updated_at=CURRENT_TIMESTAMP
")->execute([$user_id, $game_id, $rating]);

echo json_encode(['ok'=>true,'rating'=>$rating]);
?>
<!----------------------------------FAVORIS----------------------------------------------->
<?php
session_start();
$_SESSION['user_id'] = 1;
require __DIR__ . '/../DBConfig.php';
header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$game_id = (int)($_POST['game_id'] ?? 0);

if ($user_id <= 0 || $game_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad params']); exit;
}

$stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND game_id=?");
$stmt->execute([$user_id, $game_id]);

if ($stmt->fetch()) {
  $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND game_id=?")->execute([$user_id, $game_id]);
  echo json_encode(['ok'=>true,'favori'=>false]); // enlevé
} else {
  $pdo->prepare("INSERT INTO favorites (user_id, game_id) VALUES (?, ?)")->execute([$user_id, $game_id]);
  echo json_encode(['ok'=>true,'favori'=>true]); // ajouté
}
?>
<!---------------------------------COMMANTAIRE-LIST------------------------------------------------->
<?php
require __DIR__ . '/../DBConfig.php';
header('Content-Type: application/json');

$game_id = (int)($_GET['game_id'] ?? 0);
if ($game_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad params']); exit;
}

$stmt = $pdo->prepare("
  SELECT id, user_id, content, created_at
  FROM comments
  WHERE game_id=?
  ORDER BY id DESC
  LIMIT 100
");
$stmt->execute([$game_id]);
echo json_encode($stmt->fetchAll());
?>
<!---------------------------------COMMANTAIRE-CREATE------------------------------------------------>
<?php
session_start();
$_SESSION['user_id'] = 1;
require __DIR__ . '/../DBConfig.php';
header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$game_id = (int)($_POST['game_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($user_id <= 0 || $game_id <= 0 || $content === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad params']); exit;
}

if (mb_strlen($content) > 2000) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'too long']); exit;
}

$pdo->prepare("INSERT INTO comments (user_id, game_id, content) VALUES (?, ?, ?)")
    ->execute([$user_id, $game_id, $content]);

echo json_encode(['ok'=>true]);
?>
<!---------------------------------BACKLOG------------------------------------------------->
<?php
session_start();
$_SESSION['user_id'] = 1;
require __DIR__ . '/../DBConfig.php';
header('Content-Type: application/json');

$user_id = (int)($_SESSION['user_id'] ?? 0);
$game_id = (int)($_POST['game_id'] ?? 0);

if ($user_id <= 0 || $game_id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'bad params']); exit;
}

$stmt = $pdo->prepare("SELECT 1 FROM backlog WHERE user_id=? AND game_id=?");
$stmt->execute([$user_id, $game_id]);

if ($stmt->fetch()) {
  $pdo->prepare("DELETE FROM backlog WHERE user_id=? AND game_id=?")->execute([$user_id, $game_id]);
  echo json_encode(['ok'=>true,'todo'=>false]);
} else {
  $pdo->prepare("INSERT INTO backlog (user_id, game_id) VALUES (?, ?)")->execute([$user_id, $game_id]);
  echo json_encode(['ok'=>true,'todo'=>true]);
}
?>