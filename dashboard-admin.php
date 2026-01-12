<?php
session_start();
require_once 'conexao.php';

// Verificar permissão (apenas admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=random";

$mensagem = '';
$erro = '';

// Adicionar Professor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_professor') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if ($nome && $email) {
        try {
            $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha, tipo_utilizador) VALUES (:nome, :email, '123456', 'professor')");
            $stmt->execute(['nome' => $nome, 'email' => $email]);
            $mensagem = "Professor adicionado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao adicionar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}

// Remover Professor
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM utilizadores WHERE id = :id AND tipo_utilizador = 'professor'");
    $stmt->execute(['id' => $id]);
    header("Location: dashboard-admin.php");
    exit;
}

// Adicionar Curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_curso') {
    $nome_curso = $_POST['nome_curso'] ?? '';
    
    if ($nome_curso) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cursos (nome) VALUES (:nome)");
            $stmt->execute(['nome' => $nome_curso]);
            $mensagem = "Curso adicionado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao adicionar curso: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha o nome do curso.";
    }
}

// Editar Curso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_curso') {
    $id_curso = $_POST['id_curso'] ?? '';
    $nome_curso = $_POST['nome_curso'] ?? '';
    
    if ($id_curso && $nome_curso) {
        try {
            $stmt = $pdo->prepare("UPDATE cursos SET nome = :nome WHERE id = :id");
            $stmt->execute(['nome' => $nome_curso, 'id' => $id_curso]);
            $mensagem = "Curso atualizado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar curso: " . $e->getMessage();
        }
    } else {
        $erro = "Dados inválidos para atualização.";
    }
}

// Remover Curso
if (isset($_GET['delete_curso_id'])) {
    $id = $_GET['delete_curso_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard-admin.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao remover curso: " . $e->getMessage();
    }
}

// Estatísticas
$total_professores = $pdo->query("SELECT COUNT(*) FROM utilizadores WHERE tipo_utilizador = 'professor'")->fetchColumn();
$total_alunos = $pdo->query("SELECT COUNT(*) FROM utilizadores WHERE tipo_utilizador = 'aluno'")->fetchColumn();
$total_estagios = $pdo->query("SELECT COUNT(*) FROM estagios WHERE estado = 'aceite'")->fetchColumn();
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();

// Lista de Professores
$professores = $pdo->query("SELECT * FROM utilizadores WHERE tipo_utilizador = 'professor' ORDER BY id DESC")->fetchAll();

// Lista de Cursos
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - InternFLOW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard-admin.php" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                <div class="logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <span class="logo-text">InternFLOW</span>
            </a>
        </div>
        
        <div class="nav-center-links">
            <a href="dashboard-admin.php" class="active-purple">Painel</a>
            <!-- Chat removido para admin -->
        </div>

        <div class="nav-actions">
            <a href="logout.php" class="action-icon" title="Sair">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
            <!-- <i class="fa-regular fa-bell action-icon"></i> -->
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content centered-container">
        
        <h1 class="page-title">Dashboard do Administrador</h1>

        <?php if ($mensagem): ?>
            <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div style="background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">Visão Geral</h2>
        <div class="cards-container">
            <div class="card">
                <div class="card-info">
                    <span class="card-label">Total de Professores</span>
                    <strong class="card-value"><?php echo $total_professores; ?></strong>
                    <span class="card-sub">Professores registados</span>
                </div>
                <div class="card-icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <span class="card-label">Total de Alunos</span>
                    <strong class="card-value"><?php echo $total_alunos; ?></strong>
                    <span class="card-sub">Alunos ativos</span>
                </div>
                <div class="card-icon-box">
                    <i class="fa-solid fa-book"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <span class="card-label">Estágios Ativos</span>
                    <strong class="card-value"><?php echo $total_estagios; ?></strong>
                    <span class="card-sub">Estágios em andamento</span>
                </div>
                <div class="card-icon-box">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
            </div>
            <div class="card">
                <div class="card-info">
                    <span class="card-label">Total de Cursos</span>
                    <strong class="card-value"><?php echo $total_cursos; ?></strong>
                    <span class="card-sub">Cursos disponíveis</span>
                </div>
                <div class="card-icon-box">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>
        </div>

        <div class="table-header-group">
            <h2 class="section-title">Gestão de Professores</h2>
            <!-- Form simples para adicionar -->
            <form method="POST" class="form-inline">
                <input type="hidden" name="action" value="add_professor">
                <input type="text" name="nome" placeholder="Nome" required class="form-input">
                <input type="email" name="email" placeholder="Email" required class="form-input">
                <button type="submit" class="btn-primary">Adicionar</button>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th class="actions-cell">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($professores as $prof): ?>
                    <tr>
                        <td>#<?php echo $prof['id']; ?></td>
                        <td><?php echo htmlspecialchars($prof['nome']); ?></td>
                        <td><?php echo htmlspecialchars($prof['email']); ?></td>
                        <td class="actions-cell">
                            <a href="dashboard-admin.php?delete_id=<?php echo $prof['id']; ?>" class="icon-action text-danger" onclick="return confirm('Tem a certeza que deseja eliminar este professor?');">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-header-group" style="margin-top: 40px;">
            <h2 class="section-title">Gestão de Cursos</h2>
            <form method="POST" class="form-inline">
                <input type="hidden" name="action" value="add_curso">
                <input type="text" name="nome_curso" placeholder="Nome do Curso" required class="form-input" style="min-width: 300px;">
                <button type="submit" class="btn-primary">Adicionar Curso</button>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Curso</th>
                        <th class="actions-cell">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cursos as $curso): ?>
                    <tr>
                        <td>#<?php echo $curso['id']; ?></td>
                        <td><?php echo htmlspecialchars($curso['nome']); ?></td>
                        <td class="actions-cell">
                            <button type="button" class="icon-action" style="background:none; border:none; color:#4361ee; margin-right: 10px;" onclick="editarCurso(<?php echo $curso['id']; ?>, '<?php echo htmlspecialchars(addslashes($curso['nome'])); ?>')">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                            <a href="dashboard-admin.php?delete_curso_id=<?php echo $curso['id']; ?>" class="icon-action text-danger" onclick="return confirm('Tem a certeza que deseja eliminar este curso?');">
                                <i class="fa-regular fa-trash-can"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
    <script src="js/script.js"></script>
    <script>
        function editarCurso(id, nome) {
            criarModal('Editar Curso', `
                <form method="POST" action="dashboard-admin.php">
                    <input type="hidden" name="action" value="edit_curso">
                    <input type="hidden" name="id_curso" value="${id}">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; color:#4b5563;">Nome do Curso:</label>
                        <input type="text" name="nome_curso" value="${nome}" required style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" class="btn-primary">Guardar Alterações</button>
                    </div>
                </form>
            `, []);
        }
    </script>
</body>
</html>
