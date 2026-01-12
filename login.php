<?php
session_start();
require_once 'conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Buscar utilizador na base de dados
        $stmt = $pdo->prepare("SELECT * FROM utilizadores WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $utilizador = $stmt->fetch();

        // Verificar se utilizador existe e a senha está correta
        // NOTA: Num sistema real, deve usar password_verify($senha, $utilizador['senha'])
        // Como estamos a usar texto simples para facilitar (conforme pedido), comparamos diretamente
        if ($utilizador && $utilizador['senha'] === $senha) {
            // Login com sucesso
            $_SESSION['user_id'] = $utilizador['id'];
            $_SESSION['user_name'] = $utilizador['nome'];
            $_SESSION['user_role'] = $utilizador['tipo_utilizador'];
            $_SESSION['user_avatar'] = $utilizador['avatar_url'];

            // Redirecionar baseado no tipo de utilizador
            if ($utilizador['tipo_utilizador'] === 'admin') {
                header("Location: dashboard-admin.php");
            } elseif ($utilizador['tipo_utilizador'] === 'professor') {
                header("Location: dashboard-professor.php");
            } else {
                header("Location: dashboard-aluno.php");
            }
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InternFLOW</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="main-container">
        <div class="night-mode-toggle-login">
            <i class="fa-solid fa-moon" id="login-night-mode-icon"></i>
        </div>
        <div class="login-card">
            <div class="form-side">
                <div class="logo">
                    <a href="login.php" style="text-decoration: none; display: flex; align-items: center; gap: 12px; color: inherit;">
                        <div class="logo-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                        <span class="logo-text">InternFLOW</span>
                    </a>
                </div>
                <div class="header-text">
                    <h1>Bem-vindo ao InternFLOW!</h1>
                    <p>Aceda à sua conta para gerir os seus estágios.</p>
                </div>
                
                <?php if ($erro): ?>
                    <div style="color: #ef4444; background-color: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="input-wrapper">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="email@exemplo.pt" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>
                    </div>
                    <div class="input-wrapper">
                        <label for="password">Palavra-passe</label>
                        <div class="input-group">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="*********" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Entrar</button>
                </form>
                <div class="form-footer">
                    <p>O registo de novos alunos é feito exclusivamente pelos professores.</p>
                </div>
            </div>
            <div class="image-side">
                <img src="images/estudante.png" alt="Estudante">
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
