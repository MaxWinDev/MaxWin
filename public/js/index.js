const cache = {};

class Symbol {
    constructor(name = Symbol.random()) {
        this.name = name;

        if (cache[name]) {
            this.img = cache[name].cloneNode();
        } else {
            this.img = new Image();
            this.img.src = `../assets/symbols/${name}.png`;

            cache[name] = this.img;
        }
    }

    static preload() {
        Symbol.symbols.forEach((symbol) => new Symbol(symbol));
    }

    static get symbols() {
        return [
            "7",
            "cerise",
            "citron",
            "fraise",
            "gold",
            "max",
            "pasteque",
            "prune",
        ];
    }

    static random() {
        return this.symbols[Math.floor(Math.random() * this.symbols.length)];
    }
}

class Reel {
    constructor(reelContainer, idx, initialSymbols) {
        this.reelContainer = reelContainer;
        this.idx = idx;

        this.symbolContainer = document.createElement("div");
        this.symbolContainer.classList.add("icons");
        this.reelContainer.appendChild(this.symbolContainer);

        this.animation = this.symbolContainer.animate(
            [
                // We cannot animate translateY & filter at the same time in safari for some reasons,
                // so we go with animating top & filter instead.
                { top: 0, filter: "blur(0)" },
                { filter: "blur(2px)", offset: 0.5 },
                {
                    top: `calc((${Math.floor(this.factor) * 10} / 3) * -100% - (${
                        Math.floor(this.factor) * 10
                    } * 3px))`,

                    filter: "blur(0)",
                },
            ],
            {
                duration: this.factor * 1000,
                easing: "ease-in-out",
            }
        );
        this.animation.cancel();

        initialSymbols.forEach((symbol) =>
            this.symbolContainer.appendChild(new Symbol(symbol).img)
        );
    }

    get factor() {
        return 1 + Math.pow(this.idx / 2, 2);
    }

    renderSymbols(nextSymbols) {
        const fragment = document.createDocumentFragment();

        for (let i = 3; i < 3 + Math.floor(this.factor) * 10; i++) {
            const icon = new Symbol(
                i >= 10 * Math.floor(this.factor) - 2
                    ? nextSymbols[i - Math.floor(this.factor) * 10]
                    : undefined
            );
            fragment.appendChild(icon.img);
        }

        this.symbolContainer.appendChild(fragment);
    }

    spin() {
        const animationPromise = new Promise(
            (resolve) => (this.animation.onfinish = resolve)
        );
        const timeoutPromise = new Promise((resolve) =>
            setTimeout(resolve, this.factor * 1000)
        );

        this.animation.cancel();
        this.animation.play();

        return Promise.race([animationPromise, timeoutPromise]).then(() => {
            if (this.animation.playState !== "finished") this.animation.finish();

            const max = this.symbolContainer.children.length - 3;

            for (let i = 0; i < max; i++) {
                this.symbolContainer.firstChild.remove();
            }
        });
    }
}

class Slot {
    constructor(domElement, config = {}) {
        Symbol.preload();

        this.currentSymbols = [
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
        ];

        this.nextSymbols = [
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
            ["max", "max", "max"],
        ];

        if (!domElement || !domElement instanceof HTMLElement) {
            console.error("L'élément DOM passé en argument est invalide.");
            return;
        }

        this.container = domElement;

        this.reels = Array.from(this.container.getElementsByClassName("reel")).map(
            (reelContainer, idx) =>
                new Reel(reelContainer, idx, this.currentSymbols[idx])
        );

        this.spinButton = this.container.querySelector("#spin-button");
        this.spinButton.addEventListener("click", () => this.spin());

        this.autoSpinButton = this.container.querySelector("#auto_spin_button");
        this.autoSpinButton.addEventListener("click", () => this.toggleAutoSpin());

        if (config.inverted) {
            this.container.classList.add("inverted");
        }

        this.config = config;
    }

    spin() {
        this.currentSymbols = this.nextSymbols;
        this.nextSymbols = [
            [Symbol.random(), Symbol.random(), Symbol.random()],
            [Symbol.random(), Symbol.random(), Symbol.random()],
            [Symbol.random(), Symbol.random(), Symbol.random()],
            [Symbol.random(), Symbol.random(), Symbol.random()],
            [Symbol.random(), Symbol.random(), Symbol.random()],
        ];

        this.onSpinStart(this.nextSymbols);

        return Promise.all(
            this.reels.map((reel) => {
                reel.renderSymbols(this.nextSymbols[reel.idx]);
                return reel.spin();
            })
        ).then(() => this.onSpinEnd(this.nextSymbols));
    }

    onSpinStart(symbols) {
        this.spinButton.disabled = true;

        this.config.onSpinStart?.(symbols);
    }

    toggleAutoSpin() {
        // Inversez l'état de l'auto-spin et mettez à jour le texte du bouton
        this.autoSpinEnabled = !this.autoSpinEnabled;
        this.autoSpinButton.style.backgroundColor = this.autoSpinEnabled ? "#ff4136" : "#45a049";

        // Si l'auto-spin est activé, déclenchez la fonction de spin automatique
        if (this.autoSpinEnabled) {
            this.spin();
        }
    }

    onSpinEnd(symbols) {
        this.spinButton.disabled = false;

        this.config.onSpinEnd?.(symbols);

        if (this.autoSpinEnabled) {
            return window.setTimeout(() => this.spin(), 200);
        }
    }
}

const config = {
    inverted: false, // true: reels spin from top to bottom; false: reels spin from bottom to top
    onSpinStart: (symbols) => {
        console.log("onSpinStart", symbols);
    },
    onSpinEnd: (symbols) => {
        console.log("onSpinEnd", symbols);
    },
};

document.addEventListener("DOMContentLoaded", function() {
    const slot = new Slot(document.getElementById("slot"), config);
});

