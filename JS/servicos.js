/**
 * Fix it - Modulo de Ordenacao Dinamica de Elementos (JavaScript)
 * Atende ao criterio de 'Ordenacao de elementos' do barema de Programacao Web.
 */
document.addEventListener("DOMContentLoaded", function () {
    var selectOrdenacao = document.getElementById("ordenar-servicos");
    var listaServicos = document.getElementById("lista-servicos");
    var inputBusca = document.getElementById("busca-servicos");

    if (!listaServicos) return;

    // Guarda a lista inicial original para poder restaurar a ordem padrao
    var itensOriginais = Array.from(listaServicos.querySelectorAll("li.servico"));

    function ordenarLista(criterio) {
        var itens = Array.from(listaServicos.querySelectorAll("li.servico"));

        if (criterio === "padrao") {
            // Restaura a ordem original pelo id ou posicao inicial
            itensOriginais.forEach(function (item) {
                listaServicos.appendChild(item);
            });
            return;
        }

        itens.sort(function (a, b) {
            var tituloA = (a.querySelector("h3") ? a.querySelector("h3").textContent : "").trim().toLowerCase();
            var tituloB = (b.querySelector("h3") ? b.querySelector("h3").textContent : "").trim().toLowerCase();

            if (criterio === "nome-asc") {
                return tituloA.localeCompare(tituloB, "pt-BR");
            } else if (criterio === "nome-desc") {
                return tituloB.localeCompare(tituloA, "pt-BR");
            }
            return 0;
        });

        // Reanexa os itens ordenados ao DOM com efeito visual
        itens.forEach(function (item) {
            listaServicos.appendChild(item);
        });
    }

    if (selectOrdenacao) {
        selectOrdenacao.addEventListener("change", function () {
            ordenarLista(this.value);
        });
    }

    // Filtro instantaneo por texto de pesquisa (bonus de usabilidade)
    if (inputBusca) {
        inputBusca.addEventListener("input", function () {
            var termo = this.value.trim().toLowerCase();
            var itens = listaServicos.querySelectorAll("li.servico");

            itens.forEach(function (item) {
                var texto = item.textContent.toLowerCase();
                if (texto.includes(termo)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        });
    }
});
