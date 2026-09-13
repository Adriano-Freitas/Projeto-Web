<?php
	include "conexao.php";
	header("Content-Type: application/json");

	if (isset($_SESSION["usuario"])) {
		echo json_encode(array("logado" => true, "usuario" => $_SESSION["usuario"]));
	} else {
		echo json_encode(array("logado" => false));
	}
?>
