<?php
require_once __DIR__ . '/../PHP/includes/header.php';
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT id_servico AS id, nome AS titulo, descricao, valor_base AS preco_base FROM servicos WHERE id_servico=? AND LOWER(status)='ativo'");
$stmt->execute([$id]); $s=$stmt->fetch();
if(!$s){ http_response_code(404); echo '<section><h1>Serviço não encontrado</h1></section>'; require_once __DIR__.'/../PHP/includes/footer.php'; exit; }
?>
<section><h1><?=e($s['titulo'])?></h1>
<p><?=e($s['descricao'])?></p>
<div class="card"><h2>Detalhes</h2><p><?=nl2br(e($s['conteudo']))?></p><p><strong>Valor base:</strong> <?=valorBR($s['preco_base'])?></p>
<?php if(clientesAtual()): ?><a class="btn" href="solicitar.php?servico=<?=$s['id']?>">Solicitar este serviço</a><?php else: ?><a class="btn" href="conta.php">Entre para solicitar</a><?php endif; ?></div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
