<?php
include "conexao.php";
header("Content-Type: application/json");

if (isset($_SESSION["clientes"])) {
    registrarAuditoria($conexao, "LOGOUT", "clientes", $_SESSION["clientes"]["id"], "Usuário encerrou a sessão.");
}

unset($_SESSION["clientes"]);
unset($_SESSION["usuario"]);
session_destroy();

echo json_encode(array("sucesso" => true));
?>