<?php
	include __DIR__ . "/../conexao.php";

	if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"]["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0 && $id !== $_SESSION["usuario"]["id"]) {
		$stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();

		registrarAuditoria($conexao, "EXCLUSAO", "usuarios", $id, "Gerente excluiu um usuário.");
	}

	header("Location: usuarios.php");
	exit;
?>
