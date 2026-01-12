<?php
require_once 'conexao.php';

try {
    // Check table structure
    $stmt = $pdo->query("DESCRIBE relatorios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in 'relatorios':\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    // Check if 'arquivo' column exists, if not add it
    $hasArquivo = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'arquivo') {
            $hasArquivo = true;
            break;
        }
    }

    if (!$hasArquivo) {
        echo "\nColumn 'arquivo' missing. Adding it...\n";
        $pdo->exec("ALTER TABLE relatorios ADD COLUMN arquivo VARCHAR(255) NULL AFTER conteudo");
        echo "Column 'arquivo' added.\n";
    } else {
        echo "\nColumn 'arquivo' exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>