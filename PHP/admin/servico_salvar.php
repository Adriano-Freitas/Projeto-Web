<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["clientes"]) || $_SESSION["clientes"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$id_categoria = (int) $_POST["id_categoria"];
	$nome = trim($_POST["nome"]);
	$descricao = trim($_POST["descricao"]);
	$valor_base = (float) str_replace(",", ".", $_POST["valor_base"]);
	$status = $_POST["status"];

	if ($id > 0) {
		$stmt = $conexao->prepare("UPDATE servicos SET id_categoria=?, nome=?, descricao=?, valor_base=?, status=? WHERE id=?");
		$stmt->bind_param("issdsi", $id_categoria, $nome, $descricao, $valor_base, $status, $id);
		$stmt->execute();
		registrarAuditoria($conexao, "ATUALIZACAO", "servicos", $id, "Serviço atualizado: " . $nome . ".");
	} else {
		$stmt = $conexao->prepare("INSERT INTO servicos (id_categoria, nome, descricao, valor_base, status) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("issds", $id_categoria, $nome, $descricao, $valor_base, $status);
		$stmt->execute();
		registrarAuditoria($conexao, "CADASTRO", "servicos", $conexao->insert_id, "Novo serviço cadastrado: " . $nome . ".");
	}

	header("Location: servicos.php");
	exit;
?>
