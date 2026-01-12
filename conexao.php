<?php
// Configuração da base de dados
$host = 'localhost';
$dbname = 'u506280443_sanjoaDB';
$username = 'u506280443_sanjoadbUser'; 
$password = 'kTcP:b;0M'; 

try {
    // Criar a conexão usando PDO (para ser compatível com o seu login.php)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro na conexão: " . $e->getMessage());
    die("Erro ao ligar à base de dados.");
}
?>