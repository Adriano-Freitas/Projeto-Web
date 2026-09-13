<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);

	try {
		if ($id > 0) {
			$stmt = $conexao->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
			$stmt->execute([$nome, $id]);
			registrarAuditoria($conexao, "ATUALIZACAO", "categorias", $id, "Categoria renomeada para " . $nome . ".");
		} else {
			$stmt = $conexao->prepare("INSERT INTO categorias (nome) VALUES (?) RETURNING id");
			$stmt->execute([$nome]);
			$novoId = $stmt->fetchColumn();
			registrarAuditoria($conexao, "CADASTRO", "categorias", $novoId, "Nova categoria cadastrada: " . $nome . ".");
		}
	} catch (PDOException $e) {
	}

	header("Location: categorias.php");
	exit;
?>
