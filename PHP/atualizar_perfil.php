<?php
include "conexao.php";
header("Content-Type: application/json");

if (!isset($_SESSION["clientes"]) && !isset($_SESSION["usuario"])) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Sessão expirada."));
    exit;
}

$sessaoAtual = $_SESSION["clientes"] ?? $_SESSION["usuario"];
$id = $sessaoAtual["id"];
$nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$telefone = isset($_POST["telefone"]) ? trim($_POST["telefone"]) : "";
$cpf = isset($_POST["cpf"]) ? trim($_POST["cpf"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

if (empty($nome) || empty($email) || empty($telefone) || empty($cpf)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Preencha todos os campos."));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "E-mail inválido."));
    exit;
}

if (!empty($senha)) {
    if (strlen($senha) < 6) {
        echo json_encode(array("sucesso" => false, "mensagem" => "A nova senha deve ter pelo menos 6 caracteres."));
        exit;
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, senha = ? WHERE id_cliente = ?");
    $stmt->execute([$nome, $email, $telefone, $cpf, $senha_hash, $id]);
} else {
    $stmt = $conexao->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ? WHERE id_cliente = ?");
    $stmt->execute([$nome, $email, $telefone, $cpf, $id]);
}

$clientes = array(
    "id" => $id,
    "nome" => $nome,
    "email" => $email,
    "telefone" => $telefone,
    "cpf" => $cpf,
    "tipo" => "cliente"
);

$_SESSION["clientes"] = $clientes;
$_SESSION["usuario"] = $clientes;

registrarAuditoria($conexao, "ATUALIZACAO", "clientes", $id, "Cliente atualizou os próprios dados de cadastro.");

echo json_encode(array("sucesso" => true, "clientes" => $clientes, "usuario" => $clientes));
?>