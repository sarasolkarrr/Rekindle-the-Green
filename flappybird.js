// flappybird.js — Rekindle the Green: Keoladeo Bird Sanctuary
// Drop-in compatible with ImKennyYip/flappy-bird image assets:
//   flappybird.png, toppipe.png, bottompipe.png

(function () {
    const board = document.getElementById("board");
    if (!board) return;
    const ctx = board.getContext("2d");

    // ── Dimensions ──────────────────────────────────────────────────────────
    const W = 360, H = 640;
    board.width = W;
    board.height = H;

    // ── Asset loading ────────────────────────────────────────────────────────
    function tryImg(src) {
        const img = new Image();
        img.src = src;
        return img;
    }
    const birdImg = tryImg("flappybird.png");
    const topImg = tryImg("toppipe.png");
    const bottomImg = tryImg("bottompipe.png");
    const bgImg = tryImg("keoladeo-bg.jpg"); // optional; falls back to drawn bg

    // ── Bird ─────────────────────────────────────────────────────────────────
    const BIRD_W = 34, BIRD_H = 24;
    const START_X = W / 8, START_Y = H / 2;
    let bird = { x: START_X, y: START_Y, w: BIRD_W, h: BIRD_H };

    // ── Pipes ─────────────────────────────────────────────────────────────────
    const PIPE_W = 64;
    const PIPE_H = 512;
    const GAP = 160;
    const PIPE_VX = -2.4;
    let pipes = [];

    // ── Physics ───────────────────────────────────────────────────────────────
    const GRAVITY = 0.38;
    const FLAP_V = -6.5;
    let vy = 0;

    // ── State ─────────────────────────────────────────────────────────────────
    let score = 0;
    let best = parseInt(localStorage.getItem("rtg_bird_best") || "0", 10);
    let started = false;
    let over = false;
    let pipeTimer = null;

    // ── Draw helpers ──────────────────────────────────────────────────────────
    function imgReady(img) { return img.complete && img.naturalWidth > 0; }

    function drawBg() {
        if (imgReady(bgImg)) {
            ctx.drawImage(bgImg, 0, 0, W, H);
        } else {
            // Sky gradient
            const sky = ctx.createLinearGradient(0, 0, 0, H);
            sky.addColorStop(0, "#87CEEB");
            sky.addColorStop(0.65, "#b8e4f0");
            sky.addColorStop(1, "#7bbf62");
            ctx.fillStyle = sky;
            ctx.fillRect(0, 0, W, H);
            // Ground stripe
            ctx.fillStyle = "#5a8a3a";
            ctx.fillRect(0, H - 30, W, 30);
        }
    }

    function drawBird() {
        ctx.save();
        // Tilt bird body with velocity
        const angle = Math.min(Math.max(vy * 0.04, -0.4), 1.2);
        ctx.translate(bird.x + bird.w / 2, bird.y + bird.h / 2);
        ctx.rotate(angle);
        if (imgReady(birdImg)) {
            ctx.drawImage(birdImg, -bird.w / 2, -bird.h / 2, bird.w, bird.h);
        } else {
            // Painted stork / crane silhouette drawn with canvas
            // Body
            ctx.fillStyle = "#f5f5f0";
            ctx.beginPath();
            ctx.ellipse(0, 0, bird.w / 2, bird.h / 2, 0, 0, Math.PI * 2);
            ctx.fill();
            // Wing flash (blue-grey)
            ctx.fillStyle = "#8aaec0";
            ctx.beginPath();
            ctx.ellipse(-2, 2, bird.w / 2.5, bird.h / 3.5, 0.3, 0, Math.PI * 2);
            ctx.fill();
            // Red crown
            ctx.fillStyle = "#e03030";
            ctx.beginPath();
            ctx.ellipse(bird.w / 2 - 4, -bird.h / 2 + 2, 5, 4, 0, 0, Math.PI * 2);
            ctx.fill();
            // Beak
            ctx.fillStyle = "#c8a020";
            ctx.fillRect(bird.w / 2 - 2, -2, 10, 3);
            // Eye
            ctx.fillStyle = "#1a1a1a";
            ctx.beginPath();
            ctx.arc(bird.w / 2 - 5, -3, 2, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.restore();
    }

    function drawPipe(pipe) {
        if (pipe.isTop) {
            if (imgReady(topImg)) {
                // Top pipe: flip vertically so the opening faces down
                ctx.save();
                ctx.scale(1, -1);
                ctx.drawImage(topImg, pipe.x, -(pipe.y + pipe.h), PIPE_W, PIPE_H);
                ctx.restore();
            } else {
                ctx.fillStyle = "#4a8a2a";
                ctx.fillRect(pipe.x, pipe.y, PIPE_W, pipe.h);
                // Cap
                ctx.fillStyle = "#3a7a1a";
                ctx.fillRect(pipe.x - 4, pipe.y + pipe.h - 20, PIPE_W + 8, 20);
            }
        } else {
            if (imgReady(bottomImg)) {
                ctx.drawImage(bottomImg, pipe.x, pipe.y, PIPE_W, PIPE_H);
            } else {
                ctx.fillStyle = "#4a8a2a";
                ctx.fillRect(pipe.x, pipe.y, PIPE_W, pipe.h);
                // Cap
                ctx.fillStyle = "#3a7a1a";
                ctx.fillRect(pipe.x - 4, pipe.y, PIPE_W + 8, 20);
            }
        }
    }

    function drawScore() {
        ctx.textAlign = "center";
        ctx.font = "bold 40px 'Segoe UI', sans-serif";
        ctx.fillStyle = "rgba(255,255,255,0.9)";
        ctx.strokeStyle = "rgba(0,0,0,0.4)";
        ctx.lineWidth = 3;
        ctx.strokeText(Math.floor(score), W / 2, 56);
        ctx.fillText(Math.floor(score), W / 2, 56);
    }

    function drawStartScreen() {
        drawBg();
        drawBird();
        ctx.fillStyle = "rgba(0,0,0,0.38)";
        ctx.fillRect(0, 0, W, H);
        ctx.fillStyle = "#fff";
        ctx.font = "bold 28px 'Segoe UI', sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Flappy Sarus Crane", W / 2, H / 2 - 30);
        ctx.font = "16px 'Segoe UI', sans-serif";
        ctx.fillStyle = "rgba(255,255,255,0.85)";
        ctx.fillText("Press Space / tap to flap", W / 2, H / 2 + 10);
        if (best > 0) {
            ctx.font = "14px 'Segoe UI', sans-serif";
            ctx.fillStyle = "#ffd770";
            ctx.fillText("Best: " + best, W / 2, H / 2 + 40);
        }
    }

    function drawGameOver() {
        ctx.fillStyle = "rgba(0,0,0,0.52)";
        ctx.fillRect(0, 0, W, H);

        const bx = W / 2 - 120, by = H / 2 - 90, bw = 240, bh = 180;
        ctx.fillStyle = "rgba(255,255,255,0.95)";
        ctx.beginPath();
        ctx.roundRect(bx, by, bw, bh, 16);
        ctx.fill();

        ctx.fillStyle = "#1f2a22";
        ctx.font = "bold 26px 'Segoe UI', sans-serif";
        ctx.textAlign = "center";
        ctx.fillText("Game Over", W / 2, by + 44);

        ctx.font = "18px 'Segoe UI', sans-serif";
        ctx.fillText("Score: " + Math.floor(score), W / 2, by + 80);

        if (Math.floor(score) >= best) {
            ctx.fillStyle = "#c9a84c";
            ctx.font = "bold 14px 'Segoe UI', sans-serif";
            ctx.fillText("🏆 New Best!", W / 2, by + 108);
        } else {
            ctx.fillStyle = "#888";
            ctx.font = "13px 'Segoe UI', sans-serif";
            ctx.fillText("Best: " + best, W / 2, by + 108);
        }

        ctx.fillStyle = "#4a8a2a";
        ctx.font = "14px 'Segoe UI', sans-serif";
        ctx.fillText("Space / tap to play again", W / 2, by + 148);
    }

    // ── Collision ─────────────────────────────────────────────────────────────
    function hits(a, p) {
        return (
            a.x + 4 < p.x + p.w &&
            a.x + a.w - 4 > p.x &&
            a.y + 4 < p.y + p.h &&
            a.y + a.h - 4 > p.y
        );
    }

    // ── Pipe spawner ──────────────────────────────────────────────────────────
    function spawnPipes() {
        if (over || !started) return;
        const topH = Math.floor(Math.random() * (H / 2 - GAP / 2 - 60)) + 40;
        const botY = topH + GAP;
        pipes.push({ x: W, y: 0, w: PIPE_W, h: topH, isTop: true, passed: false });
        pipes.push({ x: W, y: botY, w: PIPE_W, h: H - botY, isTop: false, passed: false });
    }

    // ── Main loop ─────────────────────────────────────────────────────────────
    function loop() {
        ctx.clearRect(0, 0, W, H);

        if (!started) { drawStartScreen(); requestAnimationFrame(loop); return; }

        drawBg();

        // Physics
        vy += GRAVITY;
        bird.y += vy;

        // Floor / ceiling
        if (bird.y + bird.h >= H - 30 || bird.y <= 0) {
            endGame();
            drawGameOver();
            return;
        }

        // Pipes
        for (let i = pipes.length - 1; i >= 0; i--) {
            const p = pipes[i];
            p.x += PIPE_VX;
            drawPipe(p);

            // Score when bird passes pipe pair (only count top pipe)
            if (p.isTop && !p.passed && bird.x > p.x + PIPE_W) {
                p.passed = true;
                score += 1;
                document.dispatchEvent(new CustomEvent("rtg:score", { detail: Math.floor(score) }));
            }

            if (hits(bird, p)) { endGame(); drawBird(); drawGameOver(); return; }

            // Remove off-screen
            if (p.x + PIPE_W < 0) pipes.splice(i, 1);
        }

        drawBird();
        drawScore();

        requestAnimationFrame(loop);
    }

    // ── Game control ──────────────────────────────────────────────────────────
    function flap() {
        if (over) { resetGame(); return; }
        if (!started) {
            started = true;
            pipeTimer = setInterval(spawnPipes, 1600);
        }
        vy = FLAP_V;
    }

    function endGame() {
        over = true;
        clearInterval(pipeTimer);
        if (Math.floor(score) > best) {
            best = Math.floor(score);
            try { localStorage.setItem("rtg_bird_best", best); } catch (_) { }
        }
        // Show info overlay after short delay
        setTimeout(() => {
            const ov = document.getElementById("overlay");
            if (ov) ov.style.display = "flex";
        }, 900);
    }

    function resetGame() {
        bird.y = START_Y;
        pipes = [];
        vy = 0;
        score = 0;
        over = false;
        started = false;
        const ov = document.getElementById("overlay");
        if (ov) ov.style.display = "none";
        requestAnimationFrame(loop);
    }

    // ── Input ─────────────────────────────────────────────────────────────────
    document.addEventListener("keydown", (e) => {
        if (["Space", "ArrowUp", "KeyX"].includes(e.code)) { e.preventDefault(); flap(); }
    });
    board.addEventListener("click", () => flap());
    board.addEventListener("touchstart", (e) => { e.preventDefault(); flap(); }, { passive: false });

    // Score display outside canvas (optional span)
    document.addEventListener("rtg:score", (e) => {
        const el = document.getElementById("score");
        if (el) el.textContent = e.detail;
    });

    // Overlay close
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("closeOverlay");
        if (btn) btn.addEventListener("click", resetGame);
    });

    // ── Kick off ──────────────────────────────────────────────────────────────
    requestAnimationFrame(loop);
})();
