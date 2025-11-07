<?php require_once __DIR__ . '/session.php'; ?>
<?php
require_once 'connection.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'produto' => null);

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $produto_id = intval($_GET['id']);

    try {
        if ($conn->connect_error) {
            throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM produtos WHERE id_produto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $produto = $result->fetch_assoc();
            $response['success'] = true;
            $response['produto'] = $produto;
        } else {
            $response['message'] = "Produto não encontrado.";
        }

        $stmt->close();
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Erro ao buscar produto: " . $e->getMessage());
    }
} else {
    $response['message'] = "ID do produto não fornecido.";
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response);
exit();
?>
