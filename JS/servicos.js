document.addEventListener("DOMContentLoaded", function () {
    var selectOrdenacao = document.getElementById("ordenar-servicos");
    var listaServicos = document.getElementById("lista-servicos");
    var inputBusca = document.getElementById("busca-servicos");

    if (!listaServicos) return;

    var itensOriginais = Array.from(listaServicos.querySelectorAll("li.servico"));

    function ordenarLista(criterio) {
        var itens = Array.from(listaServicos.querySelectorAll("li.servico"));

        if (criterio === "padrao") {
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

        itens.forEach(function (item) {
            listaServicos.appendChild(item);
        });
    }

    if (selectOrdenacao) {
        selectOrdenacao.addEventListener("change", function () {
            ordenarLista(this.value);
        });
    }

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
