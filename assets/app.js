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