<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

	if ($id > 0 && $id !== (int) ($sessao["id"] ?? 0)) {
		try {
			$conexao->beginTransaction();

			$stmtCliente = $conexao->prepare("SELECT nome, email FROM clientes WHERE id_cliente = ?");
			$stmtCliente->execute([$id]);
			$clienteInfo = $stmtCliente->fetch();
			$nomeCliente = $clienteInfo["nome"] ?? "ID " . $id;

			$stmtOrdens = $conexao->prepare("SELECT id_ordem FROM ordens_servico WHERE id_cliente = ?");
			$stmtOrdens->execute([$id]);
			$ordensIds = $stmtOrdens->fetchAll(PDO::FETCH_COLUMN);

			if (!empty($ordensIds)) {
				$placeholders = implode(",", array_fill(0, count($ordensIds), "?"));
				
				$stmtDelPag = $conexao->prepare("DELETE FROM pagamentos WHERE id_ordem IN ($placeholders)");
				$stmtDelPag->execute($ordensIds);

				$stmtDelOss = $conexao->prepare("DELETE FROM ordem_servico_servicos WHERE id_ordem IN ($placeholders)");
				$stmtDelOss->execute($ordensIds);

				$stmtDelOrdens = $conexao->prepare("DELETE FROM ordens_servico WHERE id_ordem IN ($placeholders)");
				$stmtDelOrdens->execute($ordensIds);
			}

			$stmtDelEquip = $conexao->prepare("DELETE FROM equipamentos WHERE id_cliente = ?");
			$stmtDelEquip->execute([$id]);

			if (!empty($clienteInfo["email"])) {
				$stmtTec = $conexao->prepare("SELECT id_tecnico FROM tecnicos WHERE email = ?");
				$stmtTec->execute([$clienteInfo["email"]]);
				$idTec = $stmtTec->fetchColumn();
				if ($idTec) {
					$stmtUnsetTec = $conexao->prepare("UPDATE ordens_servico SET id_tecnico = NULL WHERE id_tecnico = ?");
					$stmtUnsetTec->execute([$idTec]);

					$stmtDelTec = $conexao->prepare("DELETE FROM tecnicos WHERE id_tecnico = ?");
					$stmtDelTec->execute([$idTec]);
				}
			}

			$stmt = $conexao->prepare("DELETE FROM clientes WHERE id_cliente = ?");
			$stmt->execute([$id]);

			registrarAuditoria($conexao, "EXCLUSAO", "clientes", $id, "Gerente excluiu o usuário " . $nomeCliente . ".");

			$conexao->commit();
			$_SESSION["mensagem_sucesso"] = "Usuário " . $nomeCliente . " excluído com sucesso.";
		} catch (Exception $e) {
			if ($conexao->inTransaction()) {
				$conexao->rollBack();
			}
			$_SESSION["mensagem_erro"] = "Erro ao excluir o usuário: " . $e->getMessage();
		}
	} else if ($id === (int) ($sessao["id"] ?? 0)) {
		$_SESSION["mensagem_erro"] = "Não é permitido excluir o usuário que está logado atualmente.";
	}

	header("Location: usuarios.php");
	exit;
?>
