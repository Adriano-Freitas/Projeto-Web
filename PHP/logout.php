<?php
include "conexao.php";
header("Content-Type: application/json");

if (isset($_SESSION["usuario"])) {
    registrarAuditoria($conexao, "LOGOUT", "usuarios", $_SESSION["usuario"]["id"], "Usuário encerrou a sessão.");
}

unset($_SESSION["usuario"]);
session_destroy();

echo json_encode(array("sucesso" => true));
?>