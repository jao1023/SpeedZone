<?php
// Arquivo: config.php
session_start();

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: login.php");
    exit;
}

require_once 'connection.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$user_data = [];


// --- PROCESSAMENTO DE ATUALIZAÇÃO DE ENDEREÇO ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Coletar e limpar dados
    $cep = trim($_POST['cep'] ?? '');
    $rua = trim($_POST['address'] ?? '');
    $numero = trim($_POST['number'] ?? '');
    $complemento = trim($_POST['complement'] ?? '');
    $bairro = trim($_POST['neighborhood'] ?? '');
    $cidade = trim($_POST['city'] ?? '');
    $estado = trim($_POST['state'] ?? '');

    $cep_clean = preg_replace('/\D/', '', $cep);
    $numero_clean = preg_replace('/\D/', '', $numero);
    $numero_int = (int)$numero_clean;


    

    // Validação
    if (strlen($cep_clean) !== 8) {
        $error = "Por favor, insira um CEP válido com 8 dígitos.";
    } elseif (empty($rua) || empty($numero_clean)) {
        $error = "Rua e número são obrigatórios.";
    } elseif (strlen($estado) !== 2) {
        $error = "Estado deve conter exatamente 2 letras.";
    } else {
        // Atualizar endereço
        $update_sql = "
            UPDATE usuario 
            SET cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ? 
            WHERE id_usuario = ?";

        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param(
                "ssissssi",
                $cep_clean,
                $rua,
                $numero_int,
                $complemento,
                $bairro,
                $cidade,
                $estado,
                $user_id
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Endereço atualizado com sucesso!";
                } else {
                    $success = "Nenhuma alteração detectada.";
                }
            } else {
                $error = "Erro ao atualizar o endereço: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error = "Erro interno ao preparar a consulta.";
        }
    }
}

// --- CARREGAMENTO DOS DADOS DO USUÁRIO ---
$sql_select = "SELECT * FROM usuario WHERE id_usuario = ?";
if ($stmt_select = $conn->prepare($sql_select)) {
    $stmt_select->bind_param("i", $user_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();

        // Formatar CPF
        $cpf_raw = preg_replace('/\D/', '', $user_data['cpf']);
        $cpf_display = (strlen($cpf_raw) === 11) ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf_raw) : $user_data['cpf'];

        // Formatar CEP
        $cep_raw = preg_replace('/\D/', '', $user_data['cep'] ?? '');
        $cep_display = (strlen($cep_raw) === 8) ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep_raw) : $user_data['cep'];
    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }

    $stmt_select->close();
} else {
    $error = "Erro ao carregar os dados do usuário.";
}

$conn->close();
?>

<!-- HTML começa aqui -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações da Conta - SpeedZone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="config.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <a href="index.php" class="back-btn">&lt; Voltar</a>

    <div class="container">
        <h1 class="page-title">Configurações da Conta</h1>

        <?php if (!empty($success)): ?>
            <p style="color: green; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px;"><?= $success ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: red; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px;"><?= $error ?></p>
        <?php endif; ?>

        <section class="config-section">
            <h2 class="section-title">Informações da Conta</h2>
            <form>
                <div class="form-group">
                    <label for="firstName">Primeiro nome</label>
                    <input type="text" id="firstName" value="<?= htmlspecialchars($user_data['primeiro_nome'] ?? '') ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="lastName">Último nome</label>
                    <input type="text" id="lastName" value="<?= htmlspecialchars($user_data['ultimo_nome'] ?? '') ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" value="<?= htmlspecialchars($cpf_display ?? '') ?>" disabled>
                </div>
            </form>
        </section>

        <section class="config-section">
            <h2 class="section-title">Endereço de Entrega</h2>
            <form action="config.php" method="POST">
                <div class="form-group">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000" value="<?= htmlspecialchars($cep_display ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Rua/Avenida</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($user_data['rua'] ?? '') ?>" required>
                </div>
                <div class="form-group-half">
                    <div class="form-group">
                        <label for="number">Número</label>
                        <input type="text" id="number" name="number" value="<?= htmlspecialchars($user_data['numero'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="complement">Complemento</label>
                        <input type="text" id="complement" name="complement" value="<?= htmlspecialchars($user_data['complemento'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="neighborhood">Bairro</label>
                    <input type="text" id="neighborhood" name="neighborhood" value="<?= htmlspecialchars($user_data['bairro'] ?? '') ?>" required>
                </div>
                <div class="form-group-half">
                    <div class="form-group">
                        <label for="city">Cidade</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($user_data['cidade'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="state">Estado</label>
                        <input type="text" id="state" name="state" maxlength="2" value="<?= htmlspecialchars($user_data['estado'] ?? '') ?>" required>
                    </div>
                </div>
                <button type="submit" class="save-btn">Salvar Endereço</button>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cepInput = document.getElementById('cep');
            const addressInput = document.getElementById('address');
            const neighborhoodInput = document.getElementById('neighborhood');
            const cityInput = document.getElementById('city');
            const stateInput = document.getElementById('state');

            // Máscara de CEP
            cepInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '').slice(0, 8);
                if (value.length > 5) {
                    value = value.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
                }
                e.target.value = value;
            });

            // Auto preenchimento via ViaCEP
            cepInput.addEventListener('blur', () => {
                const cep = cepInput.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch(`https://viacep.com.br/ws/${cep}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                addressInput.value = data.logradouro;
                                neighborhoodInput.value = data.bairro;
                                cityInput.value = data.localidade;
                                stateInput.value = data.uf;
                                document.getElementById('number').focus();
                            } else {
                                alert("CEP não encontrado. Preencha manualmente.");
                            }
                        })
                        .catch(() => {
                            alert("Erro ao buscar CEP.");
                        });
                }
            });
        });
    </script>
</body>
</html>
