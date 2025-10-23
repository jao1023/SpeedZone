<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

$codigo_pedido = $_GET['codigo_pedido'] ?? '';

if (empty($codigo_pedido)) {
    echo json_encode(['success' => false, 'message' => 'Código do pedido não informado']);
    exit;
}

try {
    // Buscar informações do pedido
    $sql_pedido = "SELECT pf.codigo_pedido, p.status_pedido, pf.total_final, pf.data_pedido, pf.frete, pf.total_produtos,
                          u.primeiro_nome, u.ultimo_nome, u.email, u.cep, u.rua, u.numero, u.complemento, u.bairro, u.cidade, u.estado
                   FROM pedidos_finalizados pf
                   LEFT JOIN pedidos p ON SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = pf.codigo_pedido
                   LEFT JOIN usuario u ON pf.id_usuario = u.id_usuario
                   WHERE pf.codigo_pedido = ?
                   LIMIT 1";
    
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("s", $codigo_pedido);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();
    
    if ($result_pedido->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit;
    }
    
    $pedido = $result_pedido->fetch_assoc();
    $stmt_pedido->close();
    
    // Buscar itens do pedido
    $sql_itens = "SELECT p.id_produto, p.valor_total, pr.nome_produto, pr.cod_produto,
                         (p.valor_total / pr.preco) as quantidade
                  FROM pedidos p
                  JOIN produtos pr ON p.id_produto = pr.id_produto
                  WHERE SUBSTRING(p.cod_pedido, 1, LOCATE('-', p.cod_pedido, LOCATE('-', p.cod_pedido) + 1) - 1) = ?";
    
    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bind_param("s", $codigo_pedido);
    $stmt_itens->execute();
    $result_itens = $stmt_itens->get_result();
    
    $itens = array();
    while ($item = $result_itens->fetch_assoc()) {
        $itens[] = array(
            'nome' => $item['nome_produto'],
            'codigo' => $item['cod_produto'],
            'quantidade' => (int)$item['quantidade'],
            'subtotal' => 'R$ ' . number_format($item['valor_total'], 2, ',', '.')
        );
    }
    $stmt_itens->close();
    
    // Montar endereço completo
    $endereco_parts = array();
    if ($pedido['rua']) $endereco_parts[] = $pedido['rua'];
    if ($pedido['numero']) $endereco_parts[] = $pedido['numero'];
    if ($pedido['complemento']) $endereco_parts[] = $pedido['complemento'];
    if ($pedido['bairro']) $endereco_parts[] = $pedido['bairro'];
    if ($pedido['cidade']) $endereco_parts[] = $pedido['cidade'];
    if ($pedido['estado']) $endereco_parts[] = $pedido['estado'];
    if ($pedido['cep']) $endereco_parts[] = 'CEP: ' . $pedido['cep'];
    
    $endereco_completo = implode(', ', $endereco_parts);
    
    // Preparar resposta
    $response = array(
        'success' => true,
        'pedido' => array(
            'id' => '#' . $pedido['codigo_pedido'],
            'data' => date('d/m/Y', strtotime($pedido['data_pedido'])),
            'cliente' => trim($pedido['primeiro_nome'] . ' ' . $pedido['ultimo_nome']),
            'email' => $pedido['email'],
            'endereco' => $endereco_completo ?: 'Endereço não informado',
            'status' => $pedido['status_pedido'],
            'subtotal' => 'R$ ' . number_format($pedido['total_produtos'], 2, ',', '.'),
            'frete' => 'R$ ' . number_format($pedido['frete'], 2, ',', '.'),
            'total' => 'R$ ' . number_format($pedido['total_final'], 2, ',', '.'),
            'itens' => $itens
        )
    );
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes do pedido: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>

