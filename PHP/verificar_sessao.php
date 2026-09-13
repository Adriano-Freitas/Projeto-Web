<?php
	include "conexao.php";
	header("Content-Type: application/json");

	if (isset($_SESSION["clientes"])) {
		echo json_encode(array("logado" => true, "clientes" => $_SESSION["clientes"]));
	} else {
		echo json_encode(array("logado" => false));
	}
?>
