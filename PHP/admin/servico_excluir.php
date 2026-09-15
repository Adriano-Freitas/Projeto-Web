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
			$stmtNome = $conexao->prepare("SELECT nome FROM servicos WHERE id_servico = ?");
			$stmtNome->execute([$id]);
			$nomeServico = $stmtNome->fetchColumn();

			if (!$nomeServico) {
				$_SESSION["alerta_tipo"] = "erro";
				$_SESSION["alerta_mensagem"] = "Serviço não encontrado ou já excluído.";
				header("Location: servicos.php");
				exit;
			}

			$stmt = $conexao->prepare("DELETE FROM servicos WHERE id_servico = ?");
			$stmt->execute([$id]);

			if ($stmt->rowCount() === 0) {
				$_SESSION["alerta_tipo"] = "erro";
				$_SESSION["alerta_mensagem"] = "Serviço " . $nomeServico . " não foi excluído. Tente atualizar a página e verificar se ele ainda existe.";
				header("Location: servicos.php");
				exit;
			}

			registrarAuditoria($conexao, "EXCLUSAO", "servicos", $id, "Serviço " . $nomeServico . " excluído do catálogo.");

			$_SESSION["alerta_tipo"] = "exclusao";
			$_SESSION["alerta_mensagem"] = "Serviço " . $nomeServico . " excluído com sucesso.";
		} catch (Exception $e) {
			$_SESSION["alerta_tipo"] = "erro";
			$_SESSION["alerta_mensagem"] = "Não foi possível excluir o serviço: ele pode estar vinculado a ordens de serviço existentes.";
		}
	}

	header("Location: servicos.php");
	exit;
?>
