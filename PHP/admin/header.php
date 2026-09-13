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
				<a href="servicos.php">Serviços</a>
			<?php } ?>
			<a href="ordens.php">Ordens de Serviço</a>
			<a href="../../HTML/index.html">Ver Site</a>
			<a href="logout.php">Sair (<?php echo htmlspecialchars($clientesLogado["nome"]); ?>)</a>
		</p>
	</div>

<?php
	$msgAlerta = $_SESSION["alerta_mensagem"] ?? $_SESSION["mensagem_sucesso"] ?? $_SESSION["mensagem_erro"] ?? null;
	$tipoAlerta = $_SESSION["alerta_tipo"] ?? (!empty($_SESSION["mensagem_erro"]) ? "erro" : "sucesso");
	unset($_SESSION["alerta_mensagem"], $_SESSION["alerta_tipo"], $_SESSION["mensagem_sucesso"], $_SESSION["mensagem_erro"]);
?>
<?php if ($msgAlerta) { 
	$classeAlerta = ($tipoAlerta === "exclusao" || $tipoAlerta === "erro") ? "toast-exclusao" : "toast-sucesso";
?>
	<div id="toast-alerta" class="toast-popup <?php echo $classeAlerta; ?>">
		<span><?php echo htmlspecialchars($msgAlerta); ?></span>
		<button type="button" class="toast-close" onclick="fecharAlerta()">&times;</button>
	</div>
	<script>
		setTimeout(function() {
			var el = document.getElementById("toast-alerta");
			if (el) {
				el.style.opacity = "0";
				el.style.transform = "translate(-50%, -20px)";
				setTimeout(function() { el.remove(); }, 500);
			}
		}, 3000);
		function fecharAlerta() {
			var el = document.getElementById("toast-alerta");
			if (el) el.remove();
		}
	</script>
<?php } ?>
