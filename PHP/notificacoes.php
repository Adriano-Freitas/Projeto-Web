<?php
require_once __DIR__ . '/includes/init.php'; exigirLogin();
$q=$pdo->prepare("SELECT id,mensagem,to_char(criada_em,'DD/MM/YYYY HH24:MI') AS criada_em FROM notificacoes WHERE usuario_id=? ORDER BY criada_em DESC LIMIT 10");
$q->execute([usuarioAtual()['id']]); header('Content-Type: application/json; charset=utf-8'); echo json_encode($q->fetchAll(),JSON_UNESCAPED_UNICODE);
