<?php
session_start();
require_once 'connection.php';
require_once 'carrinho_functions.php';

$acao = $_POST['acao'] ?? '';
$produto_id = intval($_POST['produto_id'] ?? 0);
$quantidade = intval($_POST['quantidade'] ?? 1);

$response = array('success' => false, 'message' => '');

try {
    switch ($acao) {
        case 'adicionar':
            if ($produto_id > 0 && $quantidade > 0) {
                adicionarAoCarrinho($produto_id, $quantidade);
                $response['success'] = true;
                $response['message'] = 'Produto adicionado ao carrinho!';
                $response['total_itens'] = contarItensCarrinho();
            } else {
                $response['message'] = 'Dados inválidos.';
            }
            break;
            
        case 'remover':
            if ($produto_id > 0) {
                removerDoCarrinho($produto_id);
                $response['success'] = true;
                $response['message'] = 'Produto removido do carrinho!';
                $response['total_itens'] = contarItensCarrinho();
            } else {
                $response['message'] = 'ID do produto inválido.';
            }
            break;
            
        case 'atualizar':
            if ($produto_id > 0) {
                atualizarQuantidade($produto_id, $quantidade);
                $response['success'] = true;
                $response['message'] = 'Quantidade atualizada!';
                $response['total_itens'] = contarItensCarrinho();
            } else {
                $response['message'] = 'Dados inválidos.';
            }
            break;
            
        case 'limpar':
            limparCarrinho();
            $response['success'] = true;
            $response['message'] = 'Carrinho limpo!';
            $response['total_itens'] = 0;
            break;
            
        case 'aplicar_cupom':
            $codigo_cupom = $_POST['codigo_cupom'] ?? '';
            $resultado = aplicarCupomAoCarrinho($codigo_cupom);
            $response = $resultado;
            break;
            
        case 'finalizar_compra':
            // Para teste, usar ID de usuário 1 (você pode ajustar conforme necessário)
            $id_usuario = 1; // Em um sistema real, isso viria da sessão do usuário logado
            $resultado = finalizarCompra($id_usuario);
            $response = $resultado;
            break;
            
        default:
            $response['message'] = 'Ação inválida.';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erro: ' . $e->getMessage();
    error_log("Erro no carrinho_action.php: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
