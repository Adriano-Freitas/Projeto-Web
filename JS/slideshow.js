document.addEventListener("DOMContentLoaded", function () {
    var container = document.querySelector(".slideshow-container");
    if (!container) return;

    var slides = container.querySelectorAll(".slide");
    var dots = container.querySelectorAll(".dot");
    var btnPrev = container.querySelector(".slide-prev");
    var btnNext = container.querySelector(".slide-next");

    if (!slides.length) return;

    var indiceAtual = 0;
    var totalSlides = slides.length;
    var intervaloTempo = 4000;
    var temporizador = null;

    function mostrarSlide(indice) {
        if (indice >= totalSlides) {
            indiceAtual = 0;
        } else if (indice < 0) {
            indiceAtual = totalSlides - 1;
        } else {
            indiceAtual = indice;
        }

        slides.forEach(function (slide, i) {
            if (i === indiceAtual) {
                slide.classList.add("ativo");
            } else {
                slide.classList.remove("ativo");
            }
        });

        dots.forEach(function (dot, i) {
            if (i === indiceAtual) {
                dot.classList.add("ativo");
            } else {
                dot.classList.remove("ativo");
            }
        });
    }

    function proximoSlide() {
        mostrarSlide(indiceAtual + 1);
    }

    function slideAnterior() {
        mostrarSlide(indiceAtual - 1);
    }

    function iniciarAutoplay() {
        pararAutoplay();
        temporizador = setInterval(proximoSlide, intervaloTempo);
    }

    function pararAutoplay() {
        if (temporizador) {
            clearInterval(temporizador);
            temporizador = null;
        }
    }

    if (btnNext) {
        btnNext.addEventListener("click", function () {
            proximoSlide();
            iniciarAutoplay();
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener("click", function () {
            slideAnterior();
            iniciarAutoplay();
        });
    }

    dots.forEach(function (dot, index) {
        dot.addEventListener("click", function () {
            mostrarSlide(index);
            iniciarAutoplay();
        });
    });

    container.addEventListener("mouseenter", pararAutoplay);
    container.addEventListener("mouseleave", iniciarAutoplay);

    container.setAttribute("tabindex", "0");
    container.addEventListener("keydown", function (evento) {
        if (evento.key === "ArrowLeft") {
            slideAnterior();
            iniciarAutoplay();
        } else if (evento.key === "ArrowRight") {
            proximoSlide();
            iniciarAutoplay();
        }
    });

    mostrarSlide(0);
    iniciarAutoplay();
});
