<?php
// config.php - Conexão com o banco e funções utilitárias

session_start();

// Headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Conexão PDO
$host = "localhost";
$dbname = "db_biblioteca"; // nome do seu banco
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => "Erro na conexão: " . $e->getMessage()]));
}

// ---------- Funções Utilitárias ----------

// Retornar resposta em JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Iniciar sessão (se não estiver iniciada)
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Verificar se usuário está logado
function isLoggedIn($type) {
    startSession();
    return isset($_SESSION[$type . '_id']);
}

// Retornar ID do usuário logado
function getLoggedUserId($type) {
    startSession();
    return $_SESSION[$type . '_id'] ?? null;
}

?>