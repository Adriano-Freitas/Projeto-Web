<?php
	include "header.php";

	if ($usuarioLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
	$usuario = array("id" => 0, "nome" => "", "email" => "", "telefone" => "", "cpf" => "", "tipo" => "cliente", "especialidade" => "", "status" => "Ativo");

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id, nome, email, telefone, cpf, tipo, especialidade, status FROM usuarios WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$resultado = $stmt->get_result();

		if ($resultado->num_rows > 0) {
			$usuario = $resultado->fetch_assoc();
		}
	}
?>
	<h2><?php echo $id > 0 ? "Editar Usuário" : "Novo Usuário"; ?></h2>

	<div class="justificar cartao" style="max-width: 500px;">
		<form method="post" action="usuario_salvar.php">
			<input type="hidden" name="id" value="<?php echo (int) $usuario["id"]; ?>">

			<label><b>Nome:</b></label><br>
			<input type="text" name="nome" required value="<?php echo htmlspecialchars($usuario["nome"]); ?>"><br>

			<label><b>E-mail:</b></label><br>
			<input type="email" name="email" required value="<?php echo htmlspecialchars($usuario["email"]); ?>"><br>

			<label><b>Telefone:</b></label><br>
			<input type="text" name="telefone" required value="<?php echo htmlspecialchars($usuario["telefone"]); ?>"><br>

			<label><b>CPF:</b></label><br>
			<input type="text" name="cpf" required value="<?php echo htmlspecialchars($usuario["cpf"]); ?>"><br>

			<label><b>Tipo:</b></label><br>
			<select name="tipo" required>
				<option value="cliente" <?php echo $usuario["tipo"] === "cliente" ? "selected" : ""; ?>>Cliente</option>
				<option value="tecnico" <?php echo $usuario["tipo"] === "tecnico" ? "selected" : ""; ?>>Técnico</option>
				<option value="gerente" <?php echo $usuario["tipo"] === "gerente" ? "selected" : ""; ?>>Gerente</option>
			</select><br>

			<label><b>Especialidade (para Técnico):</b></label><br>
			<input type="text" name="especialidade" value="<?php echo htmlspecialchars($usuario["especialidade"] ?? ""); ?>"><br>

			<label><b>Status:</b></label><br>
			<select name="status">
				<option value="Ativo" <?php echo $usuario["status"] === "Ativo" ? "selected" : ""; ?>>Ativo</option>
				<option value="Inativo" <?php echo $usuario["status"] === "Inativo" ? "selected" : ""; ?>>Inativo</option>
			</select><br>

			<label><b><?php echo $id > 0 ? "Nova Senha (deixe em branco para manter)" : "Senha"; ?>:</b></label><br>
			<input type="password" name="senha" <?php echo $id > 0 ? "" : "required"; ?>><br>

			<button type="submit" class="botao">Salvar</button>
			<a href="usuarios.php" class="botao secundario">Cancelar</a>
		</form>
	</div>
<?php
	include "footer.php";
?>
