<?php
	include "header.php";

	if ($clientesLogado["tipo"] !== "gerente") {
		echo "<p class=\"erro\">Acesso restrito ao Gerente.</p>";
		include "footer.php";
		exit;
	}

	$id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
	$id_tecnico = isset($_GET["id_tecnico"]) ? (int) $_GET["id_tecnico"] : 0;
	$tipoPadrao = isset($_GET["tipo"]) ? trim(strtolower($_GET["tipo"])) : "cliente";
	if (!in_array($tipoPadrao, array("cliente", "tecnico", "gerente"))) {
		$tipoPadrao = "cliente";
	}
	$origem = isset($_GET["origem"]) && $_GET["origem"] === "tecnicos" ? "tecnicos" : "usuarios";

	$clientes = array("id" => 0, "nome" => "", "email" => "", "telefone" => "", "cpf" => "", "tipo" => $tipoPadrao, "especialidade" => "", "status" => "Ativo");

	if ($id > 0) {
		$stmt = $conexao->prepare("SELECT id_cliente AS id, nome, email, telefone, cpf, endereco, tipo, especialidade, COALESCE(status, 'Ativo') AS status FROM clientes WHERE id_cliente = ?");
		$stmt->execute([$id]);
		$dado = $stmt->fetch();
		if ($dado) {
			$clientes = array_merge($clientes, $dado);
			if (empty($clientes["especialidade"]) && strtolower($clientes["tipo"] ?? "") === "tecnico") {
				$stmtTec = $conexao->prepare("SELECT especialidade FROM tecnicos WHERE email = ?");
				$stmtTec->execute([$clientes["email"]]);
				$clientes["especialidade"] = $stmtTec->fetchColumn() ?: "";
			}
		}
	} elseif ($id_tecnico > 0) {
		$origem = "tecnicos";
		$stmtTec = $conexao->prepare("SELECT id_tecnico, nome, email, telefone, especialidade, status FROM tecnicos WHERE id_tecnico = ?");
		$stmtTec->execute([$id_tecnico]);
		$dadoTec = $stmtTec->fetch();
		if ($dadoTec) {
			$stmtC = $conexao->prepare("SELECT id_cliente AS id, cpf, endereco FROM clientes WHERE LOWER(email) = ?");
			$stmtC->execute([strtolower($dadoTec["email"])]);
			$dadoC = $stmtC->fetch();
			$clientes["id"] = $dadoC ? (int) $dadoC["id"] : 0;
			$clientes["nome"] = $dadoTec["nome"];
			$clientes["email"] = $dadoTec["email"];
			$clientes["telefone"] = $dadoTec["telefone"];
			$clientes["cpf"] = $dadoC["cpf"] ?? "";
			$clientes["tipo"] = "tecnico";
			$clientes["especialidade"] = $dadoTec["especialidade"];
			$clientes["status"] = ucfirst(strtolower($dadoTec["status"] ?? "Ativo"));
		}
	}
	$isEdicao = ($clientes["id"] > 0 || $id_tecnico > 0);
	$tituloForm = $isEdicao ? ($clientes["tipo"] === "tecnico" ? "Editar Técnico" : "Editar Usuário") : ($clientes["tipo"] === "tecnico" ? "Novo Técnico" : "Novo Usuário");
	$linkCancelar = $origem === "tecnicos" ? "tecnicos.php" : "usuarios.php";
?>
	<h2><?php echo htmlspecialchars($tituloForm); ?></h2>

	<div class="justificar cartao" style="max-width: 500px;">
		<form method="post" action="usuario_salvar.php">
			<input type="hidden" name="id" value="<?php echo (int) $clientes["id"]; ?>">
			<input type="hidden" name="origem" value="<?php echo htmlspecialchars($origem); ?>">

			<label><b>Nome:</b></label><br>
			<input type="text" name="nome" required value="<?php echo htmlspecialchars($clientes["nome"]); ?>"><br>

			<label><b>E-mail:</b></label><br>
			<input type="email" name="email" required value="<?php echo htmlspecialchars($clientes["email"]); ?>"><br>

			<label><b>Telefone:</b></label><br>
			<input type="text" name="telefone" id="telefone" maxlength="15" required value="<?php echo htmlspecialchars($clientes["telefone"]); ?>" placeholder="(00) 00000-0000"><br>

			<label><b>CPF:</b></label><br>
			<input type="text" name="cpf" id="cpf" maxlength="14" required value="<?php echo htmlspecialchars($clientes["cpf"]); ?>" placeholder="000.000.000-00"><br>

			<label><b>Tipo:</b></label><br>
			<select name="tipo" id="tipo-usuario" required>
				<option value="cliente" <?php echo $clientes["tipo"] === "cliente" ? "selected" : ""; ?>>Cliente</option>
				<option value="tecnico" <?php echo $clientes["tipo"] === "tecnico" ? "selected" : ""; ?>>Técnico</option>
				<option value="gerente" <?php echo $clientes["tipo"] === "gerente" ? "selected" : ""; ?>>Gerente</option>
			</select><br>

			<div id="grupo-especialidade" style="<?php echo $clientes["tipo"] === "tecnico" ? "" : "display:none;"; ?>">
				<label><b>Especialidade:</b></label><br>
				<input type="text" name="especialidade" id="especialidade" value="<?php echo htmlspecialchars($clientes["especialidade"] ?? ""); ?>" placeholder="Ex: Notebooks, Computadores, Celulares"><br>
			</div>

			<label><b>Status:</b></label><br>
			<select name="status">
				<option value="Ativo" <?php echo strtolower($clientes["status"]) === "ativo" ? "selected" : ""; ?>>Ativo</option>
				<option value="Inativo" <?php echo strtolower($clientes["status"]) === "inativo" ? "selected" : ""; ?>>Inativo</option>
			</select><br>

			<label><b><?php echo $isEdicao ? "Nova Senha (deixe em branco para manter)" : "Senha"; ?>:</b></label><br>
			<input type="password" name="senha" <?php echo $isEdicao ? "" : "required"; ?>><br>

			<button type="submit" class="botao">Salvar</button>
			<a href="<?php echo htmlspecialchars($linkCancelar); ?>" class="botao secundario">Cancelar</a>
		</form>
	</div>

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			var telInput = document.getElementById("telefone");
			var cpfInput = document.getElementById("cpf");
			var tipoSelect = document.getElementById("tipo-usuario");
			var grupoEsp = document.getElementById("grupo-especialidade");

			if (tipoSelect && grupoEsp) {
				tipoSelect.addEventListener("change", function() {
					grupoEsp.style.display = (this.value === "tecnico") ? "block" : "none";
				});
			}

			function aplicarMascaraTelefone(v) {
				v = v.replace(/\D/g, "");
				if (v.length > 11) v = v.substring(0, 11);
				if (v.length > 10) {
					return "(" + v.substring(0, 2) + ") " + v.substring(2, 7) + "-" + v.substring(7, 11);
				} else if (v.length > 6) {
					return "(" + v.substring(0, 2) + ") " + v.substring(2, 6) + "-" + v.substring(6, 10);
				} else if (v.length > 2) {
					return "(" + v.substring(0, 2) + ") " + v.substring(2);
				} else if (v.length > 0) {
					return "(" + v;
				}
				return "";
			}

			function aplicarMascaraCPF(v) {
				v = v.replace(/\D/g, "");
				if (v.length > 11) v = v.substring(0, 11);
				if (v.length > 9) {
					return v.substring(0, 3) + "." + v.substring(3, 6) + "." + v.substring(6, 9) + "-" + v.substring(9, 11);
				} else if (v.length > 6) {
					return v.substring(0, 3) + "." + v.substring(3, 6) + "." + v.substring(6);
				} else if (v.length > 3) {
					return v.substring(0, 3) + "." + v.substring(3);
				}
				return v;
			}

			if (telInput) {
				telInput.value = aplicarMascaraTelefone(telInput.value);
				telInput.addEventListener("input", function() {
					this.value = aplicarMascaraTelefone(this.value);
				});
			}

			if (cpfInput) {
				cpfInput.value = aplicarMascaraCPF(cpfInput.value);
				cpfInput.addEventListener("input", function() {
					this.value = aplicarMascaraCPF(this.value);
				});
			}
		});
	</script>
<?php
	include "footer.php";
?>
