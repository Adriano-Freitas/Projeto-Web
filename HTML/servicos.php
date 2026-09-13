<?php
require_once __DIR__ . '/../PHP/includes/header.php';
$stmt=$pdo->query("SELECT a.*, c.nome categoria FROM artigos a JOIN categorias c ON c.id=a.categoria_id WHERE a.ativo=true ORDER BY a.titulo");
$servicos=$stmt->fetchAll();
?>
<section><h1>Serviços</h1><p>Consulte a lista geral e abra a página individual de cada serviço.</p>
<div class="search-row"><label for="ordenar-servicos">Ordenar:</label><select id="ordenar-servicos"><option value="nome">Nome</option><option value="preco">Preço base</option></select></div>
<div id="lista-servicos" class="grid">
<?php foreach($servicos as $s): ?>
<article class="card" data-nome="<?=e($s['titulo'])?>" data-preco="<?=$s['preco_base']?>">
 <span class="badge"><?=e($s['categoria'])?></span><h2><?=e($s['titulo'])?></h2><p><?=e($s['descricao'])?></p>
 <a class="btn" href="servico.php?id=<?=$s['id']?>">Ver detalhes</a>
</article>
<?php endforeach; ?>
</div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
