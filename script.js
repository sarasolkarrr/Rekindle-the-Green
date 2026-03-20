const raincontainer = document.getElementById("raincontainer");
const numberOfRaindrops = 200;
function crd() {
    const rd = document.createElement("div");
    rd.className = "raindrop";

    const left = Math.random() * 100;
    const width = Math.random() * 2 + 1;
    const height = Math.random() * 50 + 70;
    const duration = 2;
    const delay = Math.random();
    rd.style.left = left + "%";
    rd.style.width = width + "px";
    rd.style.height = height + "px";
    rd.style.animationDuration = duration + "s";
    rd.style.animationDelay = delay + "s";
    return rd;
}
function rain() {
    raincontainer.innerHTML = "";

    for (let i = 0; i < numberOfRaindrops; i++) {
        raincontainer.appendChild(crd());
    }
    setTimeout(() => {
        raincontainer.innerHTML = "";
        document.body.style.backgroundImage = "url(jimc.png)";
            document.body.innerHTML += `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(128, 128, 128, 0.7);
                background: 
                padding: 120px 30px 40px;
                box-sizing: border-box;
                overflow-y: auto;
                border-radius: 10px;
                text-align: center;
            ">
                <div style="background-color : rgba(220, 231, 213, 0.51); margin-top: 200px;border-radius: 4px width: 80%">
                <h2 style="text-align: center; margin-top : 55px">Forest Fire Impact</h2>
                <p style="font-size: 20px;">
                Forest fires in Jim Corbett National Park are a common problem, especially during the dry summer months when leaves and grass become highly flammable.
                Many of these fires are caused by human activities like carelessness, burning waste, or illegal entry into forest areas, though natural causes can also occur. 
                One exmaple of these forest fires is the 2016 Uttarakhand forest fire that spread to approximately 4,538 hectares (11,210 acres) of forest land and resulted in nine fatalities
                To manage forest fires,the forest officials use methods like controlled burning in winter to reduce dry vegetation and prevent larger fires. 
                Rain being a natural suppresant of these fires can only be available seasonally so in serious situations, help from the Indian Air Force is taken, where helicopters are used to drop water using Bambi buckets.
                These efforts are important to protect wildlife, forests, and the overall ecosystem of the park.
                </p>
                <img src="forestfirer.jpeg" style="width:500px; height:380px;">
                <p style="text-align:center">Helicopters being used to supress the forest fire</p>
                </div>
                
                <h2>
            </div>
        `;
    }, 4000);
}

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
    watercontainer.innerHTML = "";

    for (let i = 0; i < numberOfDrops; i++) {
        watercontainer.appendChild(createWater());
    }

    setTimeout(() => {
        watercontainer.innerHTML = "";
        seedcontainer.innerHTML = "";

        document.body.style.backgroundImage = "url('barren.jpg')";
        document.body.style.backgroundSize = "cover";
        document.body.style.backgroundPosition = "center";

        document.body.onclick = function () {
            document.body.onclick = null;

            document.body.innerHTML = `
            <header class="headerl">
                <h1>Gir National Park</h1>
            </header>
            <div style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(216,205,205,0.9);
            padding: 120px 30px;
            text-align: center;
            overflow-y: auto;
            box-sizing: border-box;
        ">
                <h2>Forest Restoration</h2>
                <p style="font-size: 20px">
                Afforestation in Gir National Park focuses on restoring degraded forest areas and strengthening habitats for wildlife, especially the endangered Asiatic lion. 
                Planting native species like teak, acacia, and neem helps maintain the natural ecosystem and provides food and shelter for animals. 
                These efforts also prevent soil erosion and improve water retention in the region’s dry climate. 
                Local communities are actively involved through awareness programs and tree-planting drives, promoting sustainable practices. 
                The government and forest department use scientific planning to ensure the right species are planted in suitable areas. 
                Afforestation also helps reduce the impact of forest fires and climate change. 
                Over time, increased green cover supports biodiversity, including birds, deer, and other wildlife. It also improves air quality and contributes to ecological balance. Continuous monitoring ensures young plants survive and grow into healthy forests. Overall, afforestation in Gir plays a vital role in conserving wildlife and maintaining environmental stability.

                </p>
                <img src="seeding.jpg" style="width:400px; border-radius:10px;">
            </div>
            `;
        };

    }, 2000);
}