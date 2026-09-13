<?php require_once __DIR__ . '/init.php'; ?>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="../HTML/index.php">Fix<span>It</span></a>
        <nav aria-label="Navegação principal">
            <a href="../HTML/index.php">Início</a>
            <a href="../HTML/sobre.php">Sobre</a>
            <a href="../HTML/servicos.php">Serviços</a>
            <a href="../HTML/contatos.php">Contatos</a>
            <?php if (clientesAtual()): ?>
                <a href="../HTML/conta.php">Minha conta</a>
                <?php if (in_array(clientesAtual()['perfil'], ['gerente','tecnico'], true)): ?>
                    <a href="../PHP/admin/index.php">CMS</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="../HTML/conta.php">Entrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
