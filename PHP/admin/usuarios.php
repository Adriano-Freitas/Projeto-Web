<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$resultado = $conexao->query("SELECT id_cliente AS id, nome, email, telefone, cpf, endereco, tipo, especialidade FROM clientes ORDER BY nome");
?>
	<h2>Gestão de Usuários</h2>

	<div class="justificar">
		<p><a href="usuario_form.php" class="botao">+ Novo Usuário</a></p>

		<table class="tabela-admin">
			<tr>
				<th>Nome</th><th>E-mail</th><th>Telefone</th><th>CPF</th><th>Tipo</th><th>Status</th><th>Ações</th>
			</tr>
			<?php while ($linha = $resultado->fetch()) { 
				$tipoOriginal = strtolower($linha["tipo"] ?? "cliente");
				$esp = (!empty($linha["especialidade"]) && $tipoOriginal === "tecnico") ? " (" . $linha["especialidade"] . ")" : "";
				$tipoTexto = $tipoOriginal === "gerente" ? "Gerente" : ($tipoOriginal === "tecnico" ? ("Técnico" . $esp) : "Cliente");
			?>
				<tr>
					<td><?php echo htmlspecialchars($linha["nome"]); ?></td>
					<td><?php echo htmlspecialchars($linha["email"]); ?></td>
					<td><?php echo htmlspecialchars($linha["telefone"]); ?></td>
					<td><?php echo htmlspecialchars($linha["cpf"]); ?></td>
					<td><b><?php echo htmlspecialchars($tipoTexto); ?></b></td>
					<td>Ativo</td>
					<td>
						<a href="usuario_form.php?id=<?php echo $linha['id']; ?>">[Editar]</a>
						<a href="usuario_excluir.php?id=<?php echo $linha['id']; ?>" onclick="return confirm('Excluir este usuário?');">[Excluir]</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php
	include "footer.php";
?>
