<?php
include "conexao.php";

if (isset($_SESSION["clientes"])) {
    registrarAuditoria($conexao, "LOGOUT", "clientes", $_SESSION["clientes"]["id"], "Usuário encerrou a sessão.");
}

unset($_SESSION["clientes"]);
unset($_SESSION["usuario"]);
session_destroy();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    echo json_encode(array("sucesso" => true));
    exit;
}

header("Location: ../HTML/conta.html");
exit;
?>