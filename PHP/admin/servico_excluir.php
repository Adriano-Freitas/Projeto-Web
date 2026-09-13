<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0) {
		$stmt = $conexao->prepare("DELETE FROM servicos WHERE id_servico = ?");
		$stmt->execute([$id]);
		registrarAuditoria($conexao, "EXCLUSAO", "servicos", $id, "Serviço excluído do catálogo.");
	}

	header("Location: servicos.php");
	exit;
?>
