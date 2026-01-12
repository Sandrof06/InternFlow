<?php
require 'conexao.php';

echo "--- Verificação de IDs ---\n";
$joao = $pdo->query("SELECT id, nome FROM utilizadores WHERE nome LIKE '%Joao%' OR nome LIKE '%João%'")->fetch(PDO::FETCH_ASSOC);
$constantino = $pdo->query("SELECT id, nome FROM utilizadores WHERE nome LIKE '%Constantino%'")->fetch(PDO::FETCH_ASSOC);

print_r($joao);
print_r($constantino);

if ($joao && $constantino) {
    echo "\n--- Verificação de Estágio ---\n";
    $sql = "SELECT * FROM estagios WHERE aluno_id = :aid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['aid' => $joao['id']]);
    $estagios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($estagios)) {
        echo "O aluno João (ID {$joao['id']}) NÃO tem estágios registados.\n";
    } else {
        foreach ($estagios as $e) {
            echo "Estágio ID: {$e['id']} | Orientador ID: {$e['orientador_id']}\n";
            if ($e['orientador_id'] == $constantino['id']) {
                echo "-> CONFIRMADO: João é aluno do Constantino neste estágio.\n";
            } else {
                echo "-> AVISO: Neste estágio, o orientador NÃO é o Constantino (ID {$constantino['id']}).\n";
            }
        }
    }
}
?>