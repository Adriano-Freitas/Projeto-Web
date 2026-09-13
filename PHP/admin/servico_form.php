<?php
	include "header.php";

	if ($usuarioLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
	$servico = array("id" => 0, "id_categoria" => "", "nome" => "", "descricao" => "", "valor_base" => "0.00", "status" => "Ativo");

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id, id_categoria, nome, descricao, valor_base, status FROM servicos WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$resultado = $stmt->get_result();

		if ($resultado->num_rows > 0) {
			$servico = $resultado->fetch_assoc();
		}
	}

	$categorias = $conexao->query("SELECT id, nome FROM categorias ORDER BY nome");
?>
	<h2><?php echo $id > 0 ? "Editar Serviço" : "Novo Serviço"; ?></h2>

	<div class="justificar cartao" style="max-width: 500px;">
		<form method="post" action="servico_salvar.php">
			<input type="hidden" name="id" value="<?php echo (int) $servico["id"]; ?>">

			<label><b>Nome:</b></label><br>
			<input type="text" name="nome" required value="<?php echo htmlspecialchars($servico["nome"]); ?>"><br>

			<label><b>Categoria:</b></label><br>
			<select name="id_categoria" required>
				<?php while ($cat = $categorias->fetch_assoc()) { ?>
					<option value="<?php echo $cat['id']; ?>" <?php echo ((string) $servico["id_categoria"] === (string) $cat["id"]) ? "selected" : ""; ?>>
						<?php echo htmlspecialchars($cat["nome"]); ?>
					</option>
				<?php } ?>
			</select><br>

			<label><b>Descrição:</b></label><br>
			<textarea name="descricao" rows="4"><?php echo htmlspecialchars($servico["descricao"]); ?></textarea><br>

			<label><b>Valor Base (R$):</b></label><br>
			<input type="number" step="0.01" min="0" name="valor_base" required value="<?php echo htmlspecialchars($servico["valor_base"]); ?>"><br>

			<label><b>Status:</b></label><br>
			<select name="status">
				<option value="Ativo" <?php echo $servico["status"] === "Ativo" ? "selected" : ""; ?>>Ativo</option>
				<option value="Inativo" <?php echo $servico["status"] === "Inativo" ? "selected" : ""; ?>>Inativo</option>
			</select><br>

			<button type="submit" class="botao">Salvar</button>
			<a href="servicos.php" class="botao secundario">Cancelar</a>
		</form>
	</div>
<?php
	include "footer.php";
?>
