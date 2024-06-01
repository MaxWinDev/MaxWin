// Définition des variables globales
let isSpinning = false;
let columns = []; // Remplissez cela avec les éléments DOM appropriés
columns = document.querySelectorAll('.column');

// Fonction pour réinitialiser les colonnes
function resetColumns() {
    columns.forEach(column => {
        column.style.animation = 'none'; // Arrêter l'animation de chaque colonne
        column.style.transition = 'none'; // Réinitialiser la transition
        column.style.transform = 'translateY(0)'; // Réinitialiser la position des fruits
    });
}

// Fonction pour spinner les colonnes

async function spinColumns() {
    // Supposons que columns est un tableau d'éléments DOM représentant vos colonnes
    const columns = document.querySelectorAll('.column'); // Ajustez le sélecteur selon votre structure HTML

    resetColumns(columns); // Assurez-vous que cette fonction existe et est définie quelque part

    const minFruits = 5;
    const maxFruits = 15;
    const totalColumns = columns.length;
    const totalFruits = totalColumns * maxFruits;
    const fruitCounts = Array.from({ length: totalColumns }, () => Math.floor(Math.random() * (maxFruits - minFruits + 1)) + minFruits);

    const minDuration = 0.5;
    const maxDuration = 2;
    let totalAnimationDuration = 0;

    for (let i = 0; i < totalColumns; i++) {
        const column = columns[i];
        const fruitCount = fruitCounts[i];
        const columnDuration = (Math.random() * (maxDuration - minDuration) + minDuration) * fruitCount; // Durée de l'animation pour cette colonne
        column.style.animation = `spin ${columnDuration}s linear forwards`;
        totalAnimationDuration = Math.max(totalAnimationDuration, columnDuration);
    }

    await new Promise(resolve => setTimeout(resolve, totalAnimationDuration * 1000)); // Attendre la fin de l'animation la plus longue

    columns.forEach(column => {
        column.style.animation = 'none'; // Arrêter l'animation de chaque colonne
        column.style.transition = 'transform 0.5s ease'; // Ajouter une transition pour la douceur
        column.style.transform = window.getComputedStyle(column).getPropertyValue('transform'); // Fixer la position actuelle des fruits
    });

    isSpinning = false; // Assurez-vous que cette variable est définie quelque part
}

// Sélection des colonnes et attachez l'écouteur d'événements au bouton
document.addEventListener("DOMContentLoaded", function() {
    const spinButton = document.getElementById("spin-button");

    spinButton.addEventListener("click", function() {
        spinColumns();
    });
});