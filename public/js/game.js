// document.addEventListener('DOMContentLoaded', () => {
//     const canvas = document.getElementById('casinoCanvas');
//     const ctx = canvas.getContext('2d');
//
//     function resizeCanvas() {
//         const scaleFactor = window.devicePixelRatio || 1;
//         const width = canvas.clientWidth;
//         const height = canvas.clientHeight;
//         canvas.width = width * scaleFactor;
//         canvas.height = height * scaleFactor;
//         ctx.scale(scaleFactor, scaleFactor);
//
//         // Redéfinir la largeur des colonnes et la hauteur des lignes
//         colWidth = canvas.width / cols;
//         rowHeight = canvas.height / (rows + 2);
//
//         drawGrid(); // Redessiner la grille après le redimensionnement
//     }
//
//     window.addEventListener('resize', resizeCanvas);
//
//     const cols = 5;
//     const rows = 3;
//     let colWidth, rowHeight;
//     const symbols = [
//         '../assets/symbols/cerise.png',
//         '../assets/symbols/citron.png',
//         '../assets/symbols/pasteque.png',
//         '../assets/symbols/gold.png',
//         '../assets/symbols/7.png',
//         '../assets/symbols/max.png',
//         '../assets/symbols/prune.png',
//         '../assets/symbols/fraise.png',
//     ];
//     const symbols = [];
//     const spinSpeed = 0.2; // Vitesse de rotation constante pour chaque spin
//     const stopSpeed = 0.00001; // Vitesse à laquelle les colonnes s'arrêtent
//     const columnDelay = 200; // Délai initial pour chaque colonne
//     let positions = Array.from({ length: cols }, () => Math.floor(Math.random() * symbols.length));
//     let speeds = Array(cols).fill(0);
//     let spinning = Array(cols).fill(false);
//
//     function loadImages(callback) {
//         let loadedImages = 0;
//         for (let i = 0; i < symbols.length; i++) {
//             symbols[i] = new Image();
//             symbols[i].src = symbols[i];
//             symbols[i].onload = () => {
//                 loadedImages++;
//                 if (loadedImages === symbols.length) {
//                     callback();
//                 }
//             };
//         }
//     }
//
//     function drawGrid() {
//         ctx.clearRect(0, 0, canvas.width, canvas.height);
//         for (let col = 0; col < cols; col++) {
//             for (let row = 0; row < rows + 2; row++) { // Dessine une rangée supplémentaire pour un défilement plus fluide
//                 const yPos = (positions[col] + row) % symbols.length;
//                 const imgIndex = Math.floor(yPos) % symbols.length;
//                 const img = symbols[imgIndex];
//                 const yOffset = (positions[col] % 1) * rowHeight;
//                 ctx.drawImage(img, col * colWidth, canvas.height - (row + 1) * rowHeight + yOffset, colWidth, rowHeight);
//             }
//         }
//         // Dessine les séparateurs de colonnes
//         ctx.strokeStyle = "white";
//         for (let col = 1; col < cols; col++) {
//             ctx.beginPath();
//             ctx.moveTo(col * colWidth, 0);
//             ctx.lineTo(col * colWidth, canvas.height);
//             ctx.stroke();
//         }
//     }
//
//     async function spinColumns() {
//         return new Promise(resolve => {
//             let animationID;
//             let stopColumn = 0;
//
//             function animate() {
//                 if (stopColumn >= cols) {
//                     cancelAnimationFrame(animationID);
//                     resolve();
//                     return;
//                 }
//                 ctx.clearRect(0, 0, canvas.width, canvas.height);
//                 for (let col = 0; col < cols; col++) {
//                     for (let row = 0; row < rows + 2; row++) { // Dessine une rangée supplémentaire pour un défilement plus fluide
//                         const yPos = (positions[col] + row) % symbols.length;
//                         const imgIndex = Math.floor(yPos) % symbols.length;
//                         const img = symbols[imgIndex];
//                         const yOffset = (positions[col] % 1) * rowHeight;
//                         ctx.drawImage(img, col * colWidth, canvas.height - (row + 1) * rowHeight + yOffset, colWidth, rowHeight);
//                     }
//                     if (!spinning[col] && stopColumn === col) {
//                         stopColumn++;
//                         continue;
//                     }
//                     positions[col] += speeds[col];
//                     if (speeds[col] > stopSpeed) { // Gradually slow down
//                         speeds[col] -= 0.001; // Deceleration factor
//                     } else {
//                         if (positions[col] % 1 !== 0) { // Align position to stop exactly at a symbol
//                             positions[col] = Math.round(positions[col]);
//                         }
//                         speeds[col] = 0;
//                         spinning[col] = false;
//                     }
//                 }
//                 // Dessine les séparateurs de colonnes
//                 for (let col = 1; col < cols; col++) {
//                     ctx.beginPath();
//                     ctx.moveTo(col * colWidth, 0);
//                     ctx.lineTo(col * colWidth, canvas.height);
//                     ctx.stroke();
//                 }
//                 animationID = requestAnimationFrame(animate);
//             }
//
//             function startNextColumn(col) {
//                 return new Promise(resolve => {
//                     setTimeout(() => {
//                         speeds[col] = spinSpeed; // Vitesse de rotation constante pour chaque colonne
//                         spinning[col] = true;
//                         animate();
//                         resolve();
//                     }, col * columnDelay);
//                 });
//             }
//
//             for (let col = 0; col < cols; col++) {
//                 startNextColumn(col);
//             }
//         });
//     }
//
//     document.getElementById('spin-button').addEventListener('click', async () => {
//         if (spinning.some(s => s)) return; // Empêche plusieurs spins
//
//         // Calculer le nombre total de permutations uniques
//         const totalPermutations = (cols - 1) * (symbols.length - 1) * (symbols.length - 2);
//
//         // Calculer la probabilité de base pour chaque symbole
//         const baseProbability = 1 / (symbols.length - 1); // On exclut le symbole 'max' des probabilités
//
//         // Ajuster les probabilités en fonction de vos préférences
//         const pruneProbability = baseProbability * 5; // Augmente la probabilité de la prune
//         const maxProbability = baseProbability * 0.1; // Réduit la probabilité du symbole 'max'
//
//         // Définir les probabilités pour chaque symbole
//         const probabilities = [
//             pruneProbability,
//             baseProbability, // cerise, citron, pasteque, gold, 7
//             baseProbability,
//             baseProbability,
//             baseProbability,
//             maxProbability, // max
//             baseProbability, // fraise
//         ];
//
//         // Normaliser les probabilités pour s'assurer qu'elles totalisent 1
//         const totalProbability = probabilities.reduce((acc, prob) => acc + prob, 0);
//         const normalizedProbabilities = probabilities.map(prob => prob / totalProbability);
//
//         // Afficher les probabilités normalisées
//         console.log("Probabilités normalisées :", normalizedProbabilities);
//
//
//         await spinColumns();
//     });
//
//     loadImages(() => {
//         resizeCanvas();
//         drawGrid();
//     });
// });