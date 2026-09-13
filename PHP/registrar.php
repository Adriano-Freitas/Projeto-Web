<?php
include "conexao.php";
header("Content-Type: application/json");

$nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$telefone = isset($_POST["telefone"]) ? trim($_POST["telefone"]) : "";
$cpf = isset($_POST["cpf"]) ? trim($_POST["cpf"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

if (empty($nome) || empty($email) || empty($telefone) || empty($cpf) || empty($senha)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Preencha todos os campos."));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "E-mail inválido."));
    exit;
}

if (strlen($senha) < 6) {
    echo json_encode(array("sucesso" => false, "mensagem" => "A senha deve ter pelo menos 6 caracteres."));
    exit;
}

$stmt = $conexao->prepare("SELECT id_cliente AS id FROM clientes WHERE email = ? OR cpf = ?");
$stmt->execute([$email, $cpf]);

if ($stmt->fetch()) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Já existe uma conta com este e-mail ou CPF."));
    exit;
}

$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$tipoPermitidos = array("cliente", "tecnico", "gerente");
$tipo = isset($_POST["tipo"]) && in_array($_POST["tipo"], $tipoPermitidos) ? $_POST["tipo"] : "cliente";

$stmt = $conexao->prepare("INSERT INTO clientes (nome, email, telefone, cpf, senha, tipo) VALUES (?, ?, ?, ?, ?, ?) RETURNING id_cliente");
$stmt->execute([$nome, $email, $telefone, $cpf, $senha_hash, $tipo]);

$id_clientes = $stmt->fetchColumn();

if ($tipo === "tecnico") {
    try {
        $stmtTec = $conexao->prepare("SELECT id_tecnico FROM tecnicos WHERE email = ?");
        $stmtTec->execute([$email]);
        if (!$stmtTec->fetch()) {
            $stmtInsTec = $conexao->prepare("INSERT INTO tecnicos (nome, email, telefone, especialidade, status) VALUES (?, ?, ?, 'Geral', 'ativo')");
            $stmtInsTec->execute([$nome, $email, $telefone]);
        }
    } catch (PDOException $e) {
        error_log("Aviso ao sincronizar tecnico: " . $e->getMessage());
    }
}

$clientes = array(
    "id" => (int) $id_clientes,
    "nome" => $nome,
    "email" => $email,
    "telefone" => $telefone,
    "cpf" => $cpf,
    "tipo" => $tipo
);

$_SESSION["clientes"] = $clientes;
$_SESSION["usuario"] = $clientes;

registrarAuditoria($conexao, "CADASTRO", "clientes", $clientes["id"], "Novo cliente cadastrado (auto cadastro).");

echo json_encode(array("sucesso" => true, "clientes" => $clientes, "usuario" => $clientes));
?>