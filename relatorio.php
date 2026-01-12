<?php
session_start();
require_once 'conexao.php';

// Verificar permissões
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'professor' && $_SESSION['user_role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=111827&color=fff";

$mensagem = '';
$erro = '';

// --- Lógica de Avaliação (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $feedback = $_POST['feedback'] ?? '';
    $rating = $_POST['rating'] ?? 5;

    if (!empty($report_id)) {
        try {
            $stmt = $pdo->prepare("UPDATE relatorios SET feedback = :feedback, classificacao = :rating, avaliado_em = NOW() WHERE id = :id");
            $stmt->execute(['feedback' => $feedback, 'rating' => $rating, 'id' => $report_id]);
            $mensagem = "Avaliação guardada com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao guardar avaliação: " . $e->getMessage();
        }
    }
}

// --- Obter Lista de Relatórios (Sidebar) ---
$relatorios_lista = [];
try {
    if ($_SESSION['user_role'] === 'admin') {
        // Admin vê tudo
        $sql = "
            SELECT r.id, r.titulo, r.submetido_em, u.nome as nome_aluno, u.avatar_url, r.arquivo
            FROM relatorios r
            JOIN estagios e ON r.estagio_id = e.id
            JOIN utilizadores u ON e.aluno_id = u.id
            ORDER BY r.submetido_em DESC
        ";
        $stmt = $pdo->query($sql);
    } else {
        // Professor vê apenas os seus
        $sql = "
            SELECT r.id, r.titulo, r.submetido_em, u.nome as nome_aluno, u.avatar_url, r.arquivo
            FROM relatorios r
            JOIN estagios e ON r.estagio_id = e.id
            JOIN utilizadores u ON e.aluno_id = u.id
            WHERE e.orientador_id = :prof_id
            ORDER BY r.submetido_em DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['prof_id' => $user_id]);
    }
    $relatorios_lista = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro = "Erro ao carregar lista: " . $e->getMessage();
}

// --- Determinar Relatório Atual ---
$relatorio_atual = null;
if (isset($_GET['id'])) {
    $selected_id = $_GET['id'];
    // Tenta encontrar o relatório específico na lista já carregada (segurança implícita)
    foreach ($relatorios_lista as $rel) {
        if ($rel['id'] == $selected_id) {
            $relatorio_atual_id = $rel['id'];
            break;
        }
    }
} elseif (count($relatorios_lista) > 0) {
    // Padrão: o primeiro da lista
    $relatorio_atual_id = $relatorios_lista[0]['id'];
}

// Se identificamos um ID, carregamos os detalhes completos
if (isset($relatorio_atual_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.nome as nome_aluno, u.avatar_url, e.curso, e.area
            FROM relatorios r
            JOIN estagios e ON r.estagio_id = e.id
            JOIN utilizadores u ON e.aluno_id = u.id
            WHERE r.id = :id
        ");
        $stmt->execute(['id' => $relatorio_atual_id]);
        $relatorio_atual = $stmt->fetch();
    } catch (PDOException $e) {
        $erro = "Erro ao carregar detalhes do relatório.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação de Relatórios - InternFLOW</title>
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
            <a href="estagios.php">Gerir Estágios</a>
            <a href="relatorio.php" class="active-purple">Relatórios</a>
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
        
        <div class="report-layout-container">
            
            <!-- Sidebar com Lista -->
            <aside class="users-sidebar">
                <div style="padding: 15px; border-bottom: 1px solid #e5e7eb; margin-bottom: 10px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #111827;">
                        Relatórios Recebidos 
                        <span style="background: #e0e7ff; color: #4361ee; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">
                            <?php echo count($relatorios_lista); ?>
                        </span>
                    </h3>
                </div>
                
                <?php if (isset($erro_debug)): ?>
                    <div style="padding: 10px; color: #b91c1c; background: #fef2f2; font-size: 12px; margin: 10px; border-radius: 6px;">
                        <?php echo $erro_debug; ?>
                    </div>
                <?php endif; ?>

                <ul class="user-list">

                <div class="users-list-scroll">
                <?php if (count($relatorios_lista) > 0): ?>
                    <?php foreach ($relatorios_lista as $rel): ?>
                    <div class="user-item <?php echo ($relatorio_atual && $rel['id'] == $relatorio_atual['id']) ? 'active' : ''; ?>">
                        <a href="relatorio.php?id=<?php echo $rel['id']; ?>">
                            <img src="<?php echo $rel['avatar_url'] ?? "https://ui-avatars.com/api/?name=" . urlencode($rel['nome_aluno']); ?>" alt="Avatar">
                            <div style="display:flex; flex-direction:column; overflow:hidden; width: 100%;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-weight:600; font-size: 14px;"><?php echo htmlspecialchars($rel['nome_aluno']); ?></span>
                                    <?php if(!empty($rel['arquivo'])): ?>
                                        <i class="fa-solid fa-paperclip" style="font-size: 10px; color: #6b7280;"></i>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size:12px; color:#6b7280; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;"><?php echo htmlspecialchars($rel['titulo']); ?></span>
                                <span style="font-size:11px; color:#9ca3af; margin-top: 2px;"><?php echo date('d/m H:i', strtotime($rel['submetido_em'])); ?></span>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: #6b7280;">
                        <i class="fa-regular fa-folder-open" style="font-size: 24px; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>Nenhum relatório encontrado.</p>
                    </div>
                <?php endif; ?>
                </div>
            </aside>

            <!-- Área Principal -->
            <main class="report-view-area">
                
                <?php if ($erro): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fecaca;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensagem): ?>
                    <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #a7f3d0;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($mensagem); ?>
                    </div>
                <?php endif; ?>

                <?php if ($relatorio_atual): ?>
                    
                    <div class="report-header-user">
                        <img src="<?php echo $relatorio_atual['avatar_url'] ?? "https://ui-avatars.com/api/?name=" . urlencode($relatorio_atual['nome_aluno']); ?>" alt="Avatar">
                        <div>
                            <h2 style="font-size: 18px; font-weight: 700; color: #111827;"><?php echo htmlspecialchars($relatorio_atual['nome_aluno']); ?></h2>
                            <p style="font-size: 13px; color: #6b7280;">Submetido em <?php echo date('d/m/Y às H:i', strtotime($relatorio_atual['submetido_em'])); ?></p>
                            <?php if (!empty($relatorio_atual['curso'])): ?>
                                <p style="font-size: 12px; color: #9ca3af; margin-top: 2px;">
                                    <i class="fa-solid fa-graduation-cap"></i> <?php echo htmlspecialchars($relatorio_atual['curso']); ?>
                                    <?php if (!empty($relatorio_atual['area'])): ?>
                                         • <?php echo htmlspecialchars($relatorio_atual['area']); ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="report-body">
                        <h1 class="report-title"><?php echo htmlspecialchars($relatorio_atual['titulo']); ?></h1>
                        
                        <?php 
                        $arquivo_db = $relatorio_atual['arquivo'] ?? null;
                        $temArquivo = false;
                        $webPath = '';

                        if ($arquivo_db) {
                            // Verifica se existe fisicamente (caminho absoluto do sistema)
                            $fsPath = __DIR__ . '/' . $arquivo_db;
                            if (file_exists($fsPath)) {
                                $temArquivo = true;
                                $webPath = $arquivo_db; // Caminho relativo para o navegador
                            }
                        }
                        
                        $conteudo = $relatorio_atual['conteudo'] ?? '';
                        ?>

                        <!-- Visualização do PDF -->
                        <?php if ($temArquivo): ?>
                            <div style="margin-bottom: 24px;">
                                <div class="pdf-container" style="position: relative; height: 700px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #f3f4f6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                    <iframe src="<?php echo htmlspecialchars($webPath); ?>" width="100%" height="100%" style="border: none;">
                                        <p style="padding: 20px; text-align: center;">O seu navegador não suporta visualização de PDF. <a href="<?php echo htmlspecialchars($webPath); ?>">Clique aqui para descarregar.</a></p>
                                    </iframe>
                                </div>
                                <div style="margin-top: 12px; display: flex; justify-content: flex-end;">
                                    <a href="<?php echo htmlspecialchars($webPath); ?>" target="_blank" class="btn-download" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 13px; transition: all 0.2s;">
                                        <i class="fa-solid fa-download"></i> Descarregar PDF
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Conteúdo de Texto -->
                        <?php if (!empty($conteudo)): ?>
                            <div style="white-space: pre-wrap; line-height: 1.6; color: #374151; background: #f9fafb; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb;">
                                <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 12px; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">Descrição / Observações</h3>
                                <?php echo nl2br(htmlspecialchars($conteudo)); ?>
                            </div>
                        <?php elseif (!$temArquivo): ?>
                            <div style="text-align: center; padding: 60px 20px; color: #9ca3af; background: #f9fafb; border-radius: 8px; border: 2px dashed #e5e7eb;">
                                <i class="fa-regular fa-file-circle-question" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                                <p style="font-size: 16px; font-weight: 500;">Este relatório não possui conteúdo.</p>
                                <p style="font-size: 14px;">O aluno não enviou ficheiro nem escreveu observações.</p>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Secção de Avaliação -->
                    <div class="evaluation-section">
                        <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 20px;">Avaliação do Professor</h3>
                        
                        <form method="POST" action="relatorio.php?id=<?php echo $relatorio_atual['id']; ?>">
                            <input type="hidden" name="report_id" value="<?php echo $relatorio_atual['id']; ?>">
                            
                            <div style="display: grid; grid-template-columns: 1fr 200px; gap: 20px; margin-bottom: 20px;">
                                <div class="form-group">
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">Feedback / Comentários</label>
                                    <textarea name="feedback" class="feedback-input" placeholder="Escreva aqui o seu feedback para o aluno..."><?php echo htmlspecialchars($relatorio_atual['feedback'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 8px;">Classificação (1-5)</label>
                                    <div class="star-rating" style="margin-bottom: 0;">
                                        <select name="rating" style="width: 100%;">
                                            <?php $rating = $relatorio_atual['classificacao'] ?? 5; ?>
                                            <option value="1" <?php echo $rating == 1 ? 'selected' : ''; ?>>1 - Insuficiente</option>
                                            <option value="2" <?php echo $rating == 2 ? 'selected' : ''; ?>>2 - Fraco</option>
                                            <option value="3" <?php echo $rating == 3 ? 'selected' : ''; ?>>3 - Suficiente</option>
                                            <option value="4" <?php echo $rating == 4 ? 'selected' : ''; ?>>4 - Bom</option>
                                            <option value="5" <?php echo $rating == 5 ? 'selected' : ''; ?>>5 - Excelente</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="action-row">
                                <button type="submit" class="btn-primary">
                                    <i class="fa-solid fa-paper-plane"></i> Enviar Avaliação
                                </button>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #9ca3af;">
                        <i class="fa-solid fa-arrow-left" style="font-size: 32px; margin-bottom: 20px;"></i>
                        <p style="font-size: 18px;">Selecione um relatório à esquerda para visualizar.</p>
                    </div>
                <?php endif; ?>

            </main>
        </div>

    </div>
</body>
</html>
