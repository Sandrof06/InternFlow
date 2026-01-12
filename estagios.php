<?php
session_start();
require_once 'conexao.php';

// Verificar permissão (apenas professor/admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'professor' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=111827&color=fff";

// Ações (Aprovar/Rejeitar)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = '';

    if ($action === 'approve') {
        $status = 'aceite';
    } elseif ($action === 'reject') {
        $status = 'nao_apto';
    } elseif ($action === 'delete') {
        // Excluir
        $stmt = $pdo->prepare("DELETE FROM estagios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: estagios.php");
        exit;
    }

    if ($status) {
        $stmt = $pdo->prepare("UPDATE estagios SET estado = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
        header("Location: estagios.php");
        exit;
    }
}

// Filtros
$filtro = $_GET['filtro'] ?? 'todos';
$sql = "SELECT e.*, u.nome as nome_aluno FROM estagios e JOIN utilizadores u ON e.aluno_id = u.id";
$params = [];

if ($filtro !== 'todos') {
    $sql .= " WHERE e.estado = :estado";
    $params['estado'] = $filtro;
}
$sql .= " ORDER BY e.criado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$estagios = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estágios - InternFLOW</title>
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
            <!-- <i class="fa-regular fa-bell action-icon"></i> -->
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content centered-container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 class="page-title" style="margin-bottom: 0;">Gestão de Estágios</h1>
            <a href="criar_estagio.php" class="btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-plus"></i> Novo Estágio</a>
        </div>

        <div class="filter-bar">
            <a href="estagios.php?filtro=todos" class="filter-tab <?php echo $filtro === 'todos' ? 'active' : ''; ?>">Todos</a>
            <a href="estagios.php?filtro=aceite" class="filter-tab <?php echo $filtro === 'aceite' ? 'active' : ''; ?>">Aceites</a>
            <a href="estagios.php?filtro=nao_apto" class="filter-tab <?php echo $filtro === 'nao_apto' ? 'active' : ''; ?>">Não Aptos</a>
            <a href="estagios.php?filtro=pendente" class="filter-tab <?php echo $filtro === 'pendente' ? 'active' : ''; ?>">Pendentes</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome do Aluno</th>
                        <th>Curso</th>
                        <th>Área de Estágio</th>
                        <th>Estado</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($estagios) > 0): ?>
                        <?php foreach ($estagios as $estagio): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($estagio['nome_aluno']); ?></td>
                            <td>
                                <span class="course-badge badge-gray" style="font-weight: normal; font-size: 13px;">
                                    <?php echo htmlspecialchars($estagio['curso']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($estagio['area']); ?></td>
                            <td>
                                <?php
                                    $badgeClass = 'badge-gray';
                                    $estadoLabel = ucfirst($estagio['estado']);
                                    
                                    if ($estagio['estado'] === 'aceite') {
                                        $badgeClass = 'badge-success';
                                        $estadoLabel = 'Aceite';
                                    } elseif ($estagio['estado'] === 'pendente') {
                                        $badgeClass = 'badge-warning';
                                        $estadoLabel = 'Pendente';
                                    } elseif ($estagio['estado'] === 'nao_apto') {
                                        $badgeClass = 'badge-danger';
                                        $estadoLabel = 'Não Apto';
                                    }
                                ?>
                                <span class="course-badge <?php echo $badgeClass; ?>"><?php echo $estadoLabel; ?></span>
                            </td>
                            <td class="actions-cell">
                                <?php if ($estagio['estado'] === 'pendente'): ?>
                                    <a href="estagios.php?action=approve&id=<?php echo $estagio['id']; ?>" class="btn-action" style="color: #059669; background-color: #ecfdf5;">Aprovar</a>
                                    <a href="estagios.php?action=reject&id=<?php echo $estagio['id']; ?>" class="btn-action" style="color: #dc2626; background-color: #fef2f2;">Rejeitar</a>
                                <?php else: ?>
                                    <a href="estagios.php?action=delete&id=<?php echo $estagio['id']; ?>" class="btn-action" style="color: #6b7280;" onclick="return confirm('Tem a certeza?');"><i class="fa-solid fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #6b7280;">Nenhum estágio encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <script src="js/script.js"></script>
</body>
</html>
