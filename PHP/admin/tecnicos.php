<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$filtroStatus = isset($_GET["status"]) ? trim(strtolower($_GET["status"])) : "";

	$where = array();
	$params = array();

	if (!empty($filtroStatus) && in_array($filtroStatus, array("ativo", "inativo"))) {
		$where[] = "LOWER(t.status) = ?";
		$params[] = $filtroStatus;
	}

	$sql = "SELECT t.id_tecnico, t.nome, t.email, t.telefone, t.especialidade, t.status, c.id_cliente
			FROM tecnicos t
			LEFT JOIN clientes c ON LOWER(c.email) = LOWER(t.email)";

	if (!empty($where)) {
		$sql .= " WHERE " . implode(" AND ", $where);
	}
	$sql .= " ORDER BY t.id_tecnico DESC";

	$stmt = $conexao->prepare($sql);
	$stmt->execute($params);

	$totalAtivos = (int) $conexao->query("SELECT COUNT(*) FROM tecnicos WHERE LOWER(status) = 'ativo'")->fetchColumn();
	$totalInativos = (int) $conexao->query("SELECT COUNT(*) FROM tecnicos WHERE LOWER(status) = 'inativo'")->fetchColumn();
	$totalTodos = $totalAtivos + $totalInativos;
?>
	<h2>Gestão da Equipe Técnica</h2>

	<div class="justificar">
		<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 20px;">
			<div>
				<a href="usuario_form.php?tipo=tecnico&origem=tecnicos" class="botao">+ Novo Técnico</a>
			</div>

			<form method="get" action="tecnicos.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 0;">
				<label style="font-weight: 600; font-size: 0.9em; margin: 0;">Status:</label>
				<select name="status" style="padding: 6px 10px; border-radius: 6px; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color);">
					<option value="">Todos os Status</option>
					<option value="ativo" <?php echo $filtroStatus === "ativo" ? "selected" : ""; ?>>Ativo</option>
					<option value="inativo" <?php echo $filtroStatus === "inativo" ? "selected" : ""; ?>>Inativo</option>
				</select>

				<button type="submit" class="botao" style="padding: 6px 14px;">Filtrar</button>
				<?php if (!empty($filtroStatus)) { ?>
					<a href="tecnicos.php" class="botao secundario" style="padding: 6px 12px;">Limpar</a>
				<?php } ?>
			</form>
		</div>

		<div style="display: flex; gap: 8px; margin-bottom: 15px;">
			<a href="tecnicos.php" class="botao <?php echo empty($filtroStatus) ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Todos (<?php echo $totalTodos; ?>)</a>
			<a href="tecnicos.php?status=ativo" class="botao <?php echo $filtroStatus === 'ativo' ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Ativos (<?php echo $totalAtivos; ?>)</a>
			<a href="tecnicos.php?status=inativo" class="botao <?php echo $filtroStatus === 'inativo' ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Inativos (<?php echo $totalInativos; ?>)</a>
		</div>

		<table class="tabela-admin">
			<tr>
				<th>#</th>
				<th>Técnico</th>
				<th>E-mail</th>
				<th>Telefone</th>
				<th>Especialidade</th>
				<th>Status</th>
				<th>Ações</th>
			</tr>
			<?php 
			$temRegistros = false;
			while ($linha = $stmt->fetch()) { 
				$temRegistros = true;
				$statusLower = strtolower($linha["status"] ?? "ativo");
				$linkEdit = !empty($linha["id_cliente"]) ? "usuario_form.php?id=" . $linha["id_cliente"] . "&origem=tecnicos" : "usuario_form.php?id_tecnico=" . $linha["id_tecnico"] . "&origem=tecnicos";
			?>
				<tr>
					<td>#<?php echo $linha["id_tecnico"]; ?></td>
					<td><b><?php echo htmlspecialchars($linha["nome"]); ?></b></td>
					<td><?php echo htmlspecialchars($linha["email"]); ?></td>
					<td><?php echo htmlspecialchars($linha["telefone"]); ?></td>
					<td>
						<span style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; padding: 4px 10px; border-radius: 4px; font-weight: 600;">
							<?php echo htmlspecialchars($linha["especialidade"] ?: "Manutenção Geral"); ?>
						</span>
					</td>
					<td>
						<span style="color: <?php echo $statusLower === 'ativo' ? '#2ecc71' : '#e74c3c'; ?>; font-weight: 600;">
							<?php echo ucfirst($statusLower); ?>
						</span>
					</td>
					<td>
						<a href="<?php echo $linkEdit; ?>">[Editar Especialidade]</a>
						<a href="usuario_excluir.php?id_tecnico=<?php echo $linha['id_tecnico']; ?>&origem=tecnicos" onclick="return confirm('Excluir este técnico?');" style="color: #e74c3c;">[Excluir]</a>
					</td>
				</tr>
			<?php } 
			if (!$temRegistros) { ?>
				<tr>
					<td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">Nenhum técnico encontrado com os filtros selecionados.</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php
	include "footer.php";
?>
