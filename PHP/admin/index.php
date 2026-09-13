<?php
	// Tela de Login do Módulo Administrativo (Gerente e Técnico).
	include __DIR__ . "/../conexao.php";

	$erro = "";

	if ($_SERVER["REQUEST_METHOD"] === "POST") {
		$email = trim(strtolower($_POST["email"]));
		$senha = $_POST["senha"];

		$stmt = $conexao->prepare("SELECT id_cliente AS id, nome, email, telefone, cpf, senha, tipo FROM clientes WHERE LOWER(email) = ?");
		$stmt->execute([$email]);
		$linha = $stmt->fetch();

		$senhaValida = false;
		if ($linha) {
			if (password_verify($senha, $linha["senha"]) || $senha === $linha["senha"]) {
				$senhaValida = true;
			}
		}

		if (!$linha || !$senhaValida) {
			$erro = "E-mail ou senha inválidos.";
		} else {
			$tipo = $linha["tipo"] ?? "cliente";

			if (!in_array($tipo, array("gerente", "tecnico"))) {
				$erro = "Acesso negado. Apenas Gerentes e Técnicos podem acessar o Módulo Administrativo.";
			} else {
				$_SESSION["clientes"] = array(
					"id" => (int) $linha["id"],
					"nome" => $linha["nome"],
					"email" => $linha["email"],
					"telefone" => $linha["telefone"] ?? "",
					"cpf" => $linha["cpf"] ?? "",
					"tipo" => $tipo
				);
				$_SESSION["usuario"] = $_SESSION["clientes"];

				registrarAuditoria($conexao, "LOGIN_ADMIN", "clientes", $linha["id"], "Login no Módulo Administrativo.");

				header("Location: index.php");
				exit;
			}
		}
	}

	$sessaoAdmin = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if ($sessaoAdmin && in_array($sessaoAdmin["tipo"], array("gerente", "tecnico"))) {
		include "header.php";
?>
		<h2>Painel Administrativo</h2>
		<div class="justificar">
			<p>Bem-vindo(a), <b><?php echo htmlspecialchars($clientesLogado["nome"]); ?></b>
			 (<?php echo $clientesLogado["tipo"] === "gerente" ? "Gerente" : "Técnico"; ?>).</p>

			<?php if ($clientesLogado["tipo"] === "gerente") { ?>
				<p>Como Gerente, você pode gerenciar <a href="usuarios.php">Usuários</a>,
				<a href="categorias.php">Categorias</a> e o <a href="servicos.php">Catálogo de Serviços</a>.</p>
			<?php } else { ?>
				<p>Como Técnico, você pode visualizar e atualizar as <a href="ordens.php">Ordens de Serviço</a>.</p>
			<?php } ?>
		</div>
<?php
		include "footer.php";
	} else {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>Login Administrativo - Fix it</title>
	<link rel="stylesheet" href="../../CSS/documento.css">
</head>
<body>
	<div class="centralizar">
		<h1>Fix it: Módulo Administrativo</h1>
	</div>

	<div class="cartao" style="max-width: 400px; margin-left: auto; margin-right: auto;">
		<h3>Acesso Restrito (Gerente / Técnico)</h3>
		<form method="post">
			<label for="email"><b>E-mail:</b></label><br>
			<input type="email" id="email" name="email" required><br>

			<label for="senha"><b>Senha:</b></label><br>
			<input type="password" id="senha" name="senha" required><br>

			<?php if (!empty($erro)) { ?>
				<p class="erro"><?php echo htmlspecialchars($erro); ?></p>
			<?php } ?>

			<button type="submit" class="botao">Entrar</button>
		</form>
		<p class="dica"><a href="../../HTML/index.html">Voltar ao site</a></p>
	</div>

	<footer>
		<h5>Todos os direitos reservados &copy;</h5>
	</footer>
</body>
</html>
<?php
	}
?>
