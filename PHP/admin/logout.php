<?php
	include __DIR__ . "/../conexao.php";

	if (isset($_SESSION["clientes"])) {
		registrarAuditoria($conexao, "LOGOUT_ADMIN", "clientes", $_SESSION["clientes"]["id"], "Logout do Módulo Administrativo.");
	}

	unset($_SESSION["clientes"]);
	session_destroy();

	header("Location: index.php");
	exit;
?>
