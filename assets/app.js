import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.scss';

// Import Font Awesome
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';

// Navbar
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("default-sidebar");
    const toggleButton = document.getElementById("sidebar-toggle");
    const overlay = document.getElementById("sidebar-overlay");

    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("-translate-x-full");
        overlay.classList.toggle("hidden");
    });

    overlay.addEventListener("click", function () {
        sidebar.classList.add("-translate-x-full");
        overlay.classList.add("hidden");
    });
});

// Profile edit
document.addEventListener("DOMContentLoaded", function() {
    const editToggle = document.getElementById("edit-toggle");
    const viewMode = document.getElementById("view-mode");
    const editMode = document.getElementById("edit-mode");

    let editActive = false;

    editToggle.addEventListener("click", function() {
        editActive = !editActive;
        if (editActive) {
            editToggle.textContent = "Annuler";
            viewMode.classList.add("hidden");
            editMode.classList.remove("hidden");
        } else {
            editToggle.textContent = "Modifier le profil";
            viewMode.classList.remove("hidden");
            editMode.classList.add("hidden");
        }
    });
});

//acordeon
document.addEventListener('DOMContentLoaded', () => {
    // Sélectionne tous les blocs d'accordéon
    const sections = document.querySelectorAll('[data-accordion-section]');

    sections.forEach(section => {
        const header = section.querySelector('[data-accordion-header]');
        const content = section.querySelector('[data-accordion-content]');

        // Au clic sur le header, on toggle la classe "hidden" sur le contenu
        header.addEventListener('click', () => {
            content.classList.toggle('hidden');
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    // Sélectionne tous les boutons de navigation des carrousels
    document.querySelectorAll(".carousel-prev").forEach(button => {
        button.addEventListener("click", function () {
            let outfitId = this.getAttribute("data-id");
            changeSlide(outfitId, "prev");
        });
    });

    document.querySelectorAll(".carousel-next").forEach(button => {
        button.addEventListener("click", function () {
            let outfitId = this.getAttribute("data-id");
            changeSlide(outfitId, "next");
        });
    });

    // Ajout du swipe sur mobile
    document.querySelectorAll(".carousel").forEach(carousel => {
        let startX = 0;
        let endX = 0;

        carousel.addEventListener("touchstart", function (e) {
            startX = e.touches[0].clientX; // Position initiale du toucher
        });

        carousel.addEventListener("touchmove", function (e) {
            endX = e.touches[0].clientX; // Position en cours du toucher
        });

        carousel.addEventListener("touchend", function () {
            let outfitId = carousel.getAttribute("data-id");
            let deltaX = startX - endX;

            if (Math.abs(deltaX) > 50) { // Seulement si le swipe est significatif
                if (deltaX > 0) {
                    changeSlide(outfitId, "next"); // Swipe gauche → Next
                } else {
                    changeSlide(outfitId, "prev"); // Swipe droite → Previous
                }
            }
        });
    });

    function changeSlide(outfitId, direction) {
        let carousel = document.querySelector(`.carousel[data-id="${outfitId}"]`);
        let items = carousel.querySelectorAll('.carousel-item');
        let activeIndex = Array.from(items).findIndex(item => item.classList.contains('opacity-100'));

        // Masquer l'image actuelle
        items[activeIndex].classList.remove('opacity-100');
        items[activeIndex].classList.add('opacity-0');

        // Déterminer la nouvelle image à afficher
        let newIndex;
        if (direction === "next") {
            newIndex = (activeIndex + 1) % items.length;
        } else {
            newIndex = (activeIndex - 1 + items.length) % items.length;
        }

        // Afficher la nouvelle image
        items[newIndex].classList.remove('opacity-0');
        items[newIndex].classList.add('opacity-100');
    }
});
document.querySelectorAll('.remove-item').forEach(function(button) {
    button.addEventListener('click', function(e) {
        // On supprime le conteneur de l'élément
        var itemRow = e.target.closest('.item-row');
        if (itemRow) {
            console.log('aled')
            itemRow.remove();
        }
    });
});