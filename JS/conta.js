var CAMINHO_PHP = "../PHP/";

document.addEventListener("DOMContentLoaded", function () {

    var areaVisitante = document.getElementById("area-visitante");
    var areaDashboard = document.getElementById("area-dashboard");

    var abaEntrar = document.getElementById("aba-entrar");
    var abaRegistrar = document.getElementById("aba-registrar");
    var painelEntrar = document.getElementById("painel-entrar");
    var painelRegistrar = document.getElementById("painel-registrar");
    var linkIrRegistrar = document.getElementById("link-ir-registrar");
    var linkIrEntrar = document.getElementById("link-ir-entrar");

    var formEntrar = document.getElementById("form-entrar");
    var formRegistrar = document.getElementById("form-registrar");
    var erroEntrar = document.getElementById("erro-entrar");
    var erroRegistrar = document.getElementById("erro-registrar");

    var abaPerfil = document.getElementById("aba-perfil");
    var abaServicos = document.getElementById("aba-servicos");
    var painelPerfil = document.getElementById("painel-perfil");
    var painelServicos = document.getElementById("painel-servicos");
    var painelPagamento = document.getElementById("painel-pagamento");

    var perfilVisualizacao = document.getElementById("perfil-visualizacao");
    var formPerfil = document.getElementById("form-perfil");
    var botaoEditarPerfil = document.getElementById("botao-editar-perfil");
    var botaoCancelarPerfil = document.getElementById("botao-cancelar-perfil");
    var erroPerfil = document.getElementById("erro-perfil");
    var msgPerfil = document.getElementById("msg-perfil");

    var listaServicosCliente = document.getElementById("lista-servicos-cliente");
    var resumoHistorico = document.getElementById("resumo-historico");

    var formPagamento = document.getElementById("form-pagamento");
    var erroPagamento = document.getElementById("erro-pagamento");
    var botaoCancelarPagamento = document.getElementById("botao-cancelar-pagamento");

    var botaoSair = document.getElementById("botao-sair");

    var servicoSelecionadoParaPagamento = null;

    if (window.IMask) {
        IMask(document.getElementById("reg-telefone"), { mask: "(00) 00000-0000" });
        IMask(document.getElementById("reg-cpf"), { mask: "000.000.000-00" });
        IMask(document.getElementById("perfil-telefone"), { mask: "(00) 00000-0000" });
        IMask(document.getElementById("perfil-cpf"), { mask: "000.000.000-00" });
    }

    async function enviarParaPHP(arquivoPhp, dados) {
        var corpo = new URLSearchParams();

        for (var campo in dados) {
            corpo.append(campo, dados[campo]);
        }

        var resposta = await fetch(CAMINHO_PHP + arquivoPhp, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            credentials: "same-origin",
            body: corpo
        });

        return await resposta.json();
    }

    async function buscarDoPHP(arquivoPhp) {
        var resposta = await fetch(CAMINHO_PHP + arquivoPhp, {
            method: "GET",
            credentials: "same-origin"
        });

        return await resposta.json();
    }

    function formatarMoeda(valor) {
        return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    }

    function classeBadgeStatus(status) {
        if (status === "Concluído") {
            return "status-concluido";
        }
        if (status === "Aguardando pagamento") {
            return "status-pendente";
        }
        return "status-andamento";
    }

    function mostrarVisitante() {
        areaVisitante.classList.remove("oculto");
        areaDashboard.classList.add("oculto");
    }

    function mostrarDashboard(clientes) {
        areaVisitante.classList.add("oculto");
        areaDashboard.classList.remove("oculto");
        preencherPerfil(clientes);
        carregarServicos();
    }

    async function iniciarPagina() {
        try {
            var resposta = await buscarDoPHP("verificar_sessao.php");

            if (resposta && resposta.logado) {
                mostrarDashboard(resposta.clientes || resposta.usuario);
            } else {
                mostrarVisitante();
            }
        } catch (erro) {
            console.warn("Não foi possível verificar a sessão no servidor:", erro);
            mostrarVisitante();
        }
    }

    abaEntrar.addEventListener("click", function () {
        abaEntrar.classList.add("ativa");
        abaRegistrar.classList.remove("ativa");
        painelEntrar.classList.remove("oculto");
        painelRegistrar.classList.add("oculto");
    });

    abaRegistrar.addEventListener("click", function () {
        abaRegistrar.classList.add("ativa");
        abaEntrar.classList.remove("ativa");
        painelRegistrar.classList.remove("oculto");
        painelEntrar.classList.add("oculto");
    });

    linkIrRegistrar.addEventListener("click", function (evento) {
        evento.preventDefault();
        abaRegistrar.click();
    });

    linkIrEntrar.addEventListener("click", function (evento) {
        evento.preventDefault();
        abaEntrar.click();
    });

    abaPerfil.addEventListener("click", function () {
        abaPerfil.classList.add("ativa");
        abaServicos.classList.remove("ativa");
        painelPerfil.classList.remove("oculto");
        painelServicos.classList.add("oculto");
        painelPagamento.classList.add("oculto");
    });

    abaServicos.addEventListener("click", function () {
        abaServicos.classList.add("ativa");
        abaPerfil.classList.remove("ativa");
        painelServicos.classList.remove("oculto");
        painelPerfil.classList.add("oculto");
        painelPagamento.classList.add("oculto");
    });

    formEntrar.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        erroEntrar.textContent = "";

        var email = document.getElementById("login-email").value.trim().toLowerCase();
        var senha = document.getElementById("login-senha").value;

        var botaoEnviar = formEntrar.querySelector("button[type='submit']");
        botaoEnviar.disabled = true;

        try {
            var resposta = await enviarParaPHP("login.php", { email: email, senha: senha });

            if (!resposta.sucesso) {
                erroEntrar.textContent = resposta.mensagem || "E-mail ou senha inválidos.";
                return;
            }

            formEntrar.reset();
            mostrarDashboard(resposta.clientes || resposta.usuario);
        } catch (erro) {
            erroEntrar.textContent = "Não foi possível conectar ao servidor. Tente novamente.";
            console.error(erro);
        } finally {
            botaoEnviar.disabled = false;
        }
    });

    formRegistrar.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        erroRegistrar.textContent = "";

        var nome = document.getElementById("reg-nome").value.trim();
        var email = document.getElementById("reg-email").value.trim().toLowerCase();
        var telefone = document.getElementById("reg-telefone").value;
        var cpf = document.getElementById("reg-cpf").value;
        var senha = document.getElementById("reg-senha").value;
        var confirmarSenha = document.getElementById("reg-confirmar-senha").value;

        if (senha.length < 6) {
            erroRegistrar.textContent = "A senha deve ter pelo menos 6 caracteres.";
            return;
        }

        if (senha !== confirmarSenha) {
            erroRegistrar.textContent = "As senhas não coincidem.";
            return;
        }

        var botaoEnviar = formRegistrar.querySelector("button[type='submit']");
        botaoEnviar.disabled = true;

        try {
            var resposta = await enviarParaPHP("registrar.php", {
                nome: nome,
                email: email,
                telefone: telefone,
                cpf: cpf,
                senha: senha
            });

            if (!resposta.sucesso) {
                erroRegistrar.textContent = resposta.mensagem || "Não foi possível criar a conta.";
                return;
            }

            formRegistrar.reset();
            mostrarDashboard(resposta.clientes || resposta.usuario);
        } catch (erro) {
            erroRegistrar.textContent = "Não foi possível conectar ao servidor. Tente novamente.";
            console.error(erro);
        } finally {
            botaoEnviar.disabled = false;
        }
    });

    function preencherPerfil(clientes) {
        document.getElementById("dashboard-nome").textContent = clientes.nome;
        document.getElementById("ver-nome").textContent = clientes.nome;
        document.getElementById("ver-email").textContent = clientes.email;
        document.getElementById("ver-telefone").textContent = clientes.telefone;
        document.getElementById("ver-cpf").textContent = clientes.cpf;

        document.getElementById("perfil-nome").value = clientes.nome;
        document.getElementById("perfil-email").value = clientes.email;
        document.getElementById("perfil-telefone").value = clientes.telefone;
        document.getElementById("perfil-cpf").value = clientes.cpf;
        document.getElementById("perfil-senha").value = "";

        msgPerfil.textContent = "";
        erroPerfil.textContent = "";
    }

    botaoEditarPerfil.addEventListener("click", function () {
        perfilVisualizacao.classList.add("oculto");
        formPerfil.classList.remove("oculto");
        msgPerfil.textContent = "";
    });

    botaoCancelarPerfil.addEventListener("click", async function () {
        try {
            var resposta = await buscarDoPHP("verificar_sessao.php");
            if (resposta && resposta.logado) {
                preencherPerfil(resposta.clientes || resposta.usuario);
            }
        } catch (erro) {
            console.error(erro);
        }
        formPerfil.classList.add("oculto");
        perfilVisualizacao.classList.remove("oculto");
    });

    formPerfil.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        erroPerfil.textContent = "";

        var nome = document.getElementById("perfil-nome").value.trim();
        var email = document.getElementById("perfil-email").value.trim().toLowerCase();
        var telefone = document.getElementById("perfil-telefone").value;
        var cpf = document.getElementById("perfil-cpf").value;
        var senha = document.getElementById("perfil-senha").value;

        if (senha && senha.length < 6) {
            erroPerfil.textContent = "A nova senha deve ter pelo menos 6 caracteres.";
            return;
        }

        var botaoEnviar = formPerfil.querySelector("button[type='submit']");
        botaoEnviar.disabled = true;

        try {
            var dadosEnvio = { nome: nome, email: email, telefone: telefone, cpf: cpf };
            if (senha) {
                dadosEnvio.senha = senha;
            }

            var resposta = await enviarParaPHP("atualizar_perfil.php", dadosEnvio);

            if (!resposta.sucesso) {
                erroPerfil.textContent = resposta.mensagem || "Não foi possível atualizar os dados.";
                return;
            }

            preencherPerfil(resposta.clientes || resposta.usuario);
            formPerfil.classList.add("oculto");
            perfilVisualizacao.classList.remove("oculto");
            msgPerfil.textContent = "Dados atualizados com sucesso!";
        } catch (erro) {
            erroPerfil.textContent = "Não foi possível conectar ao servidor. Tente novamente.";
            console.error(erro);
        } finally {
            botaoEnviar.disabled = false;
        }
    });

    async function carregarServicos() {
        listaServicosCliente.innerHTML = "<li>Carregando serviços...</li>";
        resumoHistorico.textContent = "";

        try {
            var resposta = await buscarDoPHP("listar_servicos.php");

            if (!resposta.sucesso) {
                listaServicosCliente.innerHTML = "<li>Não foi possível carregar seus serviços.</li>";
                return;
            }

            exibirServicos(resposta.servicos || []);
        } catch (erro) {
            listaServicosCliente.innerHTML = "<li>Não foi possível conectar ao servidor.</li>";
            console.error(erro);
        }
    }

    function rotuloFormaPagamento(codigo) {
        var rotulos = {
            pix: "PIX",
            cartao_credito: "Cartão de Crédito",
            cartao_debito: "Cartão de Débito",
            dinheiro: "Dinheiro"
        };

        return rotulos[codigo] || codigo;
    }

    function exibirServicos(servicos) {
        listaServicosCliente.innerHTML = "";

        if (servicos.length === 0) {
            listaServicosCliente.innerHTML = "<li>Você ainda não possui serviços solicitados.</li>";
            resumoHistorico.textContent = "Nenhum serviço registrado até o momento.";
            return;
        }

        servicos.forEach(function (servico) {
            var item = document.createElement("li");
            item.classList.add("servico-cliente");

            var botaoPagar = !servico.pago
                ? '<br><button type="button" class="botao-pagar" data-id-servico="' + servico.id + '">Pagar agora</button>'
                : "";

            var imgHtml = servico.imagem
                ? '<img src="' + servico.imagem + '" alt="' + servico.nome + '" class="servico-cliente-img">'
                : "";

            item.innerHTML =
                imgHtml +
                "<b>Serviço:</b> " + servico.nome + "<br>" +
                "<b>Data de Abertura:</b> " + servico.data + "<br>" +
                "<b>Status:</b> <span class='status-badge " + classeBadgeStatus(servico.status) + "'>" + servico.status + "</span><br>" +
                "<b>Valor:</b> " + formatarMoeda(servico.valor) + " " +
                "(" + (servico.pago ? "Pago via " + rotuloFormaPagamento(servico.forma_pagamento) : "Pagamento pendente") + ")" +
                botaoPagar;

            listaServicosCliente.appendChild(item);
        });

        var anoMaisAntigo = servicos.reduce(function (menor, servico) {
            var ano = Number(String(servico.data).split("/")[2]);
            return ano < menor ? ano : menor;
        }, Number(String(servicos[0].data).split("/")[2]));

        resumoHistorico.textContent =
            "Você possui " + servicos.length + " serviço(s) registrado(s) conosco desde " + anoMaisAntigo + ".";

        document.querySelectorAll(".botao-pagar").forEach(function (botao) {
            botao.addEventListener("click", function () {
                abrirPagamento(Number(botao.dataset.idServico), servicos);
            });
        });
    }

    function abrirPagamento(idServico, servicos) {
        var servico = servicos.find(function (s) {
            return s.id === idServico;
        });

        if (!servico) {
            return;
        }

        servicoSelecionadoParaPagamento = idServico;

        document.getElementById("pagamento-nome-servico").textContent = servico.nome;
        document.getElementById("pagamento-valor").textContent = formatarMoeda(servico.valor);
        document.getElementById("forma-pagamento").value = "";
        erroPagamento.textContent = "";

        painelServicos.classList.add("oculto");
        painelPerfil.classList.add("oculto");
        painelPagamento.classList.remove("oculto");
    }

    botaoCancelarPagamento.addEventListener("click", function () {
        servicoSelecionadoParaPagamento = null;
        painelPagamento.classList.add("oculto");
        painelServicos.classList.remove("oculto");
    });

    formPagamento.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        erroPagamento.textContent = "";

        var idServico = servicoSelecionadoParaPagamento;
        var formaPagamento = document.getElementById("forma-pagamento").value;

        if (formaPagamento === "") {
            erroPagamento.textContent = "Selecione uma forma de pagamento.";
            return;
        }

        var botaoEnviar = formPagamento.querySelector("button[type='submit']");
        botaoEnviar.disabled = true;

        try {
            var resposta = await enviarParaPHP("pagar_servico.php", {
                id_servico: idServico,
                forma_pagamento: formaPagamento
            });

            if (!resposta.sucesso) {
                erroPagamento.textContent = resposta.mensagem || "Não foi possível confirmar o pagamento.";
                return;
            }

            alert("Pagamento confirmado com sucesso!");
            servicoSelecionadoParaPagamento = null;
            painelPagamento.classList.add("oculto");
            painelServicos.classList.remove("oculto");
            carregarServicos();
        } catch (erro) {
            erroPagamento.textContent = "Não foi possível conectar ao servidor. Tente novamente.";
            console.error(erro);
        } finally {
            botaoEnviar.disabled = false;
        }
    });

    botaoSair.addEventListener("click", async function () {
        try {
            await enviarParaPHP("logout.php", {});
        } catch (erro) {
            console.error(erro);
        }

        painelPagamento.classList.add("oculto");
        painelServicos.classList.add("oculto");
        painelPerfil.classList.remove("oculto");
        abaPerfil.classList.add("ativa");
        abaServicos.classList.remove("ativa");
        mostrarVisitante();
    });

    iniciarPagina();
});