<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$resultado = $conexao->query(
		"SELECT s.id, s.nome, s.valor_base, s.status, c.nome AS categoria
		 FROM servicos s INNER JOIN categorias c ON c.id = s.id_categoria
		 ORDER BY s.nome"
	);
?>
	<h2>Gestão do Catálogo de Serviços (Artigos/Produtos)</h2>

	<div class="justificar">
		<p><a href="servico_form.php" class="botao">+ Novo Serviço</a></p>

		<table class="tabela-admin">
			<tr><th>Nome</th><th>Categoria</th><th>Valor Base</th><th>Status</th><th>Ações</th></tr>
			<?php while ($linha = $resultado->fetch_assoc()) { ?>
				<tr>
					<td><?php echo htmlspecialchars($linha["nome"]); ?></td>
					<td><?php echo htmlspecialchars($linha["categoria"]); ?></td>
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
