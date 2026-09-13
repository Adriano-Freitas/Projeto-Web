<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["clientes"]) || $_SESSION["clientes"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0 && $id !== $_SESSION["clientes"]["id"]) {
		$stmt = $conexao->prepare("DELETE FROM clientes WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		registrarAuditoria($conexao, "EXCLUSAO", "clientes", $id, "Gerente excluiu um usuário.");
	}

	header("Location: clientes.php");
	exit;
?>
