<?php
	include "conexao.php";
	header("Content-Type: application/json");

	$sessao = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
	if ($sessao) {
		echo json_encode(array("logado" => true, "clientes" => $sessao, "usuario" => $sessao));
	} else {
		echo json_encode(array("logado" => false));
	}
?>
