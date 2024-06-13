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

  static setProbabilities(probabilities) {
    this.probabilities = probabilities;
  }

  static random() {
    // Check if the probabilities are defined, if not set equals probabilities for each symbols
    const probabilities =
      this.probabilities ||
      this.symbols.reduce((acc, curr) => {
        acc[curr] = 1 / this.symbols.length;
        return acc;
      }, {});

    // Create a probability array to get the symbols after
    const cumulativeProbabilities = Object.values(probabilities).reduce(
      (acc, curr, i) => {
        acc[i] = (acc[i - 1] || 0) + curr;
        return acc;
      },
      []
    );

    const randomNumber = Math.random();
    const selectedSymbolIndex = cumulativeProbabilities.findIndex(
      (probability) => randomNumber < probability
    );

    return this.symbols[selectedSymbolIndex];
  }
}

Symbol.setProbabilities({
  7: 0.1,
  cerise: 0.15,
  citron: 0.15,
  fraise: 0.15,
  gold: 0.1,
  max: 0.05,
  pasteque: 0.15,
  prune: 0.15,
});

class Reel {
    constructor(reelContainer, idx, initialSymbols, fastSpinEnabled = false) {
        this.reelContainer = reelContainer;
        this.idx = idx;
        this.fastSpinEnabled = fastSpinEnabled;

        this.symbolContainer = document.createElement("div");
        this.symbolContainer.classList.add("icons");
        this.reelContainer.appendChild(this.symbolContainer);

        const duration = this.fastSpinEnabled ? 200 : this.factor * 1000;

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
                duration: duration,
                easing: "ease-in-out",
            }
        );
        this.animation.cancel();

        initialSymbols.forEach((symbol) =>
            this.symbolContainer.appendChild(new Symbol(symbol).img),
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
                new Reel(reelContainer, idx, this.currentSymbols[idx], false)
        );

        this.spinButton = this.container.querySelector("#spin_button");
        this.spinButton.addEventListener("click", () => this.spin());

        this.autoSpinButton = this.container.querySelector("#auto_spin_button");
        this.autoSpinButton.addEventListener("click", () => this.toggleAutoSpin());

        this.fastSpinButton = this.container.querySelector("#fast_spin_button");
        this.fastSpinButton.addEventListener("click", () => this.fastSpin());

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
    ).then(() => {
      this.checkForWin(this.nextSymbols), this.onSpinEnd(this.nextSymbols);
    });
  }

    onSpinStart(symbols) {
        this.spinButton.disabled = true;
        this.isSpinning = true;

    this.config.onSpinStart?.(symbols);
  }

    toggleAutoSpin() {
        this.autoSpinEnabled = !this.autoSpinEnabled;
        this.autoSpinButton.style.backgroundColor = this.autoSpinEnabled ? "#ff4136" : "#45a049";

        if (this.autoSpinEnabled && !this.isSpinning) {
            this.spin();
        }
    }

    fastSpin() {
        this.fastSpinEnabled = !this.fastSpinEnabled;
        this.fastSpinButton.style.backgroundColor = this.fastSpinEnabled ? "#ff4136" : "#45a049";

        if(this.fastSpinEnabled && !this.isSpinning) {
            this.reels = Array.from(this.container.getElementsByClassName("reel")).map(
                (reelContainer, idx) => {
                    reelContainer.innerHTML = "";
                    return new Reel(reelContainer, idx, this.currentSymbols[idx], this.fastSpinEnabled);
                }
            );
        } else {
            this.reels.forEach((reel) => {
                reel.animation.cancel();
                reel.animation.finish();
            });
            this.reels = Array.from(this.container.getElementsByClassName("reel")).map(
                (reelContainer, idx) => {
                    reelContainer.innerHTML = "";
                    return new Reel(reelContainer, idx, this.currentSymbols[idx], this.fastSpinEnabled);
                }
            );
        }
    }

    transformMatrix(matrix) {
        const numRows = matrix[0].length;
        const numCols = matrix.length;
        const transformed = Array.from({ length: numRows }, () => []);

        for (let col = 0; col < numCols; col++) {
            for (let row = numRows - 1; row >= 0; row--) {
                transformed[numRows - 1 - row].push(matrix[col][row]);
            }
        }

        return transformed;
    }

    onSpinEnd(symbols) {
        this.spinButton.disabled = false;
        this.isSpinning = false;

        this.config.onSpinEnd?.(symbols);

        // Transformation de la matrice pour avoir les symboles dans le bon ordre par colonnes et par lignes
        const transformedMatrix = this.transformMatrix(symbols);

        this.sendResults(transformedMatrix);

        if (this.autoSpinEnabled) {
            return window.setTimeout(() => this.spin(), 200);
        }
    }

    sendResults(symbols) {
      fetch('http://localhost:8000/game/check_wins', {
          credentials: 'include',
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ symbols: symbols }),
      })
          .then(response => {
              console.log('Réponse reçue:', response);
              return response.json();
          })
          .then(data => {
              console.log('Succès:', data);
          })
          .catch(() => {});
     }

  

  checkForWin(symbolsMatrix) {
    const wins = {};

    for (let row = 0; row < symbolsMatrix.length; row++) {
      for (let col = 0; col < symbolsMatrix[row].length - 1; col++) {

        const currentSymbol = symbolsMatrix[row][col];
        const nextSymbol = symbolsMatrix[row][col + 1];

        if (currentSymbol === nextSymbol) {
          // Ajoutez le gain au tableau des gains pour le symbole, ou créez un nouveau tableau s'il n'existe pas
          if (!wins[currentSymbol]) {
            wins[currentSymbol] = [];
          }
          wins[currentSymbol].push({ symbol: currentSymbol, row: row });
        }
      }
    }

    const winsArray = [];
    for (const symbol in wins) {
      // Ajoutez uniquement le premier gain pour chaque symbole à winsArray
      winsArray.push(wins[symbol][0]);
    }

    if (winsArray.length > 0) {
      console.log("Gains détectés :");
      winsArray.forEach((win) => {
        //console.log(`L'utilisateur a gagné avec le symbole ${win.symbol} sur la ligne ${win.row} !`);
      });
    } else {
      //console.log("Aucun gain n'a été détecté.");
    }

    return winsArray.length > 0;
  }
}


  const config = {
      inverted: true, // true: reels spin from top to bottom; false: reels spin from bottom to top
      onSpinStart: (symbols) => {
          return symbols;
          // console.log("onSpinStart", symbols);
      },
      onSpinEnd: (symbols) => {
          return symbols;
          // console.log("onSpinEnd", symbols);
      },
  };

document.addEventListener("DOMContentLoaded", function () {
  const slot = new Slot(document.getElementById("slot"), config);
});
