<?php
	include "header.php";

	if ($usuarioLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
	$categoria = array("id" => 0, "nome" => "");

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id, nome FROM categorias WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$resultado = $stmt->get_result();

		if ($resultado->num_rows > 0) {
			$categoria = $resultado->fetch_assoc();
		}
	}
?>
	<h2><?php echo $id > 0 ? "Editar Categoria" : "Nova Categoria"; ?></h2>

	<div class="justificar cartao" style="max-width: 400px;">
		<form method="post" action="categoria_salvar.php">
			<input type="hidden" name="id" value="<?php echo (int) $categoria["id"]; ?>">

			<label><b>Nome:</b></label><br>
			<input type="text" name="nome" required value="<?php echo htmlspecialchars($categoria["nome"]); ?>"><br>

			<button type="submit" class="botao">Salvar</button>
			<a href="categorias.php" class="botao secundario">Cancelar</a>
		</form>
	</div>
<?php
	include "footer.php";
?>
