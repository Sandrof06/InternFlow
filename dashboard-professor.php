<?php
session_start();
require_once 'conexao.php';

// Verificar se é professor
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'professor' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=111827&color=fff";

$mensagem = '';
$erro = '';

// Lógica para adicionar aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $nome_aluno = $_POST['nome'] ?? '';
    $email_aluno = $_POST['email'] ?? '';
    $curso_id = $_POST['curso_id'] ?? null;

    if ($nome_aluno && $email_aluno && $curso_id) {
        try {
            // Verifica se email já existe
            $stmt = $pdo->prepare("SELECT id FROM utilizadores WHERE email = :email");
            $stmt->execute(['email' => $email_aluno]);
            if ($stmt->fetch()) {
                $erro = "Este email já está registado.";
            } else {
                // Cria aluno com senha padrão '123456' e curso associado
                $stmt = $pdo->prepare("INSERT INTO utilizadores (nome, email, senha, tipo_utilizador, curso_id) VALUES (:nome, :email, '123456', 'aluno', :curso_id)");
                $stmt->execute(['nome' => $nome_aluno, 'email' => $email_aluno, 'curso_id' => $curso_id]);
                $mensagem = "Aluno adicionado com sucesso!";
            }
        } catch (PDOException $e) {
            $erro = "Erro ao adicionar aluno: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos, incluindo o curso.";
    }
}

// Lógica para remover aluno (se implementado)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Validar se é aluno
    $stmt = $pdo->prepare("DELETE FROM utilizadores WHERE id = :id AND tipo_utilizador = 'aluno'");
    $stmt->execute(['id' => $delete_id]);
    header("Location: dashboard-professor.php"); // Limpa a URL
    exit;
}

// Obter contagens de estágios
$stats = [
    'aceite' => 0,
    'pendente' => 0,
    'nao_apto' => 0
];

$stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM estagios GROUP BY estado");
while ($row = $stmt->fetch()) {
    if (isset($stats[$row['estado']])) {
        $stats[$row['estado']] = $row['total'];
    }
}

// Obter lista de cursos disponíveis
$cursos_disponiveis = [];
try {
    $cursos_disponiveis = $pdo->query("SELECT * FROM cursos ORDER BY nome ASC")->fetchAll();
} catch (PDOException $e) {
    // Tabela cursos pode não existir ainda ou erro
}

// Obter lista de alunos
$alunos = $pdo->query("SELECT u.*, c.nome as nome_curso FROM utilizadores u LEFT JOIN cursos c ON u.curso_id = c.id WHERE u.tipo_utilizador = 'aluno' ORDER BY u.id DESC LIMIT 10")->fetchAll();

// Obter relatórios recentes
$relatorios_recentes = [];
try {
    // Se for admin vê todos, se for professor vê apenas os seus
    if ($_SESSION['user_role'] === 'admin') {
        $sql_rel = "
            SELECT r.*, u.nome as nome_aluno, e.empresa 
            FROM relatorios r
            JOIN estagios e ON r.estagio_id = e.id
            JOIN utilizadores u ON e.aluno_id = u.id
            ORDER BY r.submetido_em DESC
            LIMIT 5
        ";
        $stmt = $pdo->query($sql_rel);
        $relatorios_recentes = $stmt->fetchAll();
    } else {
        $sql_rel = "
            SELECT r.*, u.nome as nome_aluno, e.empresa 
            FROM relatorios r
            JOIN estagios e ON r.estagio_id = e.id
            JOIN utilizadores u ON e.aluno_id = u.id
            WHERE e.orientador_id = :prof_id
            ORDER BY r.submetido_em DESC
            LIMIT 5
        ";
        $stmt = $pdo->prepare($sql_rel);
        $stmt->execute(['prof_id' => $_SESSION['user_id']]);
        $relatorios_recentes = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Erro ao buscar relatórios
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor - InternFLOW</title>
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
            <a href="dashboard-professor.php" class="active-purple">Painel</a>
            <a href="estagios.php">Gerir Estágios</a>
            <a href="relatorio.php">Relatórios</a>
            <a href="chat.php">Chat</a>
        </div>
        
        <div class="nav-actions">
            <a href="logout.php" class="action-icon" title="Sair"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            <!-- <i class="fa-regular fa-bell action-icon"></i> -->
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content centered-container">
        
        <h1 class="page-title">Painel do Professor</h1>

        <h2 class="section-title">Visão Geral dos Estágios</h2>
        <div class="cards-container">
            <div class="card stat-card">
                <div class="card-content">
                    <div class="card-header">
                        <span class="card-label">Estágios Aceites</span>
                        <div class="icon-wrapper"><i class="fa-regular fa-circle-check"></i></div>
                    </div>
                    <strong class="card-value"><?php echo $stats['aceite']; ?></strong>
                    <span class="card-sub">Confirmados e em andamento</span>
                </div>
            </div>
            
            <div class="card stat-card">
                <div class="card-content">
                    <div class="card-header">
                        <span class="card-label">Estágios Pendentes</span>
                        <div class="icon-wrapper"><i class="fa-solid fa-hourglass-half"></i></div>
                    </div>
                    <strong class="card-value"><?php echo $stats['pendente']; ?></strong>
                    <span class="card-sub">A aguardar aprovação ou feedback</span>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-content">
                    <div class="card-header">
                        <span class="card-label">Estágios Não Aptos</span>
                        <div class="icon-wrapper"><i class="fa-regular fa-circle-xmark"></i></div>
                    </div>
                    <strong class="card-value"><?php echo $stats['nao_apto']; ?></strong>
                    <span class="card-sub">Rejeitados ou cancelados</span>
                </div>
            </div>
        </div>

        <div class="management-section section-spacing">
            <div class="section-header-row">
                <h2 class="section-title mb-0">Relatórios Recentes</h2>
                <a href="relatorio.php" class="link-view-all">Ver todos</a>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Aluno</th>
                            <th>Título</th>
                            <th>Data</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($relatorios_recentes) > 0): ?>
                            <?php foreach ($relatorios_recentes as $rel): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rel['nome_aluno']); ?></td>
                                <td><?php echo htmlspecialchars($rel['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($rel['submetido_em'])); ?></td>
                                <td>
                                    <?php if ($rel['feedback']): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Avaliado</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><i class="fa-regular fa-clock"></i> Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="relatorio.php?id=<?php echo $rel['id']; ?>" class="btn-action"><i class="fa-solid fa-eye"></i> Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-table-cell">Nenhum relatório recente.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="management-section">
            <h2 class="section-title">Gestão de Alunos</h2>
            
            <div class="tab-group">
                <button class="tab-btn active">Adicionar Aluno</button>
                <!-- Botão remover apenas visual por enquanto, ou poderia alternar visibilidade -->
            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <div class="form-area centered-container">
                <h3>Adicionar Novo Aluno</h3>
                <form class="add-student-form" method="POST" action="dashboard-professor.php">
                    <input type="hidden" name="action" value="add_student">
                    
                    <div class="form-col flex-grow">
                        <label>Nome do Aluno</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nome" placeholder="Nome Completo" required>
                        </div>
                    </div>

                    <div class="form-col flex-grow">
                        <label>Email Institucional</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="email.aluno@escola.pt" required>
                        </div>
                    </div>
                    
                    <div class="form-col flex-grow">
                        <label>Curso</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <select name="curso_id" required>
                                <option value="">Selecione o Curso...</option>
                                <?php foreach ($cursos_disponiveis as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col btn-col">
                        <button type="submit" class="btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>

            <div class="student-list-section centered-container list-container">
                <p class="list-title">Alunos Recentemente Adicionados:</p>
                
                <div class="student-list">
                    <?php foreach ($alunos as $aluno): ?>
                    <div class="student-row">
                        <span class="student-email fw-500"><?php echo htmlspecialchars($aluno['nome']); ?></span>
                        <span class="student-email"><?php echo htmlspecialchars($aluno['email']); ?></span>
                        <a href="dashboard-professor.php?delete_id=<?php echo $aluno['id']; ?>" class="course-badge badge-danger btn-remove-student" onclick="return confirm('Tem a certeza que deseja remover este aluno?');">Remover</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
    <script src="js/script.js"></script>
</body>
</html>
