<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'professor' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=111827&color=fff";

$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aluno_id = $_POST['aluno_id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $curso = $_POST['curso'] ?? '';
    $area = $_POST['area'] ?? '';
    $empresa = $_POST['empresa'] ?? '';

    if ($aluno_id && $titulo && $curso && $area) {
        try {
            $stmt = $pdo->prepare("INSERT INTO estagios (aluno_id, orientador_id, titulo, curso, area, empresa, estado) VALUES (:aluno_id, :orientador_id, :titulo, :curso, :area, :empresa, 'pendente')");
            $stmt->execute([
                'aluno_id' => $aluno_id,
                'orientador_id' => $_SESSION['user_id'],
                'titulo' => $titulo,
                'curso' => $curso,
                'area' => $area,
                'empresa' => $empresa
            ]);
            $mensagem = "Estágio criado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao criar estágio: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha os campos obrigatórios.";
    }
}

// Buscar alunos para o select
$alunos = $pdo->query("SELECT id, nome FROM utilizadores WHERE tipo_utilizador = 'aluno'")->fetchAll();

// Buscar cursos para o select
try {
    $cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome ASC")->fetchAll();
} catch (Exception $e) {
    $cursos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Estágio - InternFLOW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/professor.css">
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard-professor.php" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                <div class="logo-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="logo-text">InternFLOW</span>
            </a>
        </div>
        
        <div class="nav-center-links">
            <a href="dashboard-professor.php">Painel</a>
            <a href="estagios.php" class="active-purple">Gerir Estágios</a>
            <a href="relatorio.php">Relatórios</a>
            <a href="chat.php">Chat</a>
        </div>
        
        <div class="nav-actions">
            <a href="logout.php" class="action-icon" title="Sair"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content centered-container">
        
        <div class="form-area" style="max-width: 800px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 class="page-title" style="margin-bottom: 0;">Novo Estágio</h1>
                <a href="estagios.php" class="btn-action"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            </div>

            <?php if ($mensagem): ?>
                <div style="padding: 15px; background-color: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div style="padding: 15px; background-color: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $erro; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="add-student-form" style="flex-direction: column; align-items: stretch; gap: 20px;">
                
                <div class="form-col">
                    <label>Selecione o Aluno</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-user"></i>
                        <select name="aluno_id" required>
                            <option value="">Selecione um aluno...</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?php echo $aluno['id']; ?>"><?php echo htmlspecialchars($aluno['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-col">
                    <label>Título do Estágio</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-heading"></i>
                        <input type="text" name="titulo" placeholder="Ex: Desenvolvimento Web Fullstack" required>
                    </div>
                </div>

                <div class="form-col">
                    <label>Empresa</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-building"></i>
                        <input type="text" name="empresa" placeholder="Nome da Empresa">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-col">
                        <label>Curso</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <input type="text" name="curso" placeholder="Ex: Engenharia Informática" required list="lista-cursos">
                            <datalist id="lista-cursos">
                                <?php foreach ($cursos as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c['nome']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="form-col">
                        <label>Área</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-layer-group"></i>
                            <input type="text" name="area" placeholder="Ex: Backend" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 10px;">Criar Estágio</button>
            </form>
        </div>

    </div>
    <script src="js/script.js"></script>
</body>
</html>
