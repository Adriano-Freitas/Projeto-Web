<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || !in_array($sessao["tipo"], array("gerente", "tecnico"))) {
		header("Location: ../../HTML/conta.html");
		exit;
	}

	$clientesLogado = $sessao;
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
			<?php if ($clientesLogado["tipo"] === "gerente") { ?>
				<a href="usuarios.php">Usuários</a>
				<a href="categorias.php">Categorias</a>
				<a href="servicos.php">Serviços/Artigos</a>
			<?php } ?>
			<a href="ordens.php">Ordens de Serviço</a>
			<a href="../../HTML/index.html">Ver Site</a>
			<a href="logout.php">Sair (<?php echo htmlspecialchars($clientesLogado["nome"]); ?>)</a>
		</p>
	</div>
