<?php require_once __DIR__ . '/session.php'; ?>
<?php

$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
    header("Location: index.php?error=access_denied");
    exit;
}
require_once 'connection.php';

$searchTerm = '';
$whereClause = '';


$conn = $conn;

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchTerm = $conn->real_escape_string(trim($_GET['search']));

    $whereClause = " WHERE primeiro_nome LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%' ";
}

$sql = "SELECT id_usuario, primeiro_nome, email, cargo, status_conta, CREATED_AT FROM usuario"
    . $whereClause .
    " ORDER BY id_usuario ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Painel Admin SpeedZone</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .action-bar.search-form {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 15px 0;
        }

        .search-container {
            flex-grow: 1;
            position: relative;
            display: flex;
            max-width: 450px;
        }

        .search-input {
            flex-grow: 1;
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
            background: none;
            border: none;
            cursor: pointer;
            padding: 0 10px;
            color: #555;
            transition: color 0.2s;
        }

        .search-icon-btn:hover {
            color: #28a745;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>

<body>
    <div class="admin-layout">

        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-tachometer-alt"></i>
                <h2>Painel Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="admin.php" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="produtos.php" class="nav-item"><i class="fas fa-box-open"></i> Produtos</a>
                <a href="vendas.php" class="nav-item"><i class="fas fa-chart-line"></i> Vendas</a>
                <a href="usuarios.php" class="nav-item sidebar-link active" data-target="usuarios-section"><i class="fas fa-users"></i> Usuários</a>
                <a href="suporteAdmin.php" class="nav-item sidebar-link" data-target="suporte-section"><i class="fas fa-headset"></i> Suporte</a>
                <a href="Cupons.php" class="nav-item"><i class="fas fa-tags"></i> Cupons</a>
            </nav>
            <div class="logout-section">
                <a href="index.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </div>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Gerenciamento de Usuários</h1>

            <section id="usuarios" class="management-section active">

                <div id="user-list-view" class="sub-section active">

                    <form method="GET" action="usuarios.php" class="action-bar search-form">

                        <div class="search-container">
                            <input type="text" name="search" placeholder="Buscar por Nome ou Email..." class="search-input" value="<?= htmlspecialchars($searchTerm) ?>">

                            <button type="submit" class="search-icon-btn" title="Pesquisar">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <div class="data-table">
                        <div class="table-header users-grid-template">
                            <span>ID</span>
                            <span>Nome</span>
                            <span>Email</span>
                            <span>Cargo</span>
                            <span>Status</span>
                            <span>Ações</span>
                        </div>

                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $user_id = $row['id_usuario'];
                                $user_name = htmlspecialchars($row['primeiro_nome']);
                                $user_email = htmlspecialchars($row['email']);
                                $user_cargo = htmlspecialchars($row['cargo']);
                                $user_status = htmlspecialchars($row['status_conta']);

                                $status_class = (strpos($user_status, 'Ativo') !== false) ? 'active' : 'inactive';
                                $cargo_class = strtolower(str_replace([' ', '/'], '', $user_cargo));

                                echo "
                                <div class='table-row users-grid-template' data-user-id='{$user_id}'>
                                    <span class='cell-data'>{$user_id}</span>
                                    <span class='cell-data' data-label='Nome:'>{$user_name}</span>
                                    <span class='cell-data' data-label='Email:'>{$user_email}</span>
                                    <span class='cell-data {$cargo_class} status' data-label='Cargo:'>{$user_cargo}</span>
                                    <span class='cell-data status {$status_class}' data-label='Status:'>{$user_status}</span>
                                    <span class='cell-data actions'>
                                        <a class='action-btn edit edit-user-btn' title='Editar Usuário' href='edit_usuario.php?id={$user_id}'><i class='fas fa-edit'></i></a>
                                        <a class='action-btn delete delete-user-btn' title='Excluir Usuário' href='delete_usuario.php?id={$user_id}'><i class='fas fa-trash-alt'></i></a>
                                    </span>
                                </div>
                                ";
                            }
                        } else {
                            echo "<div class='table-row'><span class='cell-data' style='grid-column: 1 / -1; text-align: center; padding: 20px;'>Nenhum usuário encontrado, filtre por nome ou email do usuario!!</span></div>";
                        }
                        ?>
                    </div>
                </div>

                <div id="user-form-view" class="sub-section form-view">
                    <a href="#" class="back-link" id="hide-user-form-link"><i class="fas fa-arrow-left"></i> Voltar para Lista</a>
                    <h2 class="section-heading" id="user-form-title">Adicionar Novo Usuário</h2>

                    <div class="form-container">
                        <form action="edit_users.php" method="POST" class="user-form">
                            <form action="#" method="POST" class="user-form">

                                <input type="hidden" id="user-id" name="id" value="">

                                <fieldset>
                                    <legend>Dados Pessoais e Contato</legend>
                                    <div class="form-grid">
                                        <div class="form-group"><label for="nome">Nome Completo *</label><input type="text" id="user-nome" name="nome" required></div>
                                        <div class="form-group"><label for="email">Email *</label><input type="email" id="user-email" name="email" required></div>
                                    </div>
                                    <div class="form-grid">
                                        <div class="form-group"><label for="registro">Data de Registro</label><input type="text" id="user-registro" name="registro" disabled value="<?= date('d/m/Y'); ?>"></div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <legend>Cargo e Permissões</legend>
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="cargo">Cargo/Nível de Acesso *</label>
                                            <select id="user-cargo" name="cargo" required>
                                                <option value="Cliente">Cliente (Padrão)</option>
                                                <option value="Funcionario">Funcionário</option>
                                                <option value="Administrador">Administrador (Acesso Total)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="status">Status da Conta</label>
                                            <select id="user-status" name="status">
                                                <option value="Ativo">Ativo</option>
                                                <option value="Bloqueado">Bloqueado</option>
                                                <option value="Desativado">Desativado</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>

                                <button type="submit" class="submit-btn" id="submit-user-btn">Salvar Usuário</button>
                            </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="usuarios.js"></script>
</body>

</html>