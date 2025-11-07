<?php require_once __DIR__ . '/session.php'; ?>
<?php

$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
    // Redireciona para a página principal se não tiver permissão
    header("Location: index.php?error=access_denied");
    exit;
}
require_once 'connection.php';

// Variáveis para mensagens de feedback
$feedbackMessage = '';
$feedbackClass = '';

// Verifica se a conexão $conn é válida antes de usá-la
if (!isset($conn) || $conn->connect_error) {
    die("Erro de Conexão com o Banco de Dados: Verifique 'connection.php'.");
}

// =======================================================
// 2. LÓGICA DE FEEDBACK (Mensagens após operações GET)
// Pega mensagens de sucesso/erro após UPDATE, DELETE ou CREATE
// =======================================================

if (isset($_GET['sucesso'])) {
    $id = (int)($_GET['id'] ?? 0);
    $feedbackClass = 'success';

    switch ($_GET['sucesso']) {
        case 'edicao':
            $feedbackMessage = "Sucesso: Cupom ID **{$id}** atualizado com sucesso!";
            break;
        case 'exclusao':
            $feedbackMessage = "Sucesso: Cupom ID **{$id}** excluído permanentemente.";
            break;
        case 'criacao':
            $feedbackMessage = "Sucesso: Novo cupom criado e salvo no banco de dados.";
            break;
    }
} elseif (isset($_GET['erro'])) {
    $erro = urldecode($_GET['erro']);
    $feedbackClass = 'error';

    switch ($erro) {
        case 'metodo_invalido':
            $feedbackMessage = "Erro: Requisição inválida.";
            break;
        case 'dados_faltando':
            $feedbackMessage = "Erro na Edição/Criação: Dados essenciais não foram fornecidos.";
            break;
        case 'cupom_nao_existe':
            $id = (int)($_GET['id'] ?? 'N/A');
            $feedbackMessage = "Erro na Exclusão: O cupom ID **{$id}** não foi encontrado.";
            break;
        default:
            $feedbackMessage = "Erro de Operação: " . htmlspecialchars($erro);
            break;
    }
}

// =======================================================
// 3. LÓGICA DE INSERÇÃO (PROCESSAMENTO DO FORMULÁRIO POST)
// Assume que este formulário POST vem de "novo_cupom.php" se ele existir
// Se o formulário de novo cupom estiver nesta página, esta lógica é executada.
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta e Sanitização dos dados
    $codigo = trim($_POST['codigo'] ?? '');
    $tipo_desconto = $_POST['tipo_desconto'] ?? '';
    $valor_desconto = $_POST['valor_desconto'] ?? 0.00;
    $data_expiracao = trim($_POST['data_expiracao'] ?? null);
    $limite_usos = (int) ($_POST['limite_usos'] ?? 0);

    // 2. Validação básica (garante que campos essenciais foram enviados)
    if (empty($codigo) || empty($tipo_desconto) || empty($data_expiracao)) {
        $feedbackMessage = "Erro: Campos obrigatórios (Código, Tipo, Data de Expiração) devem ser preenchidos.";
        $feedbackClass = 'error';
    } else {
        // Normaliza o tipo e valor:
        $tipo = ($tipo_desconto === 'frete') ? 'fixo' : $tipo_desconto;
        $valor = ($tipo_desconto === 'frete') ? 0.00 : (float) $valor_desconto;

        // Define uso_maximo para NULL se for 0, senão usa o limite.
        $uso_maximo_db = ($limite_usos > 0) ? $limite_usos : NULL;

        // Prepara a query de INSERT
        $stmt = $conn->prepare("INSERT INTO cupons (codigo, tipo, valor, data_expiracao, uso_maximo, usos_atuais, status) VALUES (?, ?, ?, ?, ?, 0, 'Ativo')");

        if ($stmt) {
            // "ssdsi" -> string, string, double, string (date), integer
            $stmt->bind_param("ssdsi", $codigo, $tipo, $valor, $data_expiracao, $uso_maximo_db);

            try {
                if ($stmt->execute()) {
                    $desconto_display = formatarDesconto($tipo, $valor);
                    $feedbackMessage = "Sucesso: Cupom **{$codigo}** ({$desconto_display}) criado com sucesso!";
                    $feedbackClass = 'success';
                } else {
                    // Erro de Chave Única (Código do cupom duplicado)
                    if ($conn->errno == 1062) {
                        $feedbackMessage = "Erro: O código do cupom **{$codigo}** já existe. Escolha outro código.";
                    } else {
                        $feedbackMessage = "Erro ao inserir no banco de dados: " . $stmt->error;
                    }
                    $feedbackClass = 'error';
                }
            } catch (Exception $e) {
                $feedbackMessage = "Erro inesperado: " . $e->getMessage();
                $feedbackClass = 'error';
            }
            $stmt->close();
        } else {
            $feedbackMessage = "Erro ao preparar a declaração SQL: " . $conn->error;
            $feedbackClass = 'error';
        }
    }
}
// =======================================================
// FIM DA LÓGICA DE INSERÇÃO E FEEDBACK
// =======================================================


// =======================================================
// 4. LÓGICA DE PESQUISA E CONSULTA
// =======================================================
$searchTerm = '';
$whereClause = '';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    // Captura e sanitiza o termo de pesquisa usando real_escape_string (MySQLi)
    $searchTerm = $conn->real_escape_string(trim($_GET['search']));

    // Constrói a cláusula WHERE para pesquisar no código do cupom
    $whereClause = " WHERE codigo LIKE '%$searchTerm%' ";
}


// Consulta SQL Dinâmica
$sql = "SELECT id_cupom, codigo, tipo, valor, data_expiracao, status, uso_maximo, usos_atuais FROM cupons"
    . $whereClause .
    " ORDER BY id_cupom ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

function formatarDesconto($tipo, $valor)
{
    if ($tipo === 'percentual') {
        return number_format($valor, 0, ',', '.') . '% OFF';
    } elseif ($tipo === 'fixo') {
        // Trata o caso de Frete Grátis (valor 0 e tipo 'fixo')
        return ($valor == 0.00) ? 'Frete Grátis' : 'R$ ' . number_format($valor, 2, ',', '.');
    }
    return 'N/A';
}


function getClassStatus($status, $data_expiracao, $uso_maximo, $usos_atuais)
{
    $hoje = new DateTime();

    // 1. Verificação de Expiração
    if ($data_expiracao && (new DateTime($data_expiracao) < $hoje)) {
        return 'inactive expired';
    }

    // 2. Verificação de Limite de Uso
    if ($uso_maximo !== null && $uso_maximo > 0 && $usos_atuais >= $uso_maximo) {
        return 'inactive limit-reached';
    }

    // 3. Verificação de Status Manual
    if ($status === 'Ativo') {
        return 'active';
    }
    return 'inactive';
}

// Fecha a conexão após a consulta (antes de iniciar o HTML)
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Gerenciamento de Cupons</title>

    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        .product-grid-template {
            display: grid;
            grid-template-columns: 50px 1.5fr 1fr 1fr 1fr 1fr 100px;
            gap: 15px;
            padding: 10px 0;
            align-items: center;
        }

        .table-header.product-grid-template {
            font-weight: 700;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
        }

        .status.active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status.expired {
            background-color: #fcebeb;
            color: #cc0000;
            border: 1px solid #cc0000;
        }

        .status.limit-reached {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-container {
            position: relative;
            display: flex;
            max-width: 450px;
            width: 100%;
        }

        .search-input {
            flex: 1;
            padding: 10px 40px 10px 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }

        .search-icon-btn {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            padding: 0 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-icon-btn:hover {
            color: #28a745;
        }
    </style>
</head>

<body>
    <div class="admin-layout">

        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-tags"></i>
                <h2>Painel Admin</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="admin.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="produtos.php" class="nav-item"><i class="fas fa-box-open"></i> Produtos</a>
                <a href="vendas.php" class="nav-item"><i class="fas fa-chart-line"></i> Vendas</a>
                <a href="usuarios.php" class="nav-item"><i class="fas fa-users"></i> Usuários</a>
                <a href="suporteAdmin.php" class="nav-item"><i class="fas fa-headset"></i> Suporte</a>

                <a href="#" class="nav-item active"><i class="fas fa-tags"></i> Cupons</a>
            </nav>

            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Gerenciamento de Cupons de Desconto</h1>

            <?php if ($feedbackMessage): ?>
                <div class="alert <?= $feedbackClass ?>" role="alert">
                    <?= $feedbackMessage ?>
                </div>
            <?php endif; ?>

            <section id="cupons-section">

                <div class="action-bar">
                    <a href="novo_cupom.php" class="add-btn" style="text-decoration: none;">
                        <i class="fas fa-plus"></i> Novo Cupom
                    </a>

                    <form method="GET" action="cupons.php" class="search-form" style="display: flex;">
                        <div class="search-container">
                            <input type="text" name="search" placeholder="Buscar por Código do Cupom..." class="search-input" value="<?= htmlspecialchars($searchTerm) ?>">

                            <button type="submit" class="search-icon-btn" title="Pesquisar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>                    
                    </form>
                </div>

                <div class="data-table">
                    <div class="table-header product-grid-template">
                        <span>ID</span>
                        <span>Código do Cupom</span>
                        <span>Desconto</span>
                        <span>Expira Em</span>
                        <span>Usos</span>
                        <span>Status</span>
                        <span>Ações</span>
                    </div>

                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($cupom = $result->fetch_assoc()):

                            $id_cupom = htmlspecialchars($cupom['id_cupom']);
                            $codigo = htmlspecialchars($cupom['codigo']);
                            $data_expiracao = $cupom['data_expiracao'];

                            // 4. Aplica as funções de formatação e status
                            $desconto_formatado = formatarDesconto($cupom['tipo'], $cupom['valor']);
                            $status_class = getClassStatus($cupom['status'], $data_expiracao, $cupom['uso_maximo'], $cupom['usos_atuais']);

                            // Determina o texto do status para exibição
                            $status_texto = ucfirst($cupom['status']);
                            if (strpos($status_class, 'expired') !== false) {
                                $status_texto = 'Expirado';
                            } elseif (strpos($status_class, 'limit-reached') !== false) {
                                $status_texto = 'Limite Atingido';
                            }
                        ?>
                            <div class="table-row product-grid-template">
                                <span class="cell-data" data-label="ID"><?php echo $id_cupom; ?></span>
                                <span class="cell-data" data-label="Código do Cupom"><?php echo $codigo; ?></span>
                                <span class="cell-data" data-label="Desconto"><?php echo $desconto_formatado; ?></span>

                                <span class="cell-data" data-label="Expira Em">
                                    <?php
                                    // Formata a data de expiração para o formato brasileiro
                                    echo $data_expiracao
                                        ? (new DateTime($data_expiracao))->format('d/m/Y')
                                        : 'Nunca';
                                    ?>
                                </span>

                                <span class="cell-data" data-label="Usos">
                                    <?php
                                    echo htmlspecialchars($cupom['usos_atuais']);
                                    if ($cupom['uso_maximo'] !== null) {
                                        echo ' / ' . htmlspecialchars($cupom['uso_maximo']);
                                    } else {
                                        echo ' / ∞'; // Símbolo de infinito
                                    }
                                    ?>
                                </span>

                                <span class="cell-data status <?php echo $status_class; ?>" data-label="Status">
                                    <?php echo htmlspecialchars($status_texto); ?>
                                </span>

                                <span class="actions" data-label="Ações">
                                    <a href="editar_cupom.php?id=<?php echo $id_cupom; ?>" class="action-btn edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="action-btn delete" title="Excluir" onclick="confirmarExclusao('<?php echo $id_cupom; ?>', '<?php echo $codigo; ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="table-row">
                            <span class="cell-data" style="grid-column: 1 / -1; text-align: center; padding: 15px;">
                                Nenhum cupom encontrado.
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        function confirmarExclusao(id, nome) {
            if (confirm("Tem certeza que deseja excluir o cupom " + nome + " (ID: " + id + ")? Esta ação é irreversível.")) {
                // Redireciona para o script de exclusão
                window.location.href = 'delete_cupom.php?id=' + id;
            }
        }
    </script>
</body>

</html>