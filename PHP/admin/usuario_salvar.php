<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || $sessao["tipo"] !== "gerente") {
		header("Location: index.php");
		exit;
	}

	$id = (int) $_POST["id"];
	$nome = trim($_POST["nome"]);
	$email = trim($_POST["email"]);
	$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

	$telefone = trim($_POST["telefone"]);
	$numerosTel = preg_replace("/\D/", "", $telefone);
	if (strlen($numerosTel) === 11) {
		$telefone = sprintf("(%s) %s-%s", substr($numerosTel, 0, 2), substr($numerosTel, 2, 5), substr($numerosTel, 7, 4));
	} elseif (strlen($numerosTel) === 10) {
		$telefone = sprintf("(%s) %s-%s", substr($numerosTel, 0, 2), substr($numerosTel, 2, 4), substr($numerosTel, 6, 4));
	} else {
		$telefone = substr($telefone, 0, 25);
	}

	$cpf = trim($_POST["cpf"]);
	$numerosCpf = preg_replace("/\D/", "", $cpf);
	if (strlen($numerosCpf) === 11) {
		$cpf = sprintf("%s.%s.%s-%s", substr($numerosCpf, 0, 3), substr($numerosCpf, 3, 3), substr($numerosCpf, 6, 3), substr($numerosCpf, 9, 2));
	} else {
		$cpf = substr($cpf, 0, 18);
	}

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id_cliente FROM clientes WHERE (email = ? OR cpf = ?) AND id_cliente != ?");
		$stmt->execute([$email, $cpf, $id]);
	} else {
		$stmt = $conexao->prepare("SELECT id_cliente FROM clientes WHERE email = ? OR cpf = ?");
		$stmt->execute([$email, $cpf]);
	}

	if ($stmt->fetch()) {
		$_SESSION["alerta_tipo"] = "erro";
		$_SESSION["alerta_mensagem"] = "Já existe um usuário cadastrado com este E-mail ou CPF.";
		header("Location: usuario_form.php?id=" . $id);
		exit;
	}

	$tipo = isset($_POST["tipo"]) ? trim(strtolower($_POST["tipo"])) : "cliente";
	if (!in_array($tipo, array("cliente", "tecnico", "gerente"))) {
		$tipo = "cliente";
	}

	$especialidade = ($tipo === "tecnico") ? trim($_POST["especialidade"] ?? "Manutenção Geral") : null;
	if ($tipo === "tecnico" && empty($especialidade)) {
		$especialidade = "Manutenção Geral";
	}

	try {
		if ($id > 0) {
			$stmtAntigo = $conexao->prepare("SELECT email FROM clientes WHERE id_cliente = ?");
			$stmtAntigo->execute([$id]);
			$emailAntigo = $stmtAntigo->fetchColumn() ?: $email;

			if (!empty($senha)) {
				$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
				$stmt = $conexao->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, tipo=?, especialidade=?, senha=? WHERE id_cliente=?");
				$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $especialidade, $senha_hash, $id]);
			} else {
				$stmt = $conexao->prepare("UPDATE clientes SET nome=?, email=?, telefone=?, cpf=?, tipo=?, especialidade=? WHERE id_cliente=?");
				$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $especialidade, $id]);
			}

			if ($tipo === "tecnico") {
				$stmtTec = $conexao->prepare("SELECT id_tecnico FROM tecnicos WHERE LOWER(email) = ? OR LOWER(email) = ?");
				$stmtTec->execute([strtolower($emailAntigo), strtolower($email)]);
				$idTec = $stmtTec->fetchColumn();
				if ($idTec) {
					$stmtUpTec = $conexao->prepare("UPDATE tecnicos SET nome=?, email=?, telefone=?, especialidade=? WHERE id_tecnico=?");
					$stmtUpTec->execute([$nome, $email, $telefone, $especialidade, $idTec]);
				} else {
					$stmtInsTec = $conexao->prepare("INSERT INTO tecnicos (nome, email, telefone, especialidade, status) VALUES (?, ?, ?, ?, 'ativo')");
					$stmtInsTec->execute([$nome, $email, $telefone, $especialidade]);
				}
			}

			registrarAuditoria($conexao, "ATUALIZACAO", "clientes", $id, "Gerente atualizou o cadastro de " . $nome . ".");
			$_SESSION["alerta_tipo"] = "sucesso";
			$_SESSION["alerta_mensagem"] = "Usuário " . $nome . " atualizado com sucesso.";
		} else {
			$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
			$stmt = $conexao->prepare("INSERT INTO clientes (nome, email, telefone, cpf, tipo, especialidade, senha) VALUES (?, ?, ?, ?, ?, ?, ?) RETURNING id_cliente");
			$stmt->execute([$nome, $email, $telefone, $cpf, $tipo, $especialidade, $senha_hash]);
			$novoId = $stmt->fetchColumn();

			if ($tipo === "tecnico") {
				$stmtInsTec = $conexao->prepare("INSERT INTO tecnicos (nome, email, telefone, especialidade, status) VALUES (?, ?, ?, ?, 'ativo')");
				$stmtInsTec->execute([$nome, $email, $telefone, $especialidade]);
			}

			registrarAuditoria($conexao, "CADASTRO", "clientes", $novoId, "Gerente cadastrou novo cliente: " . $nome . ".");
			$_SESSION["alerta_tipo"] = "sucesso";
			$_SESSION["alerta_mensagem"] = "Usuário " . $nome . " cadastrado com sucesso.";
		}

		header("Location: usuarios.php");
		exit;
	} catch (Exception $e) {
		$_SESSION["alerta_tipo"] = "erro";
		$_SESSION["alerta_mensagem"] = "Erro ao salvar usuário: " . $e->getMessage();
		header("Location: usuario_form.php?id=" . $id);
		exit;
	}
?>
