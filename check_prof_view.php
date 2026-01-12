<?php
require 'conexao.php';

echo "--- Verificando Visibilidade do Professor ---\n";

// 1. Obter o estágio onde inserimos o teste (o primeiro da tabela)
$stmt = $pdo->query("SELECT id, aluno_id, orientador_id FROM estagios LIMIT 1");
$estagio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$estagio) {
    echo "Erro: Estágio não encontrado.\n";
    exit;
}

$orientador_id = $estagio['orientador_id'];
echo "Estágio ID: " . $estagio['id'] . "\n";
echo "Orientador ID: " . $orientador_id . "\n";

// 2. Simular a query do professor (cópia de relatorio.php)
$sql = "
    SELECT r.id, r.titulo, r.submetido_em, u.nome as nome_aluno
    FROM relatorios r
    JOIN estagios e ON r.estagio_id = e.id
    JOIN utilizadores u ON e.aluno_id = u.id
    WHERE e.orientador_id = :prof_id
    ORDER BY r.submetido_em DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['prof_id' => $orientador_id]);
$relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- Relatórios encontrados para o Professor $orientador_id: ---\n";
if (count($relatorios) > 0) {
    foreach ($relatorios as $r) {
        echo "ID: " . $r['id'] . " | Título: " . $r['titulo'] . " | Aluno: " . $r['nome_aluno'] . "\n";
    }
} else {
    echo "NENHUM relatório encontrado.\n";
}
?>