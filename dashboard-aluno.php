<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'conexao.php';

// Verificar se é aluno
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'aluno') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=random";

// DEBUG GLOBAL - RASTREAR TODO E QUALQUER POST (REMOVIDO PARA PRODUÇÃO)
// if ($_SERVER['REQUEST_METHOD'] === 'POST') { ... }

$mensagem = '';
$erro = '';

// Obter estágio do aluno
$stmt = $pdo->prepare("
    SELECT e.*, u.nome as nome_orientador 
    FROM estagios e 
    LEFT JOIN utilizadores u ON e.orientador_id = u.id 
    WHERE e.aluno_id = :aluno_id 
    ORDER BY e.criado_em DESC 
    LIMIT 1
");
$stmt->execute(['aluno_id' => $user_id]);
$estagio = $stmt->fetch();

// Lógica de submissão de relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_report') {
    
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    
    if (!$estagio) {
        $erro = "Você não tem um estágio ativo para enviar relatórios.";
    } elseif ($titulo && ($conteudo || !empty($_FILES['relatorio_file']['name']))) {
        try {
            $arquivo_path = null;
            
            // Verificar upload
            if (isset($_FILES['relatorio_file']) && $_FILES['relatorio_file']['error'] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES['relatorio_file']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/uploads/relatorios/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_name = $_FILES['relatorio_file']['name'];
                    $file_tmp = $_FILES['relatorio_file']['tmp_name'];
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    if ($ext !== 'pdf') {
                        throw new Exception("Apenas ficheiros PDF são permitidos.");
                    }
                    
                    $new_name = uniqid('rel_') . '_' . time() . '.pdf';
                    $dest_path = $upload_dir . $new_name;
                    $db_path = 'uploads/relatorios/' . $new_name;
                    
                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        $arquivo_path = $db_path;
                    } else {
                        throw new Exception("Falha ao guardar o ficheiro no servidor.");
                    }
                } else {
                    throw new Exception("Erro no upload do ficheiro.");
                }
            }
            
            if (!$arquivo_path && empty($conteudo)) {
                 throw new Exception("É necessário enviar um ficheiro PDF ou escrever observações.");
            }

            // Inserir na BD
            $sql = "INSERT INTO relatorios (estagio_id, titulo, conteudo, arquivo, submetido_em) VALUES (:eid, :tit, :cont, :arq, NOW())";
            $stmt = $pdo->prepare($sql);
            $params = [
                'eid' => $estagio['id'],
                'tit' => $titulo,
                'cont' => $conteudo,
                'arq' => $arquivo_path
            ];
            
            if ($stmt->execute($params)) {
                $titulo = '';
                $conteudo = '';
                $orientador_nome = $estagio['nome_orientador'] ?? 'seu professor';
                $mensagem = "Relatório enviado com sucesso! O orientador ($orientador_nome) será notificado.";
            } else {
                throw new Exception("Erro ao registar o relatório na base de dados.");
            }
            
        } catch (Exception $e) {
            $erro = "Erro: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha o título e anexe um ficheiro ou escreva observações.";
    }
}

// Obter feedbacks (relatórios com feedback)
$feedbacks = [];
if ($estagio) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.nome as nome_professor 
        FROM relatorios r
        JOIN estagios e ON r.estagio_id = e.id
        LEFT JOIN utilizadores u ON e.orientador_id = u.id
        WHERE r.estagio_id = :estagio_id AND r.feedback IS NOT NULL
        ORDER BY r.avaliado_em DESC
    ");
    $stmt->execute(['estagio_id' => $estagio['id']]);
    $feedbacks = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Aluno - InternFLOW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/aluno.css">
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard-aluno.php" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                <div class="logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <span class="logo-text">InternFLOW</span>
            </a>
        </div>
        
        <div class="nav-center-links">
            <a href="dashboard-aluno.php" class="active-purple">Painel</a>
            <a href="chat.php">Chat</a>
        </div>

        <div class="nav-actions">
            <a href="logout.php" class="action-icon" title="Sair">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content centered-container">
        
        <?php if ($mensagem): ?>
            <div class="notification success">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="notification error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <div class="welcome-section">
            <h1 class="page-title">Olá, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p class="subtitle">Acompanhe o progresso do seu estágio e mantenha o seu orientador informado.</p>
        </div>

        <div class="grid-top">
            
            <div class="card stage-card">
                <div class="card-header-icon">
                    <i class="fa-solid fa-briefcase"></i>
                    <h3><?php echo $estagio ? htmlspecialchars($estagio['titulo']) : 'Nenhum Estágio Ativo'; ?></h3>
                </div>
                <p class="card-subtitle">Detalhes do seu estágio atual</p>

                <?php if ($estagio): ?>
                <div class="stage-info-list">
                    <div class="info-item">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span><strong>Curso:</strong> <?php echo htmlspecialchars($estagio['curso']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-book-open"></i>
                        <span><strong>Área:</strong> <?php echo htmlspecialchars($estagio['area']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fa-regular fa-user"></i>
                        <span><strong>Professor Responsável:</strong> <?php echo htmlspecialchars($estagio['nome_orientador'] ?? 'Não atribuído'); ?></span>
                    </div>
                </div>

                <div class="status-area">
                    <span>Estado:</span>
                    <?php
                        $badgeClass = 'badge-gray';
                        $icon = 'fa-circle-question';
                        $estadoTexto = ucfirst($estagio['estado']);
                        
                        if ($estagio['estado'] === 'aceite') { 
                            $badgeClass = 'badge-success'; 
                            $icon = 'fa-circle-check'; 
                        } elseif ($estagio['estado'] === 'pendente') { 
                            $badgeClass = 'badge-warning'; 
                            $icon = 'fa-hourglass-half'; 
                        } elseif ($estagio['estado'] === 'nao_apto') { 
                            $badgeClass = 'badge-danger'; 
                            $icon = 'fa-circle-xmark'; 
                            $estadoTexto = 'Não Apto';
                        }
                    ?>
                    <span class="<?php echo $badgeClass; ?>"><i class="fa-regular <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($estadoTexto); ?></span>
                </div>
                <?php else: ?>
                    <p>Ainda não está associado a um estágio. Fale com o seu professor.</p>
                <?php endif; ?>
            </div>

            <div class="card report-card">
                <div class="card-header-icon">
                    <i class="fa-solid fa-file-pen"></i>
                    <h3>Novo Relatório</h3>
                </div>
                <p class="card-subtitle">Envie o seu relatório mensal (PDF)</p>

                <form class="report-form" method="POST" action="dashboard-aluno.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="submit_report">
                    
                    <div class="form-group">
                        <label>Título do Relatório</label>
                        <input type="text" name="titulo" placeholder="Ex: Relatório de Janeiro" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ficheiro PDF</label>
                        <div class="file-upload-container">
                            <input type="file" name="relatorio_file" id="relatorio_file" accept=".pdf" required class="file-input" onchange="updateFileName(this)">
                            <label for="relatorio_file" class="file-label">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Escolher ficheiro...</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Observações (Opcional)</label>
                        <textarea name="conteudo" placeholder="Breve descrição..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary full-width">
                        <i class="fa-solid fa-paper-plane"></i> Enviar Relatório
                    </button>
                </form>
            </div>
        </div>

        <div class="grid-bottom">
            
            <div class="card">
                <div class="card-header-icon">
                    <i class="fa-regular fa-envelope"></i>
                    <h3>Feedback do Professor</h3>
                </div>
                <p class="card-subtitle">Revisões e comentários sobre os seus relatórios</p>

                <div class="feedback-list">
                    <?php if (count($feedbacks) > 0): ?>
                        <?php foreach ($feedbacks as $fb): ?>
                        <div class="feedback-item">
                            <div class="feedback-meta">
                                <span>De: <?php echo htmlspecialchars($fb['nome_professor']); ?></span>
                                <span class="date"><?php echo date('d/m/Y', strtotime($fb['avaliado_em'])); ?></span>
                            </div>
                            <p><strong>Ref: <?php echo htmlspecialchars($fb['titulo']); ?></strong><br><?php echo htmlspecialchars($fb['feedback']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px 20px; color: #9ca3af; background: #f9fafb; border-radius: 8px; border: 1px dashed #e5e7eb;">
                            <i class="fa-regular fa-comments" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                            <p style="font-size: 14px;">Ainda não tem feedback disponível.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header-icon">
                    <i class="fa-solid fa-circle-info"></i>
                    <h3>Recursos Úteis</h3>
                </div>
                <p class="card-subtitle">Links e guias para auxiliar o seu estágio</p>

                <div class="resources-list">
                    <a href="#" class="resource-link"><i class="fa-solid fa-link"></i> Guia de Boas Práticas para Estágios</a>
                    <a href="#" class="resource-link"><i class="fa-solid fa-link"></i> Modelo de Relatório de Estágio</a>
                    <a href="#" class="resource-link"><i class="fa-solid fa-link"></i> Dicas para Desenvolvimento Profissional</a>
                </div>
            </div>
        </div>

    </div>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        function updateFileName(input) {
            const label = input.nextElementSibling.querySelector('span');
            if (input.files && input.files.length > 0) {
                label.textContent = input.files[0].name;
                input.nextElementSibling.style.borderColor = '#4361ee';
                input.nextElementSibling.style.color = '#4361ee';
            } else {
                label.textContent = 'Escolher ficheiro...';
                input.nextElementSibling.style.borderColor = '';
                input.nextElementSibling.style.color = '';
            }
        }
    </script>
</body>
</html>
