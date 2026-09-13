<?php
	include "header.php";

	// RF09 - Controlar o Status do Serviço (iniciar/atualizar/encerrar).
	// Visível para Técnico e Gerente.
	if (isset($_POST["atualizar_status"])) {
		$id_ordem = (int) $_POST["id_ordem"];
		$novo_status = $_POST["status"];
		$conclui_ordem = in_array($novo_status, array("Concluído", "Cancelado"));

		if ($conclui_ordem) {
			$stmt = $conexao->prepare("UPDATE ordens_servico SET status = ?, data_conclusao = NOW(), id_tecnico = ? WHERE id = ?");
		} else {
			$stmt = $conexao->prepare("UPDATE ordens_servico SET status = ?, id_tecnico = ? WHERE id = ?");
		}

		$id_tecnico_responsavel = $clientesLogado["tipo"] === "tecnico" ? $clientesLogado["id"] : null;
		$stmt->bind_param("sii", $novo_status, $id_tecnico_responsavel, $id_ordem);
		$stmt->execute();

		registrarAuditoria($conexao, "ATUALIZACAO_STATUS", "ordens_servico", $id_ordem, "Status atualizado para \"" . $novo_status . "\".");

		header("Location: ordens.php");
		exit;
	}

	$resultado = $conexao->query(
		"SELECT o.id, u.nome AS cliente, s.nome AS servico, o.status, o.valor_total, o.pago, o.data_abertura
		 FROM ordens_servico o
		 INNER JOIN clientes u ON u.id = o.id_cliente
		 INNER JOIN servicos s ON s.id = o.id_servico
		 ORDER BY o.data_abertura DESC"
	);
?>
	<h2>Ordens de Serviço</h2>

	<div class="justificar">
		<table class="tabela-admin">
			<tr><th>#</th><th>Cliente</th><th>Serviço</th><th>Aberta em</th><th>Valor</th><th>Pago</th><th>Status</th><th>Atualizar</th></tr>
			<?php while ($linha = $resultado->fetch_assoc()) { ?>
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
