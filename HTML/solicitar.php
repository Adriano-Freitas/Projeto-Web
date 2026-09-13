<?php
require_once __DIR__ . '/../PHP/includes/header.php'; exigirLogin();
$id=(int)($_GET['servico']??0); $s=$pdo->prepare("SELECT * FROM artigos WHERE id=? AND ativo=true");$s->execute([$id]);$serv=$s->fetch();
if(!$serv){echo '<section><h1>Serviço não encontrado.</h1></section>';require_once __DIR__.'/../PHP/includes/footer.php';exit;}
?>
<section><div class="form-card"><h1>Solicitar: <?=e($serv['titulo'])?></h1>
<form action="../PHP/solicitar_servico.php" method="post" enctype="multipart/form-data" data-validar>
<input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="servico_id" value="<?=$serv['id']?>">
<label>Tipo de aparelho</label><select name="tipo" required><option value="">Selecione</option><option>Computador</option><option>Notebook</option><option>Celular</option><option>Outro</option></select>
<label>Marca</label><input name="marca"><label>Modelo</label><input name="modelo"><label>Número de série</label><input name="numero_serie">
<label>Descrição do problema</label><textarea name="problema" required></textarea>
<label>Imagens do equipamento (opcional)</label><input type="file" name="imagens[]" multiple accept="image/jpeg,image/png">
<p class="notice">A retirada e a entrega são presenciais. O pagamento ocorre após a geração da fatura final.</p>
<button>Enviar solicitação</button></form></div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
