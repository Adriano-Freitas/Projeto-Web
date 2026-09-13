<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["clientes"]) || $_SESSION["clientes"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0) {
		$stmt = $conexao->prepare("DELETE FROM categorias WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		registrarAuditoria($conexao, "EXCLUSAO", "categorias", $id, "Categoria excluída.");
	}

	header("Location: categorias.php");
	exit;
?>
