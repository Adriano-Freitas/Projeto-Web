<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0) {
		try {
			$stmt = $conexao->prepare("DELETE FROM categorias WHERE id = ?");
			$stmt->execute([$id]);
			registrarAuditoria($conexao, "EXCLUSAO", "categorias", $id, "Categoria excluída.");
		} catch (PDOException $e) {
			// Tabela não existe
		}
	}

	header("Location: categorias.php");
	exit;
?>
