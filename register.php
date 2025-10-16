<?php
// Arquivo: register.php

// Incluir Conexão
// Assume-se que 'connection.php' define a variável de conexão como $conn
require_once 'connection.php'; 

$error = '';
$success = '';

// Variáveis para repopular o formulário (default empty)
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$email = $_POST['email'] ?? '';
$cpf = $_POST['cpf'] ?? ''; // Valor bruto digitado
$cpf_formatted_for_form = htmlspecialchars($cpf); // Valor que vai para o input em caso de erro
$cpf_final_to_db = null; // Variável que será inserida no DB

/**
 * Função para validar o CPF algorítmicamente.
 * @param string $cpf O CPF como string contendo apenas dígitos.
 * @return bool True se o CPF for válido, False caso contrário.
 */
function validateCPF(string $cpf): bool {
    // Verifica se o CPF tem 11 dígitos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais (inválido por regra do CPF)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Coletar e Limpar Dados do Formulário
    $firstName = trim($firstName);
    $lastName = trim($lastName);
    $email = trim($email);
    $cpf = trim($cpf);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = $_POST['terms'] ?? '';

    // 1. Validação Básica
    if (empty($firstName) || empty($lastName) || empty($email) || empty($cpf) || empty($password) || empty($confirmPassword)) {
        $error = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (empty($terms)) {
        $error = "Você deve concordar com os termos de uso para criar sua conta.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "O formato do email é inválido.";
    } elseif ($password !== $confirmPassword) {
        $error = "As senhas não coincidem.";
    } elseif (strlen($password) < 6) {
        $error = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        
        // 1. Limpa o CPF para obter apenas dígitos e valida o tamanho
        $cpf_clean = preg_replace('/[^0-9]/', '', $cpf);
        
        // Validação adicional de tamanho e formato do CPF limpo
        if (strlen($cpf_clean) != 11) {
            $error = "O CPF deve conter 11 dígitos.";
            // Mantém o valor digitado (mesmo que incompleto) para repopular o campo
            $cpf_formatted_for_form = htmlspecialchars($cpf); 
        } 
        
        // NOVO: Validação algorítmica do CPF
        elseif (!validateCPF($cpf_clean)) {
             $error = "O CPF informado é inválido.";
             $cpf_formatted_for_form = htmlspecialchars($cpf); // Mantém para repopular
        }
        
        // Se passou na validação de formato e algorítmica
        if (empty($error)) {
            // 2. Formata o CPF para o padrão 111.111.111-11
            $cpf_final_to_db = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf_clean);
            
            // Usa o valor formatado tanto para o banco quanto para repopular o formulário
            $cpf_formatted_for_form = $cpf_final_to_db; 

            // 3. Checar se Email ou CPF já existem no DB
            // ESTA É A PARTE QUE JÁ CHECA A EXISTÊNCIA NO BANCO
            $check_sql = "SELECT id_usuario FROM usuario WHERE email = ? OR cpf = ?";
            if ($stmt = $conn->prepare($check_sql)) {
                // AGORA CHECA O CPF JÁ FORMATADO
                $stmt->bind_param("ss", $email, $cpf_final_to_db); 
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "Este email ou CPF já está cadastrado.";
                } else {
                    
                    // 4. Criptografar Senha
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // 5. Inserir Novo Usuário (Cargo padrão: Cliente, Status padrão: Ativo)
                    $insert_sql = "
                        INSERT INTO usuario (
                            primeiro_nome, ultimo_nome, email, cpf, cargo, status_conta, senha
                        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    
                    $default_cargo = 'Cliente';
                    $default_status = 'Ativo';
                    
                    if ($stmt_insert = $conn->prepare($insert_sql)) {
                        $stmt_insert->bind_param(
                            "sssssss", 
                            $firstName, 
                            $lastName, 
                            $email, 
                            $cpf_final_to_db, // Insere o CPF FORMATADO
                            $default_cargo, 
                            $default_status, 
                            $hashed_password
                        );

                        if ($stmt_insert->execute()) {
                            // Redireciona para a página de login após o sucesso
                            header("Location: login.php?registered=success");
                            exit();
                        } else {
                            $error = "Erro ao registrar. Tente novamente: " . $conn->error;
                        }
                        $stmt_insert->close();
                    } else {
                         $error = "Erro interno ao preparar a inserção. Tente novamente.";
                    }
                }
                $stmt->close();
            } else {
                $error = "Erro interno ao preparar a verificação de dados. Tente novamente.";
            }
        }
    }
}
// Fecha a conexão (Importante fechar, mesmo que no final)
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crie sua Conta SpeedZone</title>
    <link rel="stylesheet" href="register.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="intro-section">
            <h1>Crie sua conta SpeedZone</h1>
            <p>Preencha os campos abaixo para começar a acelerar conosco!</p>
        </div>
        
        <div class="form-section">
            
            <?php if (!empty($error)): ?>
                <p style="color: #a94442; padding: 10px; border: 1px solid #ebccd1; background-color: #f2dede; border-radius: 4px; margin-bottom: 15px;"><?= $error ?></p>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="firstName">Primeiro nome</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Ex: João" value="<?= htmlspecialchars($firstName) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="lastName">Último nome</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Ex: Silva" value="<?= htmlspecialchars($lastName) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: joao.silva@email.com" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" value="<?= $cpf_formatted_for_form ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirme a senha</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="********" required>
                </div>
                
                <div class="terms-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">Concordo com os <a href="termos.php">termos de uso</a></label>
                </div>
                
                <button type="submit" class="register-btn">Criar Conta</button>
            </form>
            
            <p class="login-link">Já tem uma conta? <a href="login.php">Faça Login</a></p>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cpfInput = document.getElementById('cpf');

            // Função para formatar o CPF enquanto digita
            cpfInput.addEventListener('input', (e) => {
                let value = e.target.value;
                
                // Remove tudo que não for dígito
                value = value.replace(/\D/g, ''); 

                // Limita a 11 dígitos
                value = value.substring(0, 11);

                // Aplica a máscara: 000.000.000-00
                if (value.length > 9) {
                    value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/^(\d{3})(\d{3})(\d{3})$/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/^(\d{3})(\d{3})$/, '$1.$2');
                } else if (value.length > 0) {
                    value = value.replace(/^(\d{3})$/, '$1');
                }
                
                e.target.value = value;
            });
        });
    </script>
</body>
</html>