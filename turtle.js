let score = 0;
let isAnimating = false;

const turtle = document.getElementById('turtle');
const scoreDisplay = document.getElementById('score');
const overlay = document.getElementById('overlay');
const closeOverlayBtn = document.getElementById('closeOverlay');

function moveTurtle() {
    const gameArea = document.querySelector('.game-area');
    const x = Math.random() * (gameArea.clientWidth - 100);
    const y = Math.random() * (gameArea.clientHeight - 100);
    turtle.style.left = `${x}px`;
    turtle.style.top = `${y}px`;
}

function animateTurtle() {
    if (isAnimating) return;
    isAnimating = true;

    turtle.style.transform = 'translateY(300px)';

    setTimeout(() => {
        turtle.style.transform = 'translateY(0)';
        isAnimating = false;
        moveTurtle();
    }, 500);
}

function resetGame() {
    score = 0;
    scoreDisplay.textContent = '0';
    overlay.style.display = 'none';
    isAnimating = false;
    turtle.style.transform = 'translateY(0)';
    moveTurtle();
}

turtle.addEventListener('click', () => {
    score++;
    scoreDisplay.textContent = score;

    if (score >= 5) {
        overlay.style.display = 'flex';
    } else {
        animateTurtle();
    }
});

closeOverlayBtn.addEventListener('click', resetGame);

// Position turtle on first load
moveTurtle();
