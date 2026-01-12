<?php
require 'conexao.php';

// Definir arquivo de log
$log_file = __DIR__ . '/debug_db_test.txt';
function logMsg($msg) {
    global $log_file;
    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] $msg\n", FILE_APPEND);
    echo "$msg\n";
}

logMsg("--- INICIANDO TESTE DE INSERÇÃO MANUAL ---");

try {
    // 1. Verificar se existe um estágio válido
    $estagio = $pdo->query("SELECT id FROM estagios LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$estagio) {
        logMsg("ERRO: Nenhum estágio encontrado na tabela 'estagios'. Crie um estágio primeiro.");
        exit;
    }
    
    logMsg("Estágio encontrado ID: " . $estagio['id']);

    // 2. Tentar Inserir Relatório
    $sql = "INSERT INTO relatorios (estagio_id, titulo, conteudo, arquivo, submetido_em) VALUES (:estagio_id, :titulo, :conteudo, :arquivo, NOW())";
    $stmt = $pdo->prepare($sql);
    
    $params = [
        'estagio_id' => $estagio['id'],
        'titulo' => 'Relatório de Teste Script Direto',
        'conteudo' => 'Conteúdo de teste inserido via script de debug para verificar BD.',
        'arquivo' => 'uploads/relatorios/teste_script.pdf'
    ];
    
    logMsg("Tentando executar INSERT com params: " . print_r($params, true));
    
    if ($stmt->execute($params)) {
        logMsg("SUCESSO: Relatório inserido com ID: " . $pdo->lastInsertId());
    } else {
        logMsg("FALHA SQL: " . print_r($stmt->errorInfo(), true));
    }

} catch (Exception $e) {
    logMsg("EXCEÇÃO: " . $e->getMessage());
}
?>