<?php
	include __DIR__ . "/../conexao.php";

	if (isset($_SESSION["usuario"])) {
		registrarAuditoria($conexao, "LOGOUT_ADMIN", "usuarios", $_SESSION["usuario"]["id"], "Logout do Módulo Administrativo.");
	}

	unset($_SESSION["usuario"]);
	session_destroy();

	header("Location: index.php");
	exit;
?>
