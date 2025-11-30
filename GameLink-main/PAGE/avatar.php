<?php
session_start();

require_once __DIR__ . '/../DATA/DBConfig.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT avatar_config FROM joueur WHERE id_joueur = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$avatar_config = $user['avatar_config'] ?? null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer mon Avatar | GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #E9D5FF, #DBEAFE);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: #1F2937;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        h2 {
            color: #374151;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        h3 {
            color: #6B7280;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .avatar-preview {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        #avatar-svg {
            width: 250px;
            height: 250px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-green {
            background: #10B981;
            color: white;
        }

        .btn-green:hover {
            background: #059669;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }

        .color-btn {
            width: 100%;
            height: 50px;
            border: 4px solid #D1D5DB;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .color-btn:hover {
            transform: scale(1.05);
        }

        .color-btn.selected {
            border-color: #3B82F6;
            transform: scale(1.1);
        }

        .choice-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }

        .choice-btn {
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #F3F4F6;
            color: #374151;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .choice-btn:hover {
            background: #E5E7EB;
        }

        .choice-btn.selected {
            background: #3B82F6;
            color: white;
        }

        .btn img {
            height: 1.3em;
            width: 1.3em;
            vertical-align: text-bottom;
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../INCLUDES/header.php'; ?>

    <div class="container">
        <h1>Création d'Avatar</h1>

        <div class="main-content">
            <div class="card">
                <h2>Aperçu</h2>
                <div class="avatar-preview">
                    <svg id="avatar-svg" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="95" fill="#f0f0f0" />
                        <g id="hair-layer"></g>
                        <circle id="head" cx="100" cy="100" r="70" fill="#F4C2A0" />
                        <g id="face-layer"></g>
                    </svg>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-green" onclick="saveAvatar()" id="save-btn">
                        <img src="../ICON/SVG/save.svg" alt="save logo"> Sauvegarder et retourner au profil
                    </button>
                </div>
            </div>

            <div class="card">
                <h2>Personnalisation</h2>

                <div>
                    <h3>Couleur de peau</h3>
                    <div class="color-grid" id="skin-colors"></div>
                </div>

                <div>
                    <h3>Type de visage</h3>
                    <div class="choice-grid" id="face-types"></div>
                </div>

                <div>
                    <h3>Style de cheveux</h3>
                    <div class="choice-grid" id="hair-styles"></div>
                </div>

                <div>
                    <h3>Couleur de cheveux</h3>
                    <div class="color-grid" id="hair-colors"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let config = <?= $avatar_config ? $avatar_config : 'null' ?> || {
            skinColor: '#F4C2A0',
            faceType: 'face1',
            hairStyle: 'style1',
            hairColor: '#4A3728'
        };

        const skinColors = [
            '#FFDFC4', '#F4C2A0', '#E8B692', '#D4A76A', 
            '#C68642', '#8D5524', '#6B4423', '#4A2F1A'
        ];

        const hairColors = [
            '#000000', '#4A3728', '#8B4513', '#D2691E',
            '#FFD700', '#FF6347', '#e35940ff', '#40E0D0'
        ];

        function drawFace1() {
            return `
                <circle cx="70" cy="90" r="8" fill="#000" />
                <circle cx="130" cy="90" r="8" fill="#000" />
                <circle cx="72" cy="88" r="3" fill="#FFF" />
                <circle cx="132" cy="88" r="3" fill="#FFF" />
                <path d="M 100 95 Q 95 110 100 115 Q 105 110 100 95" fill="none" stroke="#000" stroke-width="2" />
                <path d="M 80 130 Q 100 145 120 130" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round" />
            `;
        }

        function drawFace2() {
            return `
                <ellipse cx="70" cy="90" rx="10" ry="12" fill="#000" />
                <ellipse cx="130" cy="90" rx="10" ry="12" fill="#000" />
                <circle cx="72" cy="87" r="4" fill="#FFF" />
                <circle cx="132" cy="87" r="4" fill="#FFF" />
                <circle cx="100" cy="110" r="4" fill="#000" opacity="0.7" />
                <path d="M 75 130 Q 100 150 125 130" fill="none" stroke="#000" stroke-width="3" stroke-linecap="round" />
            `;
        }

        function drawFace3() {
            return `
                <ellipse cx="70" cy="88" rx="14" ry="6" fill="#000" />
                <ellipse cx="130" cy="88" rx="14" ry="6" fill="#000" />
                <circle cx="77" cy="87" r="4" fill="#FFF" />
                <circle cx="137" cy="87" r="4" fill="#FFF" />
                <line x1="100" y1="100" x2="100" y2="115" stroke="#000" stroke-width="2" />
                <path d="M 85 135 Q 100 140 115 135" fill="none" stroke="#000" stroke-width="2.5" stroke-linecap="round" />
            `;
        }

        function drawHair1(color) {
            return `
                <ellipse cx="100" cy="60" rx="85" ry="55" fill="${color}" />
                <rect x="15" y="50" width="170" height="40" fill="${color}" />
            `;
        }

        function drawHair2(color) {
            return `
                <ellipse cx="100" cy="55" rx="90" ry="60" fill="${color}" />
                <path d="M 20 80 Q 15 120 30 140 L 40 90 Z" fill="${color}" />
                <path d="M 180 80 Q 185 120 170 140 L 160 90 Z" fill="${color}" />
                <rect x="10" y="50" width="180" height="50" fill="${color}" />
            `;
        }

        function drawHair3(color) {
            return `
                <circle cx="100" cy="65" r="70" fill="${color}" />
                <circle cx="50" cy="70" r="45" fill="${color}" />
                <circle cx="150" cy="70" r="45" fill="${color}" />
                <circle cx="70" cy="40" r="35" fill="${color}" />
                <circle cx="130" cy="40" r="35" fill="${color}" />
            `;
        }

        function updateAvatar() {
            document.getElementById('head').setAttribute('fill', config.skinColor);

            const faceLayer = document.getElementById('face-layer');
            if (config.faceType === 'face1') {
                faceLayer.innerHTML = drawFace1();
            } else if (config.faceType === 'face2') {
                faceLayer.innerHTML = drawFace2();
            } else {
                faceLayer.innerHTML = drawFace3();
            }

            const hairLayer = document.getElementById('hair-layer');
            if (config.hairStyle === 'style1') {
                hairLayer.innerHTML = drawHair1(config.hairColor);
            } else if (config.hairStyle === 'style2') {
                hairLayer.innerHTML = drawHair2(config.hairColor);
            } else {
                hairLayer.innerHTML = drawHair3(config.hairColor);
            }
        }

        function createColorButtons() {
            const skinContainer = document.getElementById('skin-colors');
            skinColors.forEach(color => {
                const btn = document.createElement('button');
                btn.className = 'color-btn';
                btn.style.backgroundColor = color;
                if (color === config.skinColor) btn.classList.add('selected');
                btn.onclick = function() {
                    config.skinColor = color;
                    updateAvatar();
                    updateSelectedButtons();
                };
                skinContainer.appendChild(btn);
            });

            const hairContainer = document.getElementById('hair-colors');
            hairColors.forEach(color => {
                const btn = document.createElement('button');
                btn.className = 'color-btn';
                btn.style.backgroundColor = color;
                if (color === config.hairColor) btn.classList.add('selected');
                btn.onclick = function() {
                    config.hairColor = color;
                    updateAvatar();
                    updateSelectedButtons();
                };
                hairContainer.appendChild(btn);
            });
        }

        function createChoiceButtons() {
            const faceContainer = document.getElementById('face-types');
            ['face1', 'face2', 'face3'].forEach((face, index) => {
                const btn = document.createElement('button');
                btn.className = 'choice-btn';
                btn.textContent = 'Visage ' + (index + 1);
                if (face === config.faceType) btn.classList.add('selected');
                btn.onclick = function() {
                    config.faceType = face;
                    updateAvatar();
                    updateSelectedButtons();
                };
                faceContainer.appendChild(btn);
            });

            const hairStyleContainer = document.getElementById('hair-styles');
            ['style1', 'style2', 'style3'].forEach((style, index) => {
                const btn = document.createElement('button');
                btn.className = 'choice-btn';
                btn.textContent = 'Style ' + (index + 1);
                if (style === config.hairStyle) btn.classList.add('selected');
                btn.onclick = function() {
                    config.hairStyle = style;
                    updateAvatar();
                    updateSelectedButtons();
                };
                hairStyleContainer.appendChild(btn);
            });
        }

        function updateSelectedButtons() {
            document.querySelectorAll('#skin-colors .color-btn').forEach((btn, index) => {
                if (skinColors[index] === config.skinColor) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });

            document.querySelectorAll('#hair-colors .color-btn').forEach((btn, index) => {
                if (hairColors[index] === config.hairColor) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });

            document.querySelectorAll('#face-types .choice-btn').forEach((btn, index) => {
                const faces = ['face1', 'face2', 'face3'];
                if (faces[index] === config.faceType) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });

            document.querySelectorAll('#hair-styles .choice-btn').forEach((btn, index) => {
                const styles = ['style1', 'style2', 'style3'];
                if (styles[index] === config.hairStyle) {
                    btn.classList.add('selected');
                } else {
                    btn.classList.remove('selected');
                }
            });
        }

        function saveAvatar() {
            const btn = document.getElementById('save-btn');
            btn.textContent = 'Sauvegarde en cours...';
            btn.disabled = true;

            fetch('../INCLUDES/save_avatar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.textContent = 'Sauvegardé !';
                    setTimeout(() => {
                        window.location.href = 'PROFIL.php';
                    }, 1000);
                } else {
                    alert('Erreur lors de la sauvegarde');
                    btn.textContent = 'Sauvegarder et retourner au profil';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde');
                btn.textContent = 'Sauvegarder et retourner au profil';
                btn.disabled = false;
            });
        }

        createColorButtons();
        createChoiceButtons();
        updateAvatar();
    </script>
</body>
</html>