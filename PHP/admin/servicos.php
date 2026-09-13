<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$resultado = $conexao->query(
		"SELECT s.id_servico AS id, s.nome, s.valor_base, s.status
		 FROM servicos s
		 ORDER BY s.id_servico DESC"
	);
?>
	<h2>Gestão do Catálogo de Serviços</h2>

	<div class="justificar">
		<p><a href="servico_form.php" class="botao">+ Novo Serviço</a></p>

		<table class="tabela-admin">
			<tr><th>#</th><th>Nome</th><th>Valor Base</th><th>Status</th><th>Ações</th></tr>
			<?php while ($linha = $resultado->fetch()) { ?>
				<tr>
					<td>#<?php echo $linha["id"]; ?></td>
					<td><?php echo htmlspecialchars($linha["nome"]); ?></td>
					<td>R$ <?php echo number_format($linha["valor_base"], 2, ",", "."); ?></td>
					<td><?php echo htmlspecialchars($linha["status"]); ?></td>
					<td>
						<a href="servico_form.php?id=<?php echo $linha['id']; ?>">[Editar]</a>
						<a href="servico_excluir.php?id=<?php echo $linha['id']; ?>" onclick="return confirm('Excluir este serviço?');">[Excluir]</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php
	include "footer.php";
?>
