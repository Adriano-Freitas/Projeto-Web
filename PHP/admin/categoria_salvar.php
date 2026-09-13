<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);

	if ($id > 0) {
		$stmt = $conexao->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
		$stmt->bind_param("si", $nome, $id);
		$stmt->execute();
		registrarAuditoria($conexao, "ATUALIZACAO", "categorias", $id, "Categoria renomeada para " . $nome . ".");
	} else {
		$stmt = $conexao->prepare("INSERT INTO categorias (nome) VALUES (?)");
		$stmt->bind_param("s", $nome);
		$stmt->execute();
		registrarAuditoria($conexao, "CADASTRO", "categorias", $conexao->insert_id, "Nova categoria cadastrada: " . $nome . ".");
	}

	header("Location: categorias.php");
	exit;
?>
