<?php
// Arquivo PHP: processar_edicao_cupom.php - Processa o UPDATE do Cupom

// =======================================================
// 1. VERIFICAÇÃO E CONEXÃO
// =======================================================

// Apenas processa se o método for POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cupons.php?erro=metodo_invalido");
    exit();
}

// Inclui a conexão com o banco de dados
require_once 'connection.php'; 

// =======================================================
// 2. RECEBIMENTO E SANITIZAÇÃO DOS DADOS DO FORMULÁRIO
// =======================================================

// Dados obrigatórios:
$id_cupom = isset($_POST['id_cupom']) ? (int)$_POST['id_cupom'] : 0;
$codigo = isset($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : ''; // Garante maiúsculas
$tipo = isset($_POST['tipo_desconto']) ? trim($_POST['tipo_desconto']) : '';
$valor_enviado = isset($_POST['valor_desconto']) ? $_POST['valor_desconto'] : '0.00';
$data_expiracao = isset($_POST['data_expiracao']) ? $_POST['data_expiracao'] : NULL;
$limite_usos = isset($_POST['limite_usos']) ? (int)$_POST['limite_usos'] : 0;

// O valor_desconto (hidden) já vem no formato 'X.XX' do JavaScript
$valor = (float)$valor_enviado;

// Se o limite de usos for 0, deve ser NULL no banco para 'uso_maximo'
$uso_maximo_bd = ($limite_usos > 0) ? $limite_usos : NULL;

// Trata o campo 'tipo' para garantir que seja um valor válido no ENUM
if (!in_array($tipo, ['percentual', 'fixo', 'frete'])) {
    $tipo = 'fixo'; // Padrão seguro
}

// Se o tipo for 'frete', o valor deve ser 0.00 e o tipo deve ser mapeado para 'fixo' (se o ENUM não tem 'frete')
// Se o seu ENUM for apenas ('percentual', 'fixo'), 'frete' deve ser tratado como fixo 0.00
if ($tipo === 'frete') {
    $valor = 0.00;
    $tipo_para_bd = 'fixo';
} else {
    $tipo_para_bd = $tipo;
}

// =======================================================
// 3. EXECUÇÃO DO UPDATE NO BANCO DE DADOS
// =======================================================

if ($id_cupom > 0 && !empty($codigo)) {
    // A query de UPDATE. Note o uso de "?" para os parâmetros seguros.
    $sql = "UPDATE cupons 
            SET codigo = ?, tipo = ?, valor = ?, data_expiracao = ?, uso_maximo = ? 
            WHERE id_cupom = ?";
            
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Liga os parâmetros (s=string, d=double/float, s=string, i=integer, i=integer)
        // Note que o 'uso_maximo' e 'data_expiracao' podem ser NULL
        $stmt->bind_param("ssdsii", 
            $codigo, 
            $tipo_para_bd, 
            $valor, 
            $data_expiracao, 
            $uso_maximo_bd, 
            $id_cupom
        );
        
        if ($stmt->execute()) {
            // Sucesso na atualização
            header("Location: cupons.php?sucesso=edicao&id=" . $id_cupom);
            exit();
        } else {
            // Erro na execução
            $erro = "Erro ao atualizar cupom: " . $stmt->error;
            header("Location: cupons.php?erro=" . urlencode($erro));
            exit();
        }

        $stmt->close();
    } else {
        // Erro na preparação da declaração (erro de SQL)
        $erro = "Erro ao preparar a consulta de UPDATE: " . $conn->error;
        header("Location: cupons.php?erro=" . urlencode($erro));
        exit();
    }
} else {
    // Falta de dados essenciais
    header("Location: cupons.php?erro=dados_faltando");
    exit();
}

// Fecha a conexão (Geralmente é desnecessário aqui, pois o exit() a interrompe)
$conn->close();
?>