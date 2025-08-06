<?php
session_start();
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ra = trim($_POST["ra"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    // 📌 Verificar se o usuário existe
    $stmt = $mysqli->prepare("SELECT id, senha FROM alunos WHERE ra = ? AND email = ?");
    $stmt->bind_param("ss", $ra, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $senha_hash);
        $stmt->fetch();

        // 📌 Verificar senha
        if (password_verify($senha, $senha_hash)) {
            $_SESSION["id"] = $id;
            $_SESSION["ra"] = $ra;
            $_SESSION["email"] = $email;
            echo json_encode(["status" => "success", "message" => "Login realizado com sucesso!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erro: Senha incorreta."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Erro: Usuário não encontrado."]);
    }

    $stmt->close();
    $mysqli->close();
}
?>