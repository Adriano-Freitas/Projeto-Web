<?php
	include __DIR__ . "/../conexao.php";

	$conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	function logDebug(string $msg): void {
		$linha = "[" . date("Y-m-d H:i:s") . "] [EXCLUIR] " . $msg;
		error_log($linha);

		// STDERR só existe nativamente no PHP CLI. Rodando via Apache/PHP-FPM
		// (SAPI web) precisamos abrir o stream manualmente.
		static $stderr = null;
		if ($stderr === null) {
			$stderr = @fopen('php://stderr', 'a');
		}
		if ($stderr) {
			fwrite($stderr, $linha . PHP_EOL);
		}
	}

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
	$id_tecnico = isset($_GET["id_tecnico"]) ? (int) $_GET["id_tecnico"] : 0;
	$origem = isset($_GET["origem"]) && $_GET["origem"] === "tecnicos" ? "tecnicos.php" : "usuarios.php";

	logDebug("GET recebido -> " . print_r($_GET, true));
	logDebug("id=[{$id}] id_tecnico=[{$id_tecnico}] sessao_id=[" . ($sessao["id"] ?? "NULO") . "]");

	if ($id_tecnico > 0) {
		try {
			logDebug("Rodando DELETE simples em tecnicos WHERE id_tecnico = {$id_tecnico}");
			$stmt = $conexao->prepare("DELETE FROM tecnicos WHERE id_tecnico = ?");
			$stmt->execute([$id_tecnico]);
			logDebug("rowCount = " . $stmt->rowCount());

			if ($stmt->rowCount() === 0) {
				$_SESSION["alerta_tipo"] = "erro";
				$_SESSION["alerta_mensagem"] = "Técnico não encontrado ou já excluído (id={$id_tecnico}).";
			} else {
				$_SESSION["alerta_tipo"] = "exclusao";
				$_SESSION["alerta_mensagem"] = "Técnico excluído com sucesso.";
			}
		} catch (PDOException $e) {
			logDebug("ERRO PDO: " . $e->getMessage());
			logDebug("errorInfo: " . print_r($e->errorInfo ?? [], true));
			$_SESSION["alerta_tipo"] = "erro";
			$_SESSION["alerta_mensagem"] = "Erro ao excluir o técnico: " . $e->getMessage();
		}
	} else if ($id > 0) {
		if ($id === (int) ($sessao["id"] ?? 0)) {
			logDebug("Bloqueado: tentando excluir o proprio id da sessao.");
			$_SESSION["alerta_tipo"] = "erro";
			$_SESSION["alerta_mensagem"] = "Não é permitido excluir o usuário que está logado atualmente.";
		} else {
			try {
				logDebug("Rodando DELETE simples em clientes WHERE id_cliente = {$id}");
				$stmt = $conexao->prepare("DELETE FROM clientes WHERE id_cliente = ?");
				$stmt->execute([$id]);
				logDebug("rowCount = " . $stmt->rowCount());

				if ($stmt->rowCount() === 0) {
					$_SESSION["alerta_tipo"] = "erro";
					$_SESSION["alerta_mensagem"] = "Usuário não encontrado ou já excluído (id={$id}).";
				} else {
					$_SESSION["alerta_tipo"] = "exclusao";
					$_SESSION["alerta_mensagem"] = "Usuário excluído com sucesso.";
				}
			} catch (PDOException $e) {
				logDebug("ERRO PDO: " . $e->getMessage());
				logDebug("errorInfo: " . print_r($e->errorInfo ?? [], true));
				$_SESSION["alerta_tipo"] = "erro";
				$_SESSION["alerta_mensagem"] = "Erro ao excluir o usuário: " . $e->getMessage();
			}
		}
	} else {
		logDebug("Nenhum id valido recebido (id=0 e id_tecnico=0). Nada foi executado.");
		$_SESSION["alerta_tipo"] = "erro";
		$_SESSION["alerta_mensagem"] = "ID inválido ou não informado.";
	}

	header("Location: " . $origem);
	exit;