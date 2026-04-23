let score = 0;
let isAnimating = false;
let gameOver = false;

const turtle = document.getElementById('turtle');
const scoreDisplay = document.getElementById('scoreNum');
const gamePage = document.getElementById('gamePage');
const infoPage = document.getElementById('infoPage');
const targetScore = 8;

function moveTurtle() {
    if (gameOver) return;
    const gameArea = document.querySelector('.game-area');
    const x = Math.random() * (gameArea.clientWidth - 100);
    const y = Math.random() * (gameArea.clientHeight - 100);
    turtle.style.left = `${x}px`;
    turtle.style.top = `${y}px`;
}

function animateTurtle() {
    if (isAnimating || gameOver) return;
    isAnimating = true;

    turtle.style.transform = 'translateY(300px)';

    setTimeout(() => {
        turtle.style.transform = 'translateY(0)';
        isAnimating = false;
        moveTurtle();
    }, 500);
}

function showInfoPage() {
    gamePage.style.display = 'none';
    infoPage.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function restartGame() {
    score = 0;
    scoreDisplay.textContent = '0';
    isAnimating = false;
    gameOver = false;
    turtle.style.transform = 'translateY(0)';
    gamePage.style.display = 'block';
    infoPage.style.display = 'none';
    moveTurtle();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

turtle.addEventListener('click', () => {
    if (gameOver) return;

    score++;
    scoreDisplay.textContent = score;

    if (score >= targetScore) {
        gameOver = true;
        setTimeout(showInfoPage, 500);
    } else {
        animateTurtle();
    }
});

window.restartGame = restartGame;

moveTurtle();

