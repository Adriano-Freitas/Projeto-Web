<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$filtroTipo = isset($_GET["tipo"]) ? trim(strtolower($_GET["tipo"])) : "";
	$filtroStatus = isset($_GET["status"]) ? trim(strtolower($_GET["status"])) : "";

	$where = array();
	$params = array();

	if (!empty($filtroTipo) && in_array($filtroTipo, array("cliente", "tecnico", "gerente"))) {
		$where[] = "LOWER(tipo) = ?";
		$params[] = $filtroTipo;
	}

	if (!empty($filtroStatus) && in_array($filtroStatus, array("ativo", "inativo"))) {
		$where[] = "LOWER(COALESCE(status, 'ativo')) = ?";
		$params[] = $filtroStatus;
	}

	$sql = "SELECT id_cliente AS id, nome, email, telefone, cpf, endereco, tipo, especialidade, COALESCE(status, 'Ativo') AS status 
			FROM clientes";
	if (!empty($where)) {
		$sql .= " WHERE " . implode(" AND ", $where);
	}
	$sql .= " ORDER BY id_cliente DESC";

	$stmt = $conexao->prepare($sql);
	$stmt->execute($params);

	$totalTodos = (int) $conexao->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
	$totalTecnicos = (int) $conexao->query("SELECT COUNT(*) FROM clientes WHERE LOWER(tipo) = 'tecnico'")->fetchColumn();
	$totalClientes = (int) $conexao->query("SELECT COUNT(*) FROM clientes WHERE LOWER(tipo) = 'cliente'")->fetchColumn();
	$totalGerentes = (int) $conexao->query("SELECT COUNT(*) FROM clientes WHERE LOWER(tipo) = 'gerente'")->fetchColumn();

	$stmtTecnicos = $conexao->query("
		SELECT t.id_tecnico, t.nome, t.email, t.telefone, t.especialidade, t.status, c.id_cliente 
		FROM tecnicos t 
		LEFT JOIN clientes c ON LOWER(c.email) = LOWER(t.email) 
		ORDER BY t.id_tecnico DESC
	");
?>
	<h2>Gestão de Usuários</h2>

	<div class="justificar">
		<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 20px;">
			<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
				<a href="usuario_form.php" class="botao">+ Novo Usuário</a>
				<a href="usuario_form.php?tipo=tecnico" class="botao secundario">+ Novo Técnico</a>
			</div>

			<form method="get" action="usuarios.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin: 0;">
				<label style="font-weight: 600; font-size: 0.9em; margin: 0;">Tipo:</label>
				<select name="tipo" style="padding: 6px 10px; border-radius: 6px; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color);">
					<option value="">Todos os Tipos</option>
					<option value="tecnico" <?php echo $filtroTipo === "tecnico" ? "selected" : ""; ?>>Técnico</option>
					<option value="cliente" <?php echo $filtroTipo === "cliente" ? "selected" : ""; ?>>Cliente</option>
					<option value="gerente" <?php echo $filtroTipo === "gerente" ? "selected" : ""; ?>>Gerente</option>
				</select>

				<label style="font-weight: 600; font-size: 0.9em; margin: 0;">Status:</label>
				<select name="status" style="padding: 6px 10px; border-radius: 6px; background: var(--bg-input); color: var(--text-main); border: 1px solid var(--border-color);">
					<option value="">Todos os Status</option>
					<option value="ativo" <?php echo $filtroStatus === "ativo" ? "selected" : ""; ?>>Ativo</option>
					<option value="inativo" <?php echo $filtroStatus === "inativo" ? "selected" : ""; ?>>Inativo</option>
				</select>

				<button type="submit" class="botao" style="padding: 6px 14px;">Filtrar</button>
				<?php if (!empty($filtroTipo) || !empty($filtroStatus)) { ?>
					<a href="usuarios.php" class="botao secundario" style="padding: 6px 12px;">Limpar</a>
				<?php } ?>
			</form>
		</div>

		<div style="display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap;">
			<a href="usuarios.php" class="botao <?php echo empty($filtroTipo) ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Todos (<?php echo $totalTodos; ?>)</a>
			<a href="usuarios.php?tipo=tecnico" class="botao <?php echo $filtroTipo === 'tecnico' ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Técnicos (<?php echo $totalTecnicos; ?>)</a>
			<a href="usuarios.php?tipo=cliente" class="botao <?php echo $filtroTipo === 'cliente' ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Clientes (<?php echo $totalClientes; ?>)</a>
			<a href="usuarios.php?tipo=gerente" class="botao <?php echo $filtroTipo === 'gerente' ? '' : 'secundario'; ?>" style="padding: 4px 10px; font-size: 0.85em;">Gerentes (<?php echo $totalGerentes; ?>)</a>
		</div>

		<table class="tabela-admin">
			<tr>
				<th>#</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>CPF</th><th>Tipo</th><th>Status</th><th>Ações</th>
			</tr>
			<?php 
			$temUsuarios = false;
			while ($linha = $stmt->fetch()) { 
				$temUsuarios = true;
				$tipoOriginal = strtolower($linha["tipo"] ?? "cliente");
				$esp = (!empty($linha["especialidade"]) && $tipoOriginal === "tecnico") ? " (" . $linha["especialidade"] . ")" : "";
				$tipoTexto = $tipoOriginal === "gerente" ? "Gerente" : ($tipoOriginal === "tecnico" ? ("Técnico" . $esp) : "Cliente");
				$statusOriginal = ucfirst(strtolower($linha["status"] ?? "Ativo"));
				$statusCor = strtolower($statusOriginal) === "ativo" ? "#2ecc71" : "#e74c3c";
			?>
				<tr>
					<td>#<?php echo $linha["id"]; ?></td>
					<td><?php echo htmlspecialchars($linha["nome"]); ?></td>
					<td><?php echo htmlspecialchars($linha["email"]); ?></td>
					<td><?php echo htmlspecialchars($linha["telefone"]); ?></td>
					<td><?php echo htmlspecialchars($linha["cpf"]); ?></td>
					<td>
						<?php if ($tipoOriginal === "tecnico") { ?>
							<b style="color: #2ecc71;"><?php echo htmlspecialchars($tipoTexto); ?></b>
						<?php } else { ?>
							<b><?php echo htmlspecialchars($tipoTexto); ?></b>
						<?php } ?>
					</td>
					<td><span style="color: <?php echo $statusCor; ?>; font-weight: 600;"><?php echo htmlspecialchars($statusOriginal); ?></span></td>
					<td>
						<a href="usuario_form.php?id=<?php echo $linha['id']; ?>">[Editar]</a>
						<a href="usuario_excluir.php?id=<?php echo $linha['id']; ?>" onclick="return confirm('Excluir este usuário?');" style="color: #e74c3c;">[Excluir]</a>
					</td>
				</tr>
			<?php } 
			if (!$temUsuarios) { ?>
				<tr>
					<td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">Nenhum usuário encontrado com os filtros selecionados.</td>
				</tr>
			<?php } ?>
		</table>

		<div style="margin-top: 45px; padding-top: 25px; border-top: 1px solid var(--border-color);">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
				<div>
					<h3 style="margin: 0; color: var(--primary-color);">Equipe Técnica (Especialidades e Atuação)</h3>
					<p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.9em;">Visualize, altere a especialidade ou gerencie os técnicos cadastrados no sistema.</p>
				</div>
				<a href="usuario_form.php?tipo=tecnico" class="botao secundario">+ Novo Técnico</a>
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
				$temTecnicos = false;
				while ($tec = $stmtTecnicos->fetch()) { 
					$temTecnicos = true;
					$statusTecLower = strtolower($tec["status"] ?? "ativo");
					$linkEditTec = !empty($tec["id_cliente"]) ? "usuario_form.php?id=" . $tec["id_cliente"] : "usuario_form.php?id_tecnico=" . $tec["id_tecnico"];
				?>
					<tr>
						<td>#<?php echo $tec["id_tecnico"]; ?></td>
						<td><b><?php echo htmlspecialchars($tec["nome"]); ?></b></td>
						<td><?php echo htmlspecialchars($tec["email"]); ?></td>
						<td><?php echo htmlspecialchars($tec["telefone"]); ?></td>
						<td>
							<span style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; padding: 4px 10px; border-radius: 4px; font-weight: 600;">
								<?php echo htmlspecialchars($tec["especialidade"] ?: "Manutenção Geral"); ?>
							</span>
						</td>
						<td>
							<span style="color: <?php echo $statusTecLower === 'ativo' ? '#2ecc71' : '#e74c3c'; ?>; font-weight: 600;">
								<?php echo ucfirst($statusTecLower); ?>
							</span>
						</td>
						<td>
							<a href="<?php echo $linkEditTec; ?>">[Editar Especialidade]</a>
							<a href="usuario_excluir.php?id_tecnico=<?php echo $tec['id_tecnico']; ?>" onclick="return confirm('Excluir este técnico?');" style="color: #e74c3c;">[Excluir]</a>
						</td>
					</tr>
				<?php } 
				if (!$temTecnicos) { ?>
					<tr>
						<td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">Nenhum técnico cadastrado na equipe.</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
<?php
	include "footer.php";
?>
