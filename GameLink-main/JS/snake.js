let snake = [{x: 10, y: 10}];
let food = {x: 15, y: 15};
let direction = 'right';
let score = 0;
let gameRunning = false;
let gameSpeed = 200;
let keys = '';

document.addEventListener('keydown', function(e) {
    keys = keys + e.key;
    
    if (keys.length > 4) {
        keys = keys.slice(-4);
    }
    
    if (keys === 'play') {
        openGame();
        keys = '';
    }
});

function openGame() {
    let gameDiv = document.getElementById('snakeGame');
    
    if (!gameDiv) {
        gameDiv = document.createElement('div');
        gameDiv.id = 'snakeGame';
        gameDiv.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: black;
                z-index: 99999;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
            ">
                <h1 style="color: lime; font-family: Arial; margin: 20px;">🐍 SNAKE GAME 🐍</h1>
                
                <div style="color: white; font-family: Arial; font-size: 20px; margin: 10px;">
                    Score: <span id="scoreDisplay">0</span>
                </div>
                
                <canvas id="gameCanvas" width="400" height="400" style="
                    border: 3px solid lime;
                    background: #111;
                    margin: 20px;
                "></canvas>
                
                <div style="color: white; font-family: Arial; margin: 10px;">
                    Utilise les flèches ⬆️⬇️⬅️➡️ pour jouer
                </div>
                
                <button onclick="startGame()" style="
                    background: lime;
                    color: black;
                    border: none;
                    padding: 15px 30px;
                    font-size: 18px;
                    font-weight: bold;
                    cursor: pointer;
                    border-radius: 5px;
                    margin: 10px;
                ">▶️ JOUER</button>
                
                <button onclick="closeGame()" style="
                    background: red;
                    color: white;
                    border: none;
                    padding: 15px 30px;
                    font-size: 18px;
                    font-weight: bold;
                    cursor: pointer;
                    border-radius: 5px;
                    margin: 10px;
                ">❌ FERMER</button>
            </div>
        `;
        document.body.appendChild(gameDiv);
        
        document.addEventListener('keydown', changeDirection);
    }
    
    gameDiv.style.display = 'block';
}

function closeGame() {
    let gameDiv = document.getElementById('snakeGame');
    if (gameDiv) {
        gameDiv.style.display = 'none';
        gameRunning = false;
    }
}

function startGame() {
    snake = [{x: 10, y: 10}];
    food = {x: 15, y: 15};
    direction = 'right';
    score = 0;
    gameRunning = true;
    
    document.getElementById('scoreDisplay').textContent = '0';
    
    gameLoop();
}

function changeDirection(e) {
    if (!gameRunning) return;
    
    if (e.key === 'ArrowUp' && direction !== 'down') {
        direction = 'up';
    }
    if (e.key === 'ArrowDown' && direction !== 'up') {
        direction = 'down';
    }
    if (e.key === 'ArrowLeft' && direction !== 'right') {
        direction = 'left';
    }
    if (e.key === 'ArrowRight' && direction !== 'left') {
        direction = 'right';
    }
}

function gameLoop() {
    if (!gameRunning) return;
    
    moveSnake();
    
    if (checkCollision()) {
        gameOver();
        return;
    }
    
    if (snake[0].x === food.x && snake[0].y === food.y) {
        score = score + 10;
        document.getElementById('scoreDisplay').textContent = score;
        placeFood();
    } else {
        snake.pop();
    }
    
    draw();
    
    setTimeout(gameLoop, gameSpeed);
}

function moveSnake() {
    let head = {x: snake[0].x, y: snake[0].y};
    
    if (direction === 'up') head.y = head.y - 1;
    if (direction === 'down') head.y = head.y + 1;
    if (direction === 'left') head.x = head.x - 1;
    if (direction === 'right') head.x = head.x + 1;
    
    snake.unshift(head);
}

function checkCollision() {
    let head = snake[0];
    
    if (head.x < 0 || head.x >= 20 || head.y < 0 || head.y >= 20) {
        return true;
    }
    
    for (let i = 1; i < snake.length; i++) {
        if (head.x === snake[i].x && head.y === snake[i].y) {
            return true;
        }
    }
    
    return false;
}

function placeFood() {
    food.x = Math.floor(Math.random() * 20);
    food.y = Math.floor(Math.random() * 20);
    
    for (let i = 0; i < snake.length; i++) {
        if (food.x === snake[i].x && food.y === snake[i].y) {
            placeFood();
            return;
        }
    }
}

function draw() {
    let canvas = document.getElementById('gameCanvas');
    if (!canvas) return;
    
    let ctx = canvas.getContext('2d');
    
    ctx.fillStyle = '#111';
    ctx.fillRect(0, 0, 400, 400);
    
    ctx.fillStyle = 'lime';
    for (let i = 0; i < snake.length; i++) {
        ctx.fillRect(
            snake[i].x * 20,
            snake[i].y * 20,
            18,
            18
        );
    }
    
    ctx.fillStyle = 'red';
    ctx.fillRect(
        food.x * 20,
        food.y * 20,
        18,
        18
    );
}

function gameOver() {
    gameRunning = false;
    
    let canvas = document.getElementById('gameCanvas');
    let ctx = canvas.getContext('2d');
    
    ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
    ctx.fillRect(0, 0, 400, 400);
    
    ctx.fillStyle = 'red';
    ctx.font = 'bold 40px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('GAME OVER', 200, 180);
    
    ctx.fillStyle = 'white';
    ctx.font = '25px Arial';
    ctx.fillText('Score: ' + score, 200, 220);
    
    ctx.font = '16px Arial';
    ctx.fillText('Clique sur JOUER pour recommencer', 200, 260);
    
    saveScore(score);
}

function saveScore(playerScore) {
    if (playerScore === 0) return;
    
    fetch('../API/snake_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'savescore',
            score: playerScore
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.isNewHighScore) {
            alert('🎉 NOUVEAU RECORD ! 🎉');
        }
    })
    .catch(err => console.log('Erreur:', err));
}

console.log('🐍 Tape "play" pour jouer au Snake !');