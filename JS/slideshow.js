/**
 * Fix it - Modulo de Slideshow Interativo (JavaScript)
 * Atende ao criterio de 'Animacao Slideshow' do barema de Programacao Web.
 */
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
    var intervaloTempo = 4000; // 4 segundos
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

    // Controles de clique nos botoes
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

    // Controles nos indicadores (dots)
    dots.forEach(function (dot, index) {
        dot.addEventListener("click", function () {
            mostrarSlide(index);
            iniciarAutoplay();
        });
    });

    // Pausar autoplay quando o mouse estiver sobre o carrossel
    container.addEventListener("mouseenter", pararAutoplay);
    container.addEventListener("mouseleave", iniciarAutoplay);

    // Navegacao pelo teclado (setas esquerda/direita)
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

    // Iniciar exibicao
    mostrarSlide(0);
    iniciarAutoplay();
});
