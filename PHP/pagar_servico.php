<?php
include "conexao.php";
header("Content-Type: application/json");

$sessaoAtual = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
if (!$sessaoAtual) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Sessão expirada."));
    exit;
}

$id_cliente = $sessaoAtual["id"];
$id_ordem = isset($_POST["id_servico"]) ? (int) $_POST["id_servico"] : 0;
$forma_pagamento = isset($_POST["forma_pagamento"]) ? $_POST["forma_pagamento"] : "";

$formas_validas = array("pix", "cartao_credito", "cartao_debito", "dinheiro");

if (!in_array($forma_pagamento, $formas_validas)) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Forma de pagamento inválida."));
    exit;
}

$stmt = $conexao->prepare(
    "SELECT o.id_ordem, o.valor_total, p.id_pagamento 
     FROM ordens_servico o 
     LEFT JOIN pagamentos p ON p.id_ordem = o.id_ordem AND LOWER(p.status) = 'pago'
     WHERE o.id_ordem = ? AND o.id_cliente = ?"
);
$stmt->execute([$id_ordem, $id_cliente]);
$ordem = $stmt->fetch();

if (!$ordem) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Serviço não encontrado."));
    exit;
}

if (!empty($ordem["id_pagamento"])) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Este serviço já foi pago."));
    exit;
}

$stmt = $conexao->prepare("INSERT INTO pagamentos (id_ordem, valor, metodo_pagamento, status, data_pagamento) VALUES (?, ?, ?, 'pago', NOW())");
$stmt->execute([$id_ordem, $ordem["valor_total"], $forma_pagamento]);

$stmtUpdate = $conexao->prepare("UPDATE ordens_servico SET status = 'Concluído' WHERE id_ordem = ? AND id_cliente = ?");
$stmtUpdate->execute([$id_ordem, $id_cliente]);

registrarAuditoria($conexao, "PAGAMENTO", "ordens_servico", $id_ordem, "Pagamento da ordem de serviço registrado via " . $forma_pagamento . ".");

echo json_encode(array("sucesso" => true));
?>