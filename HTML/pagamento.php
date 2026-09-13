<?php require_once __DIR__ . '/../PHP/includes/header.php'; exigirLogin(); $id=(int)($_GET['ordem']??0);
$q=$pdo->prepare("SELECT o.*,u.nome FROM ordens_servico o JOIN usuarios u ON u.id=o.cliente_id WHERE o.id=? AND o.cliente_id=?");$q->execute([$id,usuarioAtual()['id']]);$o=$q->fetch();
if(!$o || $o['status']!=='Aguardando pagamento'){echo '<section><h1>Pagamento indisponível.</h1></section>';require_once __DIR__.'/../PHP/includes/footer.php';exit;}
?>
<section><div class="form-card"><h1>Pagamento da fatura</h1><p>Ordem <?=e($o['codigo'])?></p><h2><?=valorBR($o['valor_final'])?></h2>
<form action="../PHP/pagar.php" method="post" data-validar><input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="ordem_id" value="<?=$o['id']?>">
<label>Forma de pagamento</label><select name="metodo" required><option value="">Selecione</option><option value="pix">PIX</option><option value="credito">Cartão de crédito à vista</option><option value="debito">Cartão de débito</option><option value="dinheiro">Dinheiro</option></select><button>Confirmar pagamento</button></form></div></section>
<?php require_once __DIR__.'/../PHP/includes/footer.php'; ?>
