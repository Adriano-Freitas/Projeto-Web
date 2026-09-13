<?php
	include __DIR__ . "/../conexao.php";

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if ($sessao && in_array($sessao["tipo"], array("gerente", "tecnico"))) {
		header("Location: ordens.php");
		exit;
	}

	header("Location: ../../HTML/conta.html");
	exit;
