<?php
session_start();
include 'conexao.php';

header('Content-Type: application/json'); // Garante que a resposta seja JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitização e validação dos dados de entrada
    $email = filter_var(trim($_POST["email_ED"]), FILTER_VALIDATE_EMAIL);
    $senha = trim($_POST["senha_ED"]);

    if (!$email || empty($senha)) {
        echo json_encode(["status" => "error", "message" => "Erro: Todos os campos devem ser preenchidos corretamente."]);
        exit();
    }

    // Preparação da consulta para evitar SQL Injection
    $stmt = $mysqli->prepare("SELECT id_ED, senha_ED FROM professores WHERE email_ED = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Erro na preparação da consulta ao banco de dados."]);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_ED, $senha_banco);
        $stmt->fetch();

        // Comparação simples (sem password_verify)
        if ($senha === $senha_banco) { 
            $_SESSION["id_ED"] = $id_ED;
            $_SESSION["email_ED"] = $email;

            echo json_encode(["status" => "success", "message" => "Login realizado com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro: Senha incorreta."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Erro: Usuário não encontrado."]);
    }

    // Fechar conexão
    $stmt->close();
    $mysqli->close();
}
?>
