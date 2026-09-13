<?php
include "conexao.php";
header("Content-Type: application/json");

if (!isset($_SESSION["clientes"])) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Sessão expirada."));
    exit;
}

$id_cliente = $_SESSION["clientes"]["id"];
$id_ordem = isset($_POST["id_servico"]) ? (int) $_POST["id_servico"] : 0;
$forma_pagamento = isset($_POST["forma_pagamento"]) ? $_POST["forma_pagamento"] : "";

$formas_validas = array("pix", "cartao_credito", "cartao_debito", "dinheiro");

if (!in_array($forma_pagamento, $formas_validas)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Forma de pagamento inválida."));
    exit;
}

$stmt = $conexao->prepare("SELECT valor_total, pago FROM ordens_servico WHERE id = ? AND id_cliente = ?");
$stmt->execute([$id_ordem, $id_cliente]);
$ordem = $stmt->fetch();

if (!$ordem) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Serviço não encontrado."));
    exit;
}

if ((bool) $ordem["pago"]) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Este serviço já foi pago."));
    exit;
}

$stmt = $conexao->prepare("UPDATE ordens_servico SET pago = TRUE, forma_pagamento = ? WHERE id = ? AND id_cliente = ?");
$stmt->execute([$forma_pagamento, $id_ordem, $id_cliente]);

$stmt = $conexao->prepare("INSERT INTO pagamentos (id_ordem, valor, metodo_pagamento) VALUES (?, ?, ?)");
$stmt->execute([$id_ordem, $ordem["valor_total"], $forma_pagamento]);

registrarAuditoria($conexao, "PAGAMENTO", "ordens_servico", $id_ordem, "Pagamento da ordem de serviço registrado via " . $forma_pagamento . ".");

echo json_encode(array("sucesso" => true));
?>