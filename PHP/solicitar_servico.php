<?php
	// UC02 (RF05) - Abertura de Ordem de Serviço.
	include "conexao.php";

	if (!isset($_SESSION["clientes"])) {
		header("Location: ../HTML/conta.html");
		exit;
	}

	$id_cliente = $_SESSION["clientes"]["id"];

	$pasta_destino = "uploads/pecas/";

	if (!is_dir($pasta_destino)) {
		mkdir($pasta_destino, 0777, true);
	}

	$id_servico = isset($_POST["id_servico"]) ? (int) $_POST["id_servico"] : 0;
	$tipo_pagamento = isset($_POST["tipo_pagamento"]) ? $_POST["tipo_pagamento"] : "";

	$campos_servico = array("tipo_aparelho", "problema_aparelho", "backup", "processador", "placa_mae", "memoria_ram", "placa_video", "armazenamento", "fonte", "gabinete");
	$dados_servico = array();

	foreach ($campos_servico as $campo) {
		if (isset($_POST[$campo]) && $_POST[$campo] !== "") {
			$dados_servico[$campo] = $_POST[$campo];
		}
	}

	$enviados = 0;
	$erros = array();

	if (isset($_FILES["imagens"])) {
		$total = count($_FILES["imagens"]["name"]);

		for ($i = 0; $i < $total; $i++) {
			if ($_FILES["imagens"]["error"][$i] !== UPLOAD_ERR_OK) {
				continue;
			}

			$nome_original = basename($_FILES["imagens"]["name"][$i]);
			$tipo = $_FILES["imagens"]["type"][$i];
			$tmp = $_FILES["imagens"]["tmp_name"][$i];
			$nome_arquivo = "servico" . $id_servico . "_" . time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "", $nome_original);

			if (function_exists("imagecreatefromjpeg") && function_exists("imagecreatefrompng")) {
				$origem = null;
				if ($tipo === "image/jpeg" || $tipo === "image/jpg") {
					$origem = @imagecreatefromjpeg($tmp);
				} elseif ($tipo === "image/png") {
					$origem = @imagecreatefrompng($tmp);
				}

				if ($origem) {
					$largura_original = imagesx($origem);
					$altura_original = imagesy($origem);
					$largura_nova = 800;

					if ($largura_original > $largura_nova) {
						$altura_nova = intval($altura_original * ($largura_nova / $largura_original));
					} else {
						$largura_nova = $largura_original;
						$altura_nova = $altura_original;
					}

					$redimensionada = imagecreatetruecolor($largura_nova, $altura_nova);
					imagecopyresampled($redimensionada, $origem, 0, 0, 0, 0, $largura_nova, $altura_nova, $largura_original, $altura_original);

					imagejpeg($redimensionada, $pasta_destino . $nome_arquivo, 85);

					imagedestroy($origem);
					imagedestroy($redimensionada);
					$enviados++;
					continue;
				}
			}

			if (move_uploaded_file($tmp, $pasta_destino . $nome_arquivo)) {
				$enviados++;
			} else {
				$erros[] = $nome_original . " - erro ao processar imagem.";
			}
		}
	}

	// RF07 - o orçamento inicial é calculado automaticamente com base no
	// valor_base cadastrado no catálogo de serviços (Gerenciar Catálogo -
	// RF04). O valor final só é confirmado após o relatório de gastos do
	// Técnico e a fatura (ver UC03 na especificação de requisitos).
	$stmt = $conexao->prepare("SELECT nome, valor_base FROM servicos WHERE id_servico = ? AND LOWER(status) = 'ativo'");
	$stmt->execute([$id_servico]);
	$servico = $stmt->fetch();

	$id_ordem = null;

	if ($servico) {
		$tipo_aparelho = isset($dados_servico["tipo_aparelho"]) ? $dados_servico["tipo_aparelho"] : "";
		$problema = isset($dados_servico["problema_aparelho"]) ? $dados_servico["problema_aparelho"] : "";
		$valor_total = (float) $servico["valor_base"];

		unset($dados_servico["tipo_aparelho"], $dados_servico["problema_aparelho"]);
		$especificacoes = "";

		foreach ($dados_servico as $campo => $valor) {
			$especificacoes .= ucfirst(str_replace("_", " ", $campo)) . ": " . $valor . "; ";
		}

		$descricao_completa = $problema;
		if (!empty($tipo_aparelho)) {
			$descricao_completa = "[Aparelho: " . $tipo_aparelho . "] " . $descricao_completa;
		}
		if (!empty($especificacoes)) {
			$descricao_completa .= " | " . $especificacoes;
		}

		$id_equipamento = null;
		try {
			$stmtEquip = $conexao->prepare("INSERT INTO equipamentos (id_cliente, tipo, descricao_problema) VALUES (?, ?, ?) RETURNING id_equipamento");
			$stmtEquip->execute([$id_cliente, !empty($tipo_aparelho) ? $tipo_aparelho : "Aparelho", $problema]);
			$id_equipamento = $stmtEquip->fetchColumn();
		} catch (PDOException $e) {
			error_log("Aviso ao registrar equipamento: " . $e->getMessage());
		}

		$stmt = $conexao->prepare(
			"INSERT INTO ordens_servico (id_cliente, id_equipamento, descricao_problema, status, valor_total, data_abertura)
			 VALUES (?, ?, ?, 'Aguardando análise', ?, NOW()) RETURNING id_ordem"
		);
		$stmt->execute([$id_cliente, $id_equipamento, $descricao_completa, $valor_total]);
		$id_ordem = $stmt->fetchColumn();

		if ($id_ordem) {
			$stmtItem = $conexao->prepare("INSERT INTO ordem_servico_servicos (id_ordem, id_servico, quantidade, valor_unitario, subtotal) VALUES (?, ?, 1, ?, ?)");
			$stmtItem->execute([$id_ordem, $id_servico, $valor_total, $valor_total]);
		}

		registrarAuditoria($conexao, "ABERTURA_ORDEM", "ordens_servico", $id_ordem, "Cliente abriu uma nova ordem de serviço (" . $servico["nome"] . ").");
	}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>Solicitação Enviada - Fix it</title>
	<link rel="stylesheet" href="../CSS/documento.css">
</head>
<body>
	<div class="centralizar">
		<h1>Fix it: Assistência técnica</h1>
		<p><a href="../HTML/index.html">[Início]</a> <a href="../HTML/sobre.html">[Sobre]</a> <a href="../HTML/servicos.html">[Serviços]</a> <a href="../HTML/contatos.html">[Contatos]</a> <a href="../HTML/conta.html">[Área cliente]</a></p>
	</div>

	<h2>Solicitação Enviada</h2>

	<div class="justificar">
		<?php if ($id_ordem) { ?>
			<p class="sucesso">Sua solicitação foi recebida com sucesso! Código da Ordem de Serviço: #<?php echo $id_ordem; ?></p>
			<p><b>Serviço:</b> <?php echo htmlspecialchars($servico["nome"]); ?></p>
			<p><b>Orçamento estimado:</b> R$ <?php echo number_format($valor_total, 2, ",", "."); ?></p>
		<?php } else { ?>
			<p class="erro">Não foi possível localizar o serviço selecionado.</p>
		<?php } ?>

		<p><b>Forma de pagamento preferida:</b> <?php echo htmlspecialchars($tipo_pagamento); ?> <i>(o pagamento efetivo é confirmado na Área do Cliente após a conclusão do serviço)</i></p>

		<?php foreach ($dados_servico as $campo => $valor) { ?>
			<p><b><?php echo ucfirst(str_replace("_", " ", $campo)); ?>:</b> <?php echo htmlspecialchars($valor); ?></p>
		<?php } ?>

		<?php if ($enviados > 0) { ?>
			<p class="sucesso"><?php echo $enviados; ?> imagem(ns) enviada(s) e redimensionada(s) com sucesso.</p>
		<?php } ?>

		<?php foreach ($erros as $erro) { ?>
			<p class="erro"><?php echo $erro; ?></p>
		<?php } ?>

		<p><a href="../HTML/servicos.html">[Voltar para Serviços]</a></p>
	</div>

	<footer>
		<h5>Todos os direitos reservados &copy;</h5>
	</footer>
</body>
</html>