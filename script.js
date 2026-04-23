




let seedcontainer;
let watercontainer;

const numberOfSeeds = 80;
const numberOfDrops = 150;

window.onload = function () {
    seedcontainer = document.getElementById("seedcontainer");
    watercontainer = document.getElementById("watercontainer");
};

function createSeed() {
    const s = document.createElement("div");
    s.className = "seed";

    const left = Math.random() * 100;
    const delay = Math.random();

    s.style.left = left + "%";
    s.style.animationDelay = delay + "s";

    return s;
}

function seed() {
    if (!seedcontainer) return;
    seedcontainer.innerHTML = "";

    for (let i = 0; i < numberOfSeeds; i++) {
        seedcontainer.appendChild(createSeed());
    }
}

function createWater() {
    const wd = document.createElement("div");
    wd.className = "waterdrop";

    const left = Math.random() * 100;
    const duration = 1.5;
    const delay = Math.random();

    wd.style.left = left + "%";
    wd.style.animationDuration = duration + "s";
    wd.style.animationDelay = delay + "s";

    return wd;
}

function water() {
    if (!watercontainer) return;
    watercontainer.innerHTML = "";

    for (let i = 0; i < numberOfDrops; i++) {
        watercontainer.appendChild(createWater());
    }

    setTimeout(() => {
        watercontainer.innerHTML = "";
        if (seedcontainer) seedcontainer.innerHTML = "";

        document.body.style.backgroundImage = "url('barren.jpg')";
        document.body.style.backgroundSize = "cover";
        document.body.style.backgroundPosition = "center";
    }, 2000);
}

