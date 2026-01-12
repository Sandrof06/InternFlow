<?php
// conexao.php - Ficheiro de conexão à base de dados

$host = 'localhost';
$dbname = 'u506280443_sanjoaDB';
$username = ' u506280443_sanjoadbUser'; // Utilizador padrão do XAMPP
$password = 'kTcP:b;0M';     // Senha padrão do XAMPP (vazia)

try {
    // Cria a conexão PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Define o modo de erro para exceções, para facilitar a depuração
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Opcional: Configura o fetch padrão para array associativo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Em caso de erro, mostra uma mensagem amigável (ou o erro técnico se preferires)
    die("Erro na conexão com a base de dados: " . $e->getMessage());
}
?>
