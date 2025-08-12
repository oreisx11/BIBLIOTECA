<?php
// config.php - Arquivo de configuração para conexão com o banco de dados
// Coloque este arquivo em um diretório raiz ou includes/

$host = 'localhost'; // Host do XAMPP (padrão)
$dbname = 'db_biblioteca'; // Nome do banco de dados
$username = 'root'; // Usuário padrão do XAMPP MySQL
$password = ''; // Senha padrão (vazia no XAMPP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Funções úteis globais
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn($type) {
    startSession();
    return isset($_SESSION[$type . '_id']);
}

function getLoggedUserId($type) {
    startSession();
    return $_SESSION[$type . '_id'] ?? null;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}
?>