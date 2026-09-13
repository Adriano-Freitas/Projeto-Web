<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if (!$sessao || !in_array($sessao["tipo"], array("gerente", "tecnico"))) {
		header("Location: ../../HTML/conta.html");
		exit;
	}

	if (isset($_POST["atualizar_status"])) {
		$id_ordem = (int) $_POST["id_ordem"];
		$novo_status = $_POST["status"];
		$conclui_ordem = in_array($novo_status, array("Concluído", "Cancelado"));

		if ($conclui_ordem) {
			$stmt = $conexao->prepare("UPDATE ordens_servico SET status = ?, data_conclusao = NOW() WHERE id_ordem = ?");
			$stmt->execute([$novo_status, $id_ordem]);
		} else {
			$stmt = $conexao->prepare("UPDATE ordens_servico SET status = ? WHERE id_ordem = ?");
			$stmt->execute([$novo_status, $id_ordem]);
		}

		if ($novo_status === "Concluído") {
			$stmtPag = $conexao->prepare("SELECT id_pagamento FROM pagamentos WHERE id_ordem = ?");
			$stmtPag->execute([$id_ordem]);
			$pagExistente = $stmtPag->fetch();

			if ($pagExistente) {
				$stmtUpPag = $conexao->prepare("UPDATE pagamentos SET status = 'pago', data_pagamento = COALESCE(data_pagamento, NOW()) WHERE id_ordem = ?");
				$stmtUpPag->execute([$id_ordem]);
			} else {
				$stmtValor = $conexao->prepare("SELECT valor_total FROM ordens_servico WHERE id_ordem = ?");
				$stmtValor->execute([$id_ordem]);
				$valorTotal = $stmtValor->fetchColumn() ?: 0.00;

				$stmtInsPag = $conexao->prepare("INSERT INTO pagamentos (id_ordem, valor, metodo_pagamento, status, data_pagamento) VALUES (?, ?, 'dinheiro', 'pago', NOW())");
				$stmtInsPag->execute([$id_ordem, $valorTotal]);
			}
		}

		registrarAuditoria($conexao, "ATUALIZACAO_STATUS", "ordens_servico", $id_ordem, "Status atualizado para \"" . $novo_status . "\".");

		$_SESSION["alerta_tipo"] = "sucesso";
		$_SESSION["alerta_mensagem"] = "Status da Ordem #" . $id_ordem . " atualizado para \"" . $novo_status . "\".";

		header("Location: ordens.php");
		exit;
	}

	include "header.php";

	$resultado = $conexao->query(
		"SELECT 
			o.id_ordem AS id, 
			u.nome AS cliente, 
			COALESCE(s.nome, 'Ordem #' || o.id_ordem) AS servico, 
			o.status, 
			o.valor_total, 
			CASE WHEN LOWER(p.status) = 'pago' THEN TRUE ELSE FALSE END AS pago, 
			o.data_abertura
		 FROM ordens_servico o
		 INNER JOIN clientes u ON u.id_cliente = o.id_cliente
		 LEFT JOIN ordem_servico_servicos oss ON oss.id_ordem = o.id_ordem
		 LEFT JOIN servicos s ON s.id_servico = oss.id_servico
		 LEFT JOIN pagamentos p ON p.id_ordem = o.id_ordem
		 ORDER BY o.data_abertura DESC"
	);
?>
	<h2>Ordens de Serviço</h2>

	<div class="justificar">
		<table class="tabela-admin">
			<tr><th>#</th><th>Cliente</th><th>Serviço</th><th>Aberta em</th><th>Valor</th><th>Pago</th><th>Status</th><th>Atualizar</th></tr>
			<?php while ($linha = $resultado->fetch()) { ?>
				<tr>
					<td>#<?php echo $linha["id"]; ?></td>
					<td><?php echo htmlspecialchars($linha["cliente"]); ?></td>
					<td><?php echo htmlspecialchars($linha["servico"]); ?></td>
					<td><?php echo date("d/m/Y", strtotime($linha["data_abertura"])); ?></td>
					<td>R$ <?php echo number_format($linha["valor_total"], 2, ",", "."); ?></td>
					<td><?php echo $linha["pago"] ? "Sim" : "Não"; ?></td>
					<td><?php echo htmlspecialchars($linha["status"]); ?></td>
					<td>
						<form method="post" style="display:flex; gap:5px;">
							<input type="hidden" name="id_ordem" value="<?php echo $linha['id']; ?>">
							<select name="status">
								<?php foreach (array("Aguardando análise", "Em andamento", "Concluído", "Cancelado") as $opcao) { ?>
									<option value="<?php echo $opcao; ?>" <?php echo $linha["status"] === $opcao ? "selected" : ""; ?>><?php echo $opcao; ?></option>
								<?php } ?>
							</select>
							<button type="submit" name="atualizar_status" class="botao">Salvar</button>
						</form>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php
	include "footer.php";
?>
