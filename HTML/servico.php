<?php
require_once __DIR__ . '/../PHP/includes/header.php';
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT a.*,c.nome categoria FROM artigos a JOIN categorias c ON c.id=a.categoria_id WHERE a.id=? AND a.ativo=true");
$stmt->execute([$id]); $s=$stmt->fetch();
if(!$s){ http_response_code(404); echo '<section><h1>Serviço não encontrado</h1></section>'; require_once __DIR__.'/../PHP/includes/footer.php'; exit; }
?>
<section><span class="badge"><?=e($s['categoria'])?></span><h1><?=e($s['titulo'])?></h1>
<p><?=e($s['descricao'])?></p>
<div class="card"><h2>Detalhes</h2><p><?=nl2br(e($s['conteudo']))?></p><p><strong>Valor base:</strong> <?=valorBR($s['preco_base'])?></p>
<?php if(usuarioAtual()): ?><a class="btn" href="solicitar.php?servico=<?=$s['id']?>">Solicitar este serviço</a><?php else: ?><a class="btn" href="conta.php">Entre para solicitar</a><?php endif; ?></div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
