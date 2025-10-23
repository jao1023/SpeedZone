<?php
// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'connection.php';

/**
 * Adicionar item ao carrinho
 */
function adicionarAoCarrinho($produto_id, $quantidade = 1) {
    if (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id] += $quantidade;
    } else {
        $_SESSION['carrinho'][$produto_id] = $quantidade;
    }
    return true;
}

/**
 * Remover item do carrinho
 */
function removerDoCarrinho($produto_id) {
    if (isset($_SESSION['carrinho'][$produto_id])) {
        unset($_SESSION['carrinho'][$produto_id]);
        return true;
    }
    return false;
}

/**
 * Atualizar quantidade de um item no carrinho
 */
function atualizarQuantidade($produto_id, $quantidade) {
    if ($quantidade <= 0) {
        return removerDoCarrinho($produto_id);
    }
    
    if (isset($_SESSION['carrinho'][$produto_id])) {
        $_SESSION['carrinho'][$produto_id] = $quantidade;
        return true;
    }
    return false;
}

/**
 * Obter todos os itens do carrinho com detalhes do produto
 */
function obterCarrinhoComDetalhes() {
    if (empty($_SESSION['carrinho'])) {
        return array();
    }
    
    global $conn;
    $carrinho_detalhado = array();
    
    try {
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }
        
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            $sql = "SELECT * FROM produtos WHERE id_produto = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $produto = $result->fetch_assoc();
                $produto['quantidade_carrinho'] = $quantidade;
                $produto['subtotal'] = $produto['preco'] * $quantidade;
                $carrinho_detalhado[] = $produto;
            }
            
            $stmt->close();
        }
        
    } catch (Exception $e) {
        error_log("Erro ao obter detalhes do carrinho: " . $e->getMessage());
    }
    
    return $carrinho_detalhado;
}

/**
 * Calcular total do carrinho
 */
function calcularTotalCarrinho() {
    $carrinho = obterCarrinhoComDetalhes();
    $total = 0;
    
    foreach ($carrinho as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

/**
 * Contar total de itens no carrinho
 */
function contarItensCarrinho() {
    $total = 0;
    foreach ($_SESSION['carrinho'] as $quantidade) {
        $total += $quantidade;
    }
    return $total;
}

/**
 * Limpar carrinho
 */
function limparCarrinho() {
    $_SESSION['carrinho'] = array();
    return true;
}

/**
 * Validar e aplicar cupom
 */
function validarCupom($codigo_cupom) {
    global $conn;
    
    if (empty($codigo_cupom)) {
        return array('success' => false, 'message' => 'Código do cupom não informado');
    }
    
    try {
        $sql = "SELECT * FROM cupons WHERE codigo = ? AND status = 'Ativo'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $codigo_cupom);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return array('success' => false, 'message' => 'Cupom não encontrado ou inativo');
        }
        
        $cupom = $result->fetch_assoc();
        
        // Verificar se o cupom expirou
        if ($cupom['data_expiracao'] && strtotime($cupom['data_expiracao']) < time()) {
            return array('success' => false, 'message' => 'Cupom expirado');
        }
        
        // Verificar limite de uso
        if ($cupom['uso_maximo'] && $cupom['usos_atuais'] >= $cupom['uso_maximo']) {
            return array('success' => false, 'message' => 'Cupom esgotado');
        }
        
        return array('success' => true, 'cupom' => $cupom);
        
    } catch (Exception $e) {
        error_log("Erro ao validar cupom: " . $e->getMessage());
        return array('success' => false, 'message' => 'Erro interno do servidor');
    }
}

/**
 * Calcular desconto do cupom
 */
function calcularDescontoCupom($cupom, $subtotal) {
    if ($cupom['tipo'] === 'percentual') {
        return ($subtotal * $cupom['valor']) / 100;
    } elseif ($cupom['tipo'] === 'fixo') {
        return min($cupom['valor'], $subtotal); // Não pode ser maior que o subtotal
    }
    return 0;
}

/**
 * Aplicar cupom ao carrinho
 */
function aplicarCupomAoCarrinho($codigo_cupom) {
    $validacao = validarCupom($codigo_cupom);
    
    if (!$validacao['success']) {
        return $validacao;
    }
    
    $cupom = $validacao['cupom'];
    $subtotal = calcularTotalCarrinho();
    $desconto = calcularDescontoCupom($cupom, $subtotal);
    
    // Salvar cupom na sessão
    $_SESSION['cupom_aplicado'] = array(
        'codigo' => $cupom['codigo'],
        'tipo' => $cupom['tipo'],
        'valor' => $cupom['valor'],
        'desconto' => $desconto
    );
    
    return array(
        'success' => true, 
        'message' => 'Cupom aplicado com sucesso!',
        'desconto' => $desconto,
        'total_final' => $subtotal - $desconto
    );
}

/**
 * Gerar código único do pedido
 */
function gerarCodigoPedido() {
    global $conn;
    
    try {
        // Buscar o último código gerado
        $sql = "SELECT codigo_pedido FROM pedidos_finalizados WHERE codigo_pedido LIKE 'SPDZ-%' ORDER BY id_pedido DESC LIMIT 1";
        $result = $conn->query($sql);
        
        $proximo_numero = 1;
        if ($result && $result->num_rows > 0) {
            $ultimo_codigo = $result->fetch_assoc()['codigo_pedido'];
            // Extrair o número do último código (ex: SPDZ-0001 -> 1)
            $numero_atual = (int) substr($ultimo_codigo, 5);
            $proximo_numero = $numero_atual + 1;
        }
        
        // Gerar novo código no formato SPDZ-0001
        $novo_codigo = 'SPDZ-' . str_pad($proximo_numero, 4, '0', STR_PAD_LEFT);
        
        return $novo_codigo;
        
    } catch (Exception $e) {
        error_log("Erro ao gerar código do pedido: " . $e->getMessage());
        return 'SPDZ-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Finalizar compra - criar pedido
 */
function finalizarCompra($id_usuario = null) {
    global $conn;
    
    if (empty($_SESSION['carrinho'])) {
        return array('success' => false, 'message' => 'Carrinho vazio');
    }
    
    try {
        $conn->begin_transaction();
        
        // Gerar código único do pedido
        $codigo_pedido = gerarCodigoPedido();
        
        // Calcular totais
        $total_produtos = calcularTotalCarrinho();
        $frete = 20.00; // Frete fixo
        $desconto_cupom = 0;
        
        if (isset($_SESSION['cupom_aplicado'])) {
            $desconto_cupom = $_SESSION['cupom_aplicado']['desconto'];
        }
        
        $total_final = $total_produtos + $frete - $desconto_cupom;
        
        // Inserir pedido na tabela pedidos_finalizados
        $sql_pedido = "INSERT INTO pedidos_finalizados (id_usuario, codigo_pedido, status_pedido, total_produtos, frete, total_final) VALUES (?, ?, 'Pendente', ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("isddd", $id_usuario, $codigo_pedido, $total_produtos, $frete, $total_final);
        $stmt_pedido->execute();
        $id_pedido = $conn->insert_id;
        
        // Inserir itens do pedido na tabela pedidos (tabela original)
        // Usar um contador para criar códigos únicos para cada item
        $item_counter = 1;
        foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
            // Buscar dados do produto
            $sql_produto = "SELECT preco FROM produtos WHERE id_produto = ?";
            $stmt_produto = $conn->prepare($sql_produto);
            $stmt_produto->bind_param("i", $produto_id);
            $stmt_produto->execute();
            $result_produto = $stmt_produto->get_result();
            
            if ($result_produto->num_rows > 0) {
                $produto = $result_produto->fetch_assoc();
                $valor_total_item = $produto['preco'] * $quantidade;
                
                // Criar código único para cada item (ex: SPDZ-0001-1, SPDZ-0001-2)
                $codigo_item = $codigo_pedido . '-' . $item_counter;
                
                // Inserir na tabela pedidos original
                $sql_item = "INSERT INTO pedidos (cod_pedido, id_cliente, id_produto, valor_total, status_pedido) VALUES (?, ?, ?, ?, 'Em Processo de entrega')";
                $stmt_item = $conn->prepare($sql_item);
                $stmt_item->bind_param("siid", $codigo_item, $id_usuario, $produto_id, $valor_total_item);
                $stmt_item->execute();
                $stmt_item->close();
                
                $item_counter++;
            }
            $stmt_produto->close();
        }
        
        $conn->commit();
        
        // Limpar carrinho e cupom da sessão
        limparCarrinho();
        unset($_SESSION['cupom_aplicado']);
        
        return array(
            'success' => true,
            'message' => 'Pedido finalizado com sucesso!',
            'codigo_pedido' => $codigo_pedido,
            'id_pedido' => $id_pedido
        );
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao finalizar compra: " . $e->getMessage());
        return array('success' => false, 'message' => 'Erro ao finalizar compra: ' . $e->getMessage());
    }
}
?>
