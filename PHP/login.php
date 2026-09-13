<?php
include "conexao.php";
header("Content-Type: application/json");

$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

$stmt = $conexao->prepare("SELECT id, nome, email, telefone, cpf, senha, tipo FROM clientes WHERE email = ?");
$stmt->execute([$email]);

$linha = $stmt->fetch();

if (!$linha || !password_verify($senha, $linha["senha"])) {
    echo json_encode(array("sucesso" => false, "mensagem" => "E-mail ou senha inválidos."));
    exit;
}

$clientes = array(
    "id" => (int) $linha["id"],
    "nome" => $linha["nome"],
    "email" => $linha["email"],
    "telefone" => $linha["telefone"],
    "cpf" => $linha["cpf"],
    "tipo" => $linha["tipo"]
);

$_SESSION["clientes"] = $clientes;

registrarAuditoria($conexao, "LOGIN", "clientes", $clientes["id"], "Usuário autenticado no sistema.");

echo json_encode(array("sucesso" => true, "clientes" => $clientes));
?>