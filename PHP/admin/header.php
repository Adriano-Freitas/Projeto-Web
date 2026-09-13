<?php
	// Cabeçalho comum do Módulo Administrativo (CMS) - Inclusão de
	// Arquivos (cabeçalho/rodapé) e controle de acesso por sessão (RF10).
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["usuario"]) || !in_array($_SESSION["usuario"]["tipo"], array("gerente", "tecnico"))) {
		header("Location: index.php");
		exit;
	}

	$usuarioLogado = $_SESSION["usuario"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>Painel Administrativo - Fix it</title>
	<link rel="stylesheet" href="../../CSS/documento.css">
</head>
<body>
	<div class="centralizar">
		<h1>Fix it: Módulo Administrativo</h1>
		<p>
			<a href="index.php">[Início]</a>
			<?php if ($usuarioLogado["tipo"] === "gerente") { ?>
				<a href="usuarios.php">[Usuários]</a>
				<a href="categorias.php">[Categorias]</a>
				<a href="servicos.php">[Serviços/Artigos]</a>
			<?php } ?>
			<a href="ordens.php">[Ordens de Serviço]</a>
			<a href="../../HTML/index.html">[Ver Site]</a>
			<a href="logout.php">[Sair (<?php echo htmlspecialchars($usuarioLogado["nome"]); ?>)]</a>
		</p>
	</div>
