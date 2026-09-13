document.addEventListener("DOMContentLoaded", function () {

    var servicos = [
        {
            id_servico: 1,
            nome: "Manutenção Especializada",
            descricao: "Reparo e conserto completo de computadores e notebooks de diversas marcas e modelos.",
            valor_base: 0,
            status: "ativo",
            pagina: "./especificacaoServico/manutencao.html"
        },
        {
            id_servico: 2,
            nome: "Formatação de Sistemas",
            descricao: "Instalação limpa, configuração de drivers e otimização para computadores, notebooks e celulares.",
            valor_base: 0,
            status: "ativo",
            pagina: "./especificacaoServico/formatacao.html"
        },
        {
            id_servico: 3,
            nome: "Montagem de Computadores",
            descricao: "Projetos personalizados para PCs gamer, uso profissional ou estações de trabalho de alto desempenho.",
            valor_base: 0,
            status: "ativo",
            pagina: "./especificacaoServico/montagem.html"
        },
        {
            id_servico: 4,
            nome: "Recuperação de Dados",
            descricao: "Resgate seguro de arquivos, documentos e dados perdidos ou corrompidos.",
            valor_base: 0,
            status: "ativo",
            pagina: "./especificacaoServico/recuperacao.html"
        },
        {
            id_servico: 5,
            nome: "Limpeza Preventiva",
            descricao: "Higienização interna e externa para prevenir superaquecimento e aumentar a vida útil do equipamento.",
            valor_base: 0,
            status: "ativo",
            pagina: "./especificacaoServico/limpeza.html"
        }
    ];

    var listaServicos = document.getElementById("lista-servicos");

    if (!listaServicos) {
        return;
    }

    servicos.forEach(function (servico) {
        if (servico.status === "ativo") {
            var item = document.createElement("li");
            item.classList.add("servico");
            item.dataset.idServico = servico.id_servico;

            item.innerHTML =
                "<h3>" + servico.nome + "</h3>" +
                "<p>" + servico.descricao + "</p>" +
                "<a href='" + servico.pagina + "'>[Solicitar Serviço]</a>";

            listaServicos.appendChild(item);
        }
    });

});

document.addEventListener("DOMContentLoaded", function () {

    var formulario = document.getElementById("form-solicitacao");

    if (!formulario) {
        return;
    }

    formulario.addEventListener("submit", function (event) {

        event.preventDefault();

        var idServico = formulario.dataset.servicoId;

        var tipoAparelho = document.getElementById("tipo-aparelho");
        var tipoPagamento = document.getElementById("tipo-pagamento");
        var problemaAparelho = document.getElementById("problema-aparelho");
        var backup = document.getElementById("backup");

        var processador = document.getElementById("processador");
        var placaMae = document.getElementById("placa-mae");
        var memoriaRam = document.getElementById("memoria-ram");
        var placaVideo = document.getElementById("placa-video");
        var armazenamento = document.getElementById("armazenamento");
        var fonte = document.getElementById("fonte");
        var gabinete = document.getElementById("gabinete");

        if (!tipoPagamento) {
            console.error("Campo tipo-pagamento não encontrado.");
            return;
        }

        if (tipoAparelho && tipoAparelho.value === "") {
            alert("Selecione o tipo de aparelho.");
            tipoAparelho.focus();
            return;
        }

        if (backup && backup.value === "") {
            alert("Selecione uma opção de backup.");
            backup.focus();
            return;
        }

        var formasValidas = ["dinheiro", "pix", "cartao_credito", "cartao_debito"];
        if (tipoPagamento.value === "" || !formasValidas.includes(tipoPagamento.value)) {
            alert("Selecione uma forma de pagamento válida.");
            tipoPagamento.focus();
            return;
        }

        var solicitacao = {
            id_servico: Number(idServico),

            tipo_aparelho: tipoAparelho
                ? tipoAparelho.value
                : "",

            problema_aparelho: problemaAparelho
                ? problemaAparelho.value
                : "",

            backup: backup
                ? backup.value
                : "",

            tipo_pagamento: tipoPagamento.value,

            processador: processador
                ? processador.value
                : "",

            placa_mae: placaMae
                ? placaMae.value
                : "",

            memoria_ram: memoriaRam
                ? memoriaRam.value
                : "",

            placa_video: placaVideo
                ? placaVideo.value
                : "",

            armazenamento: armazenamento
                ? armazenamento.value
                : "",

            fonte: fonte
                ? fonte.value
                : "",

            gabinete: gabinete
                ? gabinete.value
                : ""
        };

        console.log("Solicitação criada:", solicitacao);

        if (formulario.getAttribute("action")) {
            formulario.submit();
        } else {
            alert("Solicitação preenchida com sucesso!");
        }

    });

});