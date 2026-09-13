<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$categorias = array();
	try {
		$resultado = $conexao->query("SELECT id, nome FROM categorias ORDER BY nome");
		if ($resultado) {
			$categorias = $resultado->fetchAll();
		}
	} catch (PDOException $e) {
		$categorias = array(
			array("id" => 1, "nome" => "Geral")
		);
	}
?>
	<h2>Gestão de Categorias/Tipos de Serviço</h2>

	<div class="justificar">
		<p><a href="categoria_form.php" class="botao">+ Nova Categoria</a></p>

		<table class="tabela-admin">
			<tr><th>Nome</th><th>Ações</th></tr>
			<?php foreach ($categorias as $linha) { ?>
				<tr>
					<td><?php echo htmlspecialchars($linha["nome"]); ?></td>
					<td>
						<a href="categoria_form.php?id=<?php echo $linha['id']; ?>">[Editar]</a>
						<a href="categoria_excluir.php?id=<?php echo $linha['id']; ?>" onclick="return confirm('Excluir esta categoria?');">[Excluir]</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
<?php
	include "footer.php";
?>
