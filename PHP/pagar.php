<?php
require_once __DIR__.'/includes/init.php'; exigirLogin(); validarCsrf();
$id=(int)($_POST['ordem_id']??0);$metodo=$_POST['metodo']??'';
if(!in_array($metodo,['pix','credito','debito','dinheiro'],true))exit('Método inválido.');
$q=$pdo->prepare("SELECT * FROM ordens_servico WHERE id=? AND cliente_id=? AND status='Aguardando pagamento'");$q->execute([$id,clientesAtual()['id']]);$o=$q->fetch();if(!$o)exit('Fatura não disponível.');
$pdo->beginTransaction();
try{$pdo->prepare("INSERT INTO pagamentos(ordem_id,valor,metodo) VALUES(?,?,?)")->execute([$id,$o['valor_final'],$metodo]);$pdo->prepare("UPDATE ordens_servico SET status='Entregue',pagamento_liberado=true WHERE id=?")->execute([$id]);$pdo->prepare("INSERT INTO notificacoes(clientes_id,ordem_id,mensagem) VALUES(?,?,?)")->execute([clientesAtual()['id'],$id,'Pagamento confirmado. O equipamento está liberado para retirada presencial.']);registrarAuditoria($pdo,clientesAtual()['id'],'INCLUIR','pagamentos',$id,'Pagamento de ordem');$pdo->commit();header('Location: ../HTML/conta.php?pagamento=1');}catch(Throwable $e){$pdo->rollBack();exit('Pagamento não registrado.');}
