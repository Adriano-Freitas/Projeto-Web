document.addEventListener("DOMContentLoaded", function () {
    var ehSubpasta = window.location.pathname.includes("/especificacaoServico/");
    var caminhoPhp = ehSubpasta ? "../../PHP/" : "../PHP/";
    var caminhoConta = ehSubpasta ? "../conta.html" : "./conta.html";

    function atualizarMenuTopo(usuario) {
        var menu = document.querySelector(".centralizar p");
        if (!menu) return;

        var itemLogin = null;
        var itens = menu.querySelectorAll("a, span");
        itens.forEach(function (el) {
            var texto = el.textContent.trim().toLowerCase();
            var href = el.getAttribute("href") || "";
            if (texto === "login" || href.includes("conta.html") || el.classList.contains("menu-auth")) {
                itemLogin = el;
            }
        });

        if (usuario && usuario.nome) {
            var primeiroNome = usuario.nome.split(" ")[0];
            var containerAuth = document.createElement("span");
            containerAuth.className = "menu-auth";
            containerAuth.innerHTML = '<a href="' + caminhoConta + '">Olá, ' + primeiroNome + '</a> <a href="' + caminhoPhp + 'logout.php" class="link-sair-menu">[Sair]</a>';

            if (itemLogin) {
                itemLogin.replaceWith(containerAuth);
            }
        } else {
            if (itemLogin && itemLogin.classList && itemLogin.classList.contains("menu-auth")) {
                var spanOuLink = document.createElement("a");
                spanOuLink.href = caminhoConta;
                spanOuLink.textContent = "Login";
                itemLogin.replaceWith(spanOuLink);
            }
        }
    }

    function protegerPaginaEspecificacao(logado) {
        if (ehSubpasta && !logado) {
            alert("Você precisa estar conectado para solicitar um serviço. Redirecionando para o login...");
            window.location.replace(caminhoConta);
        }
    }

    function interceptarSolicitacoes(logado) {
        var linksSolicitar = document.querySelectorAll('a[href*="especificacaoServico"]');
        linksSolicitar.forEach(function (link) {
            link.addEventListener("click", function (e) {
                if (!logado) {
                    e.preventDefault();
                    alert("Você precisa estar conectado para solicitar um serviço. Redirecionando para o login...");
                    window.location.href = caminhoConta;
                }
            });
        });
    }

    fetch(caminhoPhp + "verificar_sessao.php", { credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var logado = !!(res && res.logado && (res.clientes || res.usuario));
            var usuario = logado ? (res.clientes || res.usuario) : null;

            atualizarMenuTopo(usuario);
            protegerPaginaEspecificacao(logado);
            interceptarSolicitacoes(logado);
        })
        .catch(function () {
            protegerPaginaEspecificacao(false);
            interceptarSolicitacoes(false);
        });

    document.addEventListener("click", function (e) {
        if (e.target && e.target.classList && e.target.classList.contains("link-sair-menu")) {
            e.preventDefault();
            fetch(caminhoPhp + "logout.php", { method: "POST", credentials: "same-origin" })
                .then(function () {
                    window.location.href = caminhoConta;
                })
                .catch(function () {
                    window.location.href = caminhoPhp + "logout.php";
                });
        }
    });
});
