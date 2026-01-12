<?php
$dirs = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/relatorios'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "Criado: $dir\n";
        } else {
            echo "ERRO ao criar: $dir\n";
        }
    } else {
        echo "Já existe: $dir\n";
    }
    // Tentar chmod
    @chmod($dir, 0777);
}
echo "Permissões ajustadas.\n";
?>