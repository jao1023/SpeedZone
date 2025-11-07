<?php require_once __DIR__ . '/session.php'; ?>
<?php
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
                $resultado = adicionarAoCarrinho($produto_id, $quantidade);
                if (is_array($resultado)) {
                    $response = array_merge($response, $resultado);
                    if ($resultado['success']) {
                        $response['total_itens'] = contarItensCarrinho();
                    }
                } else if ($resultado === true) {
                    $response['success'] = true;
                    $response['message'] = 'Produto adicionado ao carrinho!';
                    $response['total_itens'] = contarItensCarrinho();
                } else {
                    $response['message'] = 'Não foi possível adicionar ao carrinho.';
                }
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
                $resultado = atualizarQuantidade($produto_id, $quantidade);
                if (is_array($resultado)) {
                    $response = array_merge($response, $resultado);
                    if ($resultado['success']) {
                        $response['total_itens'] = contarItensCarrinho();
                    }
                } else if ($resultado === true) {
                    $response['success'] = true;
                    $response['message'] = 'Quantidade atualizada!';
                    $response['total_itens'] = contarItensCarrinho();
                } else {
                    $response['message'] = 'Não foi possível atualizar a quantidade.';
                }
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
            // Usar usuário logado
            $id_usuario = $_SESSION['user_id'] ?? null;
            if (!$id_usuario) {
                $response['success'] = false;
                $response['message'] = 'É necessário estar logado para finalizar a compra.';
                break;
            }

            // Validar se usuário possui endereço completo
            $sql = "SELECT cep, rua, numero, bairro, cidade, estado FROM usuario WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows === 1) {
                $u = $result->fetch_assoc();
                $cep = preg_replace('/\D/', '', $u['cep'] ?? '');
                $endereco_ok = !empty($cep) && strlen($cep) === 8 && !empty($u['rua']) && !empty($u['numero']) && !empty($u['bairro']) && !empty($u['cidade']) && !empty($u['estado']);
                if (!$endereco_ok) {
                    $response['success'] = false;
                    $response['message'] = 'Cadastre um endereço completo para finalizar a compra.';
                    $stmt->close();
                    break;
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Usuário não encontrado.';
                $stmt->close();
                break;
            }
            $stmt->close();

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
