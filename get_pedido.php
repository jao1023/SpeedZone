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

// Verificar se o código do pedido foi fornecido
if (!isset($_GET['codigo_pedido']) || empty($_GET['codigo_pedido'])) {
    echo json_encode(['success' => false, 'message' => 'Código do pedido não fornecido']);
    exit;
}

$codigo_pedido = $_GET['codigo_pedido'];

try {
    // Buscar informações do pedido finalizado
    $sql_pedido = "SELECT pf.codigo_pedido, pf.total_final, pf.data_pedido, pf.id_usuario,
                          u.primeiro_nome, u.ultimo_nome, u.email,
                          u.logradouro, u.numero, u.complemento, u.bairro, u.cidade, u.estado, u.cep,
                          p.status_pedido
                   FROM pedidos_finalizados pf
                   LEFT JOIN usuario u ON pf.id_usuario = u.id_usuario
                   LEFT JOIN pedidos p ON SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = pf.codigo_pedido
                   WHERE pf.codigo_pedido = ?
                   LIMIT 1";
    
    $stmt = $conn->prepare($sql_pedido);
    $stmt->bind_param("s", $codigo_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit;
    }
    
    $pedido_info = $result->fetch_assoc();
    
    // Montar endereço completo
    $endereco = trim(
        $pedido_info['logradouro'] . ', ' . 
        $pedido_info['numero'] . 
        ($pedido_info['complemento'] ? ' - ' . $pedido_info['complemento'] : '') . 
        ', ' . $pedido_info['bairro'] . ', ' . 
        $pedido_info['cidade'] . ' - ' . 
        $pedido_info['estado'] . ', ' . 
        'CEP: ' . $pedido_info['cep']
    );
    
    // Buscar itens do pedido
    $sql_itens = "SELECT p.cod_pedido, p.cod_produto, pr.nome_produto, 
                         p.quantidade, p.preco_unitario, 
                         (p.quantidade * p.preco_unitario) as subtotal
                  FROM pedidos p
                  LEFT JOIN produtos pr ON p.cod_produto = pr.codigo
                  WHERE SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = ?
                  ORDER BY p.cod_pedido";
    
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bind_param("s", $codigo_pedido);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    
    $itens = array();
    $subtotal_geral = 0;
    
    while ($item = $result_itens->fetch_assoc()) {
        $subtotal_item = $item['quantidade'] * $item['preco_unitario'];
        $subtotal_geral += $subtotal_item;
        
        $itens[] = array(
            'codigo' => $item['cod_produto'],
            'nome' => $item['nome_produto'] ?: 'Produto não encontrado',
            'quantidade' => $item['quantidade'],
            'preco_unitario' => 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.'),
            'subtotal' => 'R$ ' . number_format($subtotal_item, 2, ',', '.')
        );
    }
    
    // Calcular frete (total - subtotal)
    $frete = $pedido_info['total_final'] - $subtotal_geral;
    
    // Montar resposta
    $response = array(
        'success' => true,
        'pedido' => array(
            'id' => '#' . $codigo_pedido,
            'cliente' => trim($pedido_info['primeiro_nome'] . ' ' . $pedido_info['ultimo_nome']),
            'email' => $pedido_info['email'],
            'endereco' => $endereco,
            'status' => $pedido_info['status_pedido'] ?: 'Separação do pedido',
            'itens' => $itens,
            'subtotal' => 'R$ ' . number_format($subtotal_geral, 2, ',', '.'),
            'frete' => 'R$ ' . number_format($frete, 2, ',', '.'),
            'total' => 'R$ ' . number_format($pedido_info['total_final'], 2, ',', '.')
        )
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes do pedido: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
    ]);
}
?>