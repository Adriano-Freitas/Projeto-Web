<?php
// 1. Dados de acesso fornecidos pelo Neon.tech
$host     = "ep-aged-term-ac4575sp-pooler.sa-east-1.aws.neon.tech"; 
$port     = "5432";
$dbname   = "assistencia";
$user     = "neondb_owner";
$password = "npg_gSTtM8h2uFLE";

// 2. Monta a string de conexão CORRETA exigindo o banco e o SSL
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    // 3. Tenta conectar usando o PDO do PHP
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h1>Conexão com o Neon.tech realizada com sucesso!</h1>";

    // 4. Teste rápido: Listando as tabelas do seu banco para provar que conectou
    echo "<h3>Suas tabelas no banco:</h3>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
    
    echo "<ul>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<li>" . htmlspecialchars($row['table_name']) . "</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    // Se der qualquer erro (senha errada, host incorreto), exibe aqui
    echo "<h1>Erro ao conectar:</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
