<?php
// Arquivo: login.php

// 1. INICIA A SESSÃO
session_start();

// Incluir Conexão
// Assume-se que 'connection.php' define a variável de conexão como $conn
require_once 'connection.php';

$error = '';
$success = '';
$email_input = ''; // Para repopular o campo email

// Verifica se há uma mensagem de sucesso do registro
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success = "Conta criada com sucesso! Faça login para continuar.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Coletar e Limpar Dados do Formulário
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $email_input = htmlspecialchars($email); // Repopula o campo em caso de erro

    // 2. Validação e Busca no Banco
    if (empty($email) || empty($password)) {
        $error = "Por favor, preencha todos os campos.";
    } else {

        $sql = "SELECT id_usuario, primeiro_nome, senha, cargo, status_conta FROM usuario WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // 3. Verificação de Senha e Status
                // Checa a senha usando o hash
                if (password_verify($password, $user['senha'])) {

                    if ($user['status_conta'] !== 'Ativo') {
                        $error = "Sua conta está inativa ou bloqueada. Contate o suporte.";
                    } else {
                        // 4. Login Bem-sucedido: Armazena dados na sessão
                        $_SESSION['loggedin'] = TRUE;
                        $_SESSION['user_id'] = $user['id_usuario'];
                        $_SESSION['primeiro_nome'] = $user['primeiro_nome'];
                        $_SESSION['cargo'] = $user['cargo'];

                        // 5. Redirecionamento baseado no cargo
                        if ($user['cargo'] === 'Administrador' || $user['cargo'] === 'Funcionario') {
                            header("Location: admin.php"); // Redireciona para o painel admin
                        } else {
                            header("Location: cliente_dashboard.php"); // Redireciona para o painel do cliente
                        }
                        exit();
                    }
                } else {
                    // Senha incorreta
                    $error = "Email ou senha incorretos.";
                }
            } else {
                // Email não encontrado
                $error = "Email ou senha incorretos.";
            }
            $stmt->close();
        } else {
            $error = "Erro interno ao processar a requisição.";
        }
    }
}
// Fecha a conexão
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faça Login SpeedZone</title>
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="intro-section">
            <h1>Faça Login</h1>
            <p>Acesse sua conta para continuar a acelerar!</p>
        </div>

        <div class="form-section">

            <?php if (!empty($success)): ?>
                <p style="color: #155724; padding: 10px; border: 1px solid #c3e6cb; background-color: #d4edda; border-radius: 4px; margin-bottom: 15px;"><?= $success ?></p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p style="color: #a94442; padding: 10px; border: 1px solid #ebccd1; background-color: #f2dede; border-radius: 4px; margin-bottom: 15px;"><?= $error ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Seu email" value="<?= $email_input ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>

                <button type="submit" class="register-btn">Entrar</button>
            </form>

            <p class="login-link">Não tem uma conta? <a href="register.php">Registre-se</a></p>
        </div>
    </div>
</body>

</html>