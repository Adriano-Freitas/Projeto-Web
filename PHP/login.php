<?php
include "conexao.php";
header("Content-Type: application/json");

$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

$stmt = $conexao->prepare("SELECT id_cliente AS id, nome, email, telefone, cpf, senha FROM clientes WHERE email = ?");
$stmt->execute([$email]);

$linha = $stmt->fetch();

$senhaValida = false;
if ($linha) {
    if (password_verify($senha, $linha["senha"]) || $senha === $linha["senha"]) {
        $senhaValida = true;
    }
}

if (!$linha || !$senhaValida) {
    echo json_encode(array("sucesso" => false, "mensagem" => "E-mail ou senha inválidos."));
    exit;
}

$clientes = array(
    "id" => (int) $linha["id"],
    "nome" => $linha["nome"],
    "email" => $linha["email"],
    "telefone" => $linha["telefone"] ?? "",
    "cpf" => $linha["cpf"] ?? "",
    "tipo" => "cliente"
);

$_SESSION["clientes"] = $clientes;
$_SESSION["usuario"] = $clientes;

registrarAuditoria($conexao, "LOGIN", "clientes", $clientes["id"], "Cliente autenticado no sistema.");

echo json_encode(array("sucesso" => true, "clientes" => $clientes, "usuario" => $clientes));
?>