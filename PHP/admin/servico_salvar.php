<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);
	$descricao = trim($_POST["descricao"]);
	$valor_base = (float) str_replace(",", ".", $_POST["valor_base"]);
	$status = $_POST["status"];

	if ($id > 0) {
		$stmt = $conexao->prepare("UPDATE servicos SET nome=?, descricao=?, valor_base=?, status=? WHERE id_servico=?");
		$stmt->execute([$nome, $descricao, $valor_base, $status, $id]);
		registrarAuditoria($conexao, "ATUALIZACAO", "servicos", $id, "Serviço atualizado: " . $nome . ".");
		$_SESSION["alerta_tipo"] = "sucesso";
		$_SESSION["alerta_mensagem"] = "Serviço " . $nome . " atualizado com sucesso.";
	} else {
		$stmt = $conexao->prepare("INSERT INTO servicos (nome, descricao, valor_base, status, data_solicitacao) VALUES (?, ?, ?, ?, NOW()) RETURNING id_servico");
		$stmt->execute([$nome, $descricao, $valor_base, $status]);
		$novoId = $stmt->fetchColumn();
		registrarAuditoria($conexao, "CADASTRO", "servicos", $novoId, "Novo serviço cadastrado: " . $nome . ".");
		$_SESSION["alerta_tipo"] = "sucesso";
		$_SESSION["alerta_mensagem"] = "Serviço " . $nome . " cadastrado com sucesso.";
	}

	header("Location: servicos.php");
	exit;
?>
