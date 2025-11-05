<?php
require_once __DIR__ . '/session.php';
require_once 'connection.php';

// Verificar permissões
$user_cargo = $_SESSION['cargo'] ?? 'Cliente';
if (!in_array($user_cargo, ['Funcionario', 'Administrador'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Configurar cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar dados recebidos
if (!isset($data['codigo_pedido']) || !isset($data['novo_status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$codigo_pedido = $data['codigo_pedido'];
$novo_status = $data['novo_status'];

// Validar status permitidos
$status_permitidos = ['Separação do pedido', 'Em Transporte', 'Entregue'];
if (!in_array($novo_status, $status_permitidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

try {
    // Iniciar transação
    $conn->begin_transaction();
    
    // Atualizar todos os pedidos relacionados ao código base
    $sql = "UPDATE pedidos 
            SET status_pedido = ? 
            WHERE SUBSTRING(cod_pedido, 1, LOCATE('-', cod_pedido, LOCATE('-', cod_pedido) + 1) - 1) = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar statement: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $novo_status, $codigo_pedido);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar query: " . $stmt->error);
    }
    
    $linhas_afetadas = $stmt->affected_rows;
    
    if ($linhas_afetadas === 0) {
        throw new Exception("Nenhum pedido encontrado com o código: " . $codigo_pedido);
    }
    
    // Commit da transação
    $conn->commit();
    
    // Retornar sucesso
    echo json_encode([
        'success' => true, 
        'message' => 'Status atualizado com sucesso',
        'linhas_afetadas' => $linhas_afetadas,
        'novo_status' => $novo_status
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    
    error_log("Erro ao atualizar status do pedido: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>