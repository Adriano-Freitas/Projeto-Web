<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);
	$email = trim($_POST["email"]);
	$telefone = trim($_POST["telefone"]);
	$cpf = trim($_POST["cpf"]);
	$tipo = $_POST["tipo"];
	$especialidade = trim($_POST["especialidade"]);
	$status = $_POST["status"];
	$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

	// RN01 - não permite duplicidade de CPF/e-mail (validação também
	// feita no cadastro público em registrar.php).
	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE (email = ? OR cpf = ?) AND id != ?");
		$stmt->bind_param("ssi", $email, $cpf, $id);
	} else {
		$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? OR cpf = ?");
		$stmt->bind_param("ss", $email, $cpf);
	}
	$stmt->execute();

	if ($stmt->get_result()->num_rows > 0) {
		header("Location: usuario_form.php?id=" . $id . "&erro=duplicado");
		exit;
	}

	if ($id > 0) {
		if (!empty($senha)) {
			$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
			$stmt = $conexao->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, cpf=?, tipo=?, especialidade=?, status=?, senha=? WHERE id=?");
			$stmt->bind_param("ssssssssi", $nome, $email, $telefone, $cpf, $tipo, $especialidade, $status, $senha_hash, $id);
		} else {
			$stmt = $conexao->prepare("UPDATE usuarios SET nome=?, email=?, telefone=?, cpf=?, tipo=?, especialidade=?, status=? WHERE id=?");
			$stmt->bind_param("sssssssi", $nome, $email, $telefone, $cpf, $tipo, $especialidade, $status, $id);
		}
		$stmt->execute();

		registrarAuditoria($conexao, "ATUALIZACAO", "usuarios", $id, "Gerente atualizou o cadastro de " . $nome . ".");
	} else {
		$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
		$stmt = $conexao->prepare("INSERT INTO usuarios (nome, email, telefone, cpf, tipo, especialidade, status, senha) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssssss", $nome, $email, $telefone, $cpf, $tipo, $especialidade, $status, $senha_hash);
		$stmt->execute();

		registrarAuditoria($conexao, "CADASTRO", "usuarios", $conexao->insert_id, "Gerente cadastrou novo usuário (" . $tipo . "): " . $nome . ".");
	}

	header("Location: usuarios.php");
	exit;
?>
