<?php require_once __DIR__ . '/session.php'; ?>
<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "speedzone";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexão falhou" . $conn->connect_error);
}
