let score = 0;
let isAnimating = false;

const turtle = document.getElementById('turtle');
const scoreDisplay = document.getElementById('score');
const overlay = document.getElementById('overlay');
const closeOverlayBtn = document.getElementById('closeOverlay');

function moveTurtle() {
    const x = Math.random() * (document.querySelector('.game-area').clientWidth - 100);
    const y = Math.random() * (document.querySelector('.game-area').clientHeight - 100);
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

turtle.addEventListener('click', () => {
    score++;
    scoreDisplay.textContent = score;

    if (score === 1) {
        overlay.style.display = 'flex'; // Show overlay when score is 5
    } else {
        animateTurtle();
    }
});

closeOverlayBtn.addEventListener('click', () => {
    // Redirect to the project main index
    window.location.href = '../index.html';
});

// Move turtle for the first time on page load without animation
moveTurtle();
