<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0 && $id !== (int) ($sessao["id"] ?? 0)) {
		$stmt = $conexao->prepare("DELETE FROM clientes WHERE id_cliente = ?");
		$stmt->execute([$id]);

		registrarAuditoria($conexao, "EXCLUSAO", "clientes", $id, "Gerente excluiu um cliente.");
	}

	header("Location: usuarios.php");
	exit;
?>
