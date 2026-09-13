<?php
require_once __DIR__ . '/config.php';

$sslMode = (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') ? '' : ';sslmode=require';
$dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . $sslMode;

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h1>Conexão com o banco realizada com sucesso!</h1>";
    echo "<h3>Tabelas no banco:</h3>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
    
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . htmlspecialchars($row['table_name']) . "</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h1>Erro ao conectar:</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
