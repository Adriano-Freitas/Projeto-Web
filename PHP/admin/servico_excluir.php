<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["clientes"]) || $_SESSION["clientes"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0) {
		$stmt = $conexao->prepare("DELETE FROM servicos WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		registrarAuditoria($conexao, "EXCLUSAO", "servicos", $id, "Serviço excluído do catálogo.");
	}

	header("Location: servicos.php");
	exit;
?>
