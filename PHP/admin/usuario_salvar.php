<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);
	$email = trim($_POST["email"]);
	$telefone = trim($_POST["telefone"]);
	$cpf = trim($_POST["cpf"]);
	$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id_cliente FROM clientes WHERE (email = ? OR cpf = ?) AND id_cliente != ?");
		$stmt->execute([$email, $cpf, $id]);
	} else {
		$stmt = $conexao->prepare("SELECT id_cliente FROM clientes WHERE email = ? OR cpf = ?");
		$stmt->execute([$email, $cpf]);
	}

	if ($stmt->fetch()) {
		header("Location: usuario_form.php?id=" . $id . "&erro=duplicado");
		exit;
	}

	$tipo = isset($_POST["tipo"]) ? trim(strtolower($_POST["tipo"])) : "cliente";
	if (!in_array($tipo, array("cliente", "tecnico", "gerente"))) {
		$tipo = "cliente";
	}

	if ($id > 0) {
		if (!empty($senha)) {
			$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
			$stmt = $conexao->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, tipo=?, senha=? WHERE id_cliente=?");
			$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $senha_hash, $id]);
		} else {
			$stmt = $conexao->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, tipo=? WHERE id_cliente=?");
			$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $id]);
		}

		registrarAuditoria($conexao, "ATUALIZACAO", "clientes", $id, "Gerente atualizou o cadastro de " . $nome . ".");
		$_SESSION["alerta_tipo"] = "sucesso";
		$_SESSION["alerta_mensagem"] = "Usuário " . $nome . " atualizado com sucesso.";
	} else {
		$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
		$stmt = $conexao->prepare("INSERT INTO clientes (nome, email, telefone, cpf, tipo, senha) VALUES (?, ?, ?, ?, ?, ?) RETURNING id_cliente");
		$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $senha_hash]);
		$novoId = $stmt->fetchColumn();

		registrarAuditoria($conexao, "CADASTRO", "clientes", $novoId, "Gerente cadastrou novo cliente: " . $nome . ".");
		$_SESSION["alerta_tipo"] = "sucesso";
		$_SESSION["alerta_mensagem"] = "Usuário " . $nome . " cadastrado com sucesso.";
	}

	header("Location: usuarios.php");
	exit;
?>
