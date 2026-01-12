<?php
session_start();
require_once 'conexao.php';

// Configurações de Erro e JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// 1. Autenticação
if (!isset($_SESSION['user_id'])) {
    if (isset($_GET['api'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit;
    }
    header("Location: login.php");
    exit;
}

$meu_id = $_SESSION['user_id'];
$meu_role = $_SESSION['user_role'];
$user_name = $_SESSION['user_name'];
$user_avatar = $_SESSION['user_avatar'] ?? "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=111827&color=fff";

// Bloquear Admin
if ($meu_role === 'admin') {
    header("Location: dashboard-admin.php");
    exit;
}

// ---------------------------------------------------------
// 2. API AJAX (Retorna JSON)
// ---------------------------------------------------------
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $action = $_GET['api'];

    try {
        // A: Listar Contatos
        if ($action === 'get_contacts') {
            $sql = "";
            if ($meu_role === 'professor') {
                $sql = "SELECT id, nome, avatar_url FROM utilizadores WHERE tipo_utilizador = 'aluno' ORDER BY nome ASC";
            } else {
                $sql = "SELECT id, nome, avatar_url FROM utilizadores WHERE tipo_utilizador = 'professor' ORDER BY nome ASC";
            }
            
            $stmt = $pdo->query($sql);
            $contatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adicionar avatares padrão se nulo
            foreach ($contatos as &$c) {
                if (empty($c['avatar_url'])) {
                    $c['avatar_url'] = "https://ui-avatars.com/api/?name=" . urlencode($c['nome']);
                }
            }
            echo json_encode($contatos);
            exit;
        }

        // B: Obter Mensagens
        if ($action === 'get_messages') {
            $other_id = $_GET['user_id'] ?? 0;
            
            if (!$other_id) { echo json_encode([]); exit; }

            // Marcar como lidas
            $upd = $pdo->prepare("UPDATE mensagens SET lida = 1 WHERE destinatario_id = ? AND remetente_id = ?");
            $upd->execute([$meu_id, $other_id]);

            // Buscar conversa
            $stmt = $pdo->prepare("
                SELECT id, remetente_id, conteudo, DATE_FORMAT(enviada_em, '%H:%i') as hora 
                FROM mensagens 
                WHERE (remetente_id = :eu AND destinatario_id = :ele) 
                   OR (remetente_id = :ele AND destinatario_id = :eu) 
                ORDER BY enviada_em ASC
            ");
            $stmt->execute(['eu' => $meu_id, 'ele' => $other_id]);
            $msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($msgs);
            exit;
        }

        // C: Enviar Mensagem
        if ($action === 'send_message') {
            // Ler input JSON raw
            $input = json_decode(file_get_contents('php://input'), true);
            
            $destinatario_id = $input['destinatario_id'] ?? 0;
            $conteudo = trim($input['conteudo'] ?? '');

            if (!$destinatario_id || empty($conteudo)) {
                throw new Exception("Dados inválidos");
            }

            $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, conteudo, enviada_em, lida) VALUES (?, ?, ?, NOW(), 0)");
            $stmt->execute([$meu_id, $destinatario_id, $conteudo]);

            echo json_encode(['status' => 'success']);
            exit;
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// ---------------------------------------------------------
// 3. Interface HTML (View)
// ---------------------------------------------------------
$dashboard_link = ($meu_role === 'aluno') ? 'dashboard-aluno.php' : 'dashboard-professor.php';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - InternFLOW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ($meu_role === 'professor'): ?>
    <link rel="stylesheet" href="css/professor.css">
    <?php else: ?>
    <link rel="stylesheet" href="css/aluno.css">
    <?php endif; ?>
    <link rel="stylesheet" href="css/chat.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Navbar Padrão -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?php echo $dashboard_link; ?>" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                <div class="logo-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <span class="logo-text">InternFLOW</span>
            </a>
        </div>
        
        <div class="nav-center-links">
            <a href="<?php echo $dashboard_link; ?>">Painel</a>
            <?php if ($meu_role === 'professor'): ?>
                <a href="estagios.php">Gerir Estágios</a>
                <a href="relatorio.php">Relatórios</a>
            <?php endif; ?>
            <a href="chat.php" class="active-purple">Chat</a>
        </div>
        
        <div class="nav-actions">
            <a href="logout.php" class="action-icon" title="Sair"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
            <!-- <i class="fa-regular fa-bell action-icon"></i> -->
            <i class="fa-solid fa-gear action-icon"></i>
            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar" class="user-avatar">
        </div>
    </nav>

    <div class="main-content">
        <div class="chat-layout-container">
            
            <!-- Sidebar: Lista de Contactos -->
            <aside class="chat-sidebar">
                <h2 class="sidebar-title">Contactos</h2>
                <div class="conversation-list" id="contacts-list">
                    <!-- Carregado via JS -->
                    <div class="loading-text">A carregar...</div>
                </div>
            </aside>

            <!-- Janela Principal -->
            <main class="chat-window-area" id="chat-window">
                
                <!-- Estado Vazio (Sem seleção) -->
                <div id="empty-state" class="empty-state-container">
                    <i class="fa-regular fa-comments empty-state-icon"></i>
                    <p>Selecione um contacto para conversar</p>
                </div>

                <!-- Conteúdo do Chat (Oculto inicialmente) -->
                <div id="active-chat-content" class="active-chat-content">
                    
                    <div class="chat-header">
                        <img id="header-avatar" src="" alt="Avatar">
                        <h3 id="header-name">Nome do Contacto</h3>
                    </div>

                    <div class="chat-messages" id="messages-container">
                        <!-- Mensagens aqui -->
                    </div>

                    <div class="chat-input-wrapper">
                        <input type="text" id="message-input" placeholder="Escreva a sua mensagem..." autocomplete="off">
                        <button id="send-btn" class="btn-send"><i class="fa-regular fa-paper-plane"></i></button>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script>
        const myId = <?php echo $meu_id; ?>;
    </script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
