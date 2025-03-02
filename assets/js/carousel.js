document.addEventListener("DOMContentLoaded", function () {
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

    document.querySelectorAll(".carousel").forEach(carousel => {
        let startX = 0;
        let endX = 0;

        carousel.addEventListener("touchstart", function (e) {
            startX = e.touches[0].clientX;
        });

        carousel.addEventListener("touchmove", function (e) {
            endX = e.touches[0].clientX;
        });

        carousel.addEventListener("touchend", function () {
            let outfitId = carousel.getAttribute("data-id");
            let deltaX = startX - endX;

            if (Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    changeSlide(outfitId, "next");
                } else {
                    changeSlide(outfitId, "prev");
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
