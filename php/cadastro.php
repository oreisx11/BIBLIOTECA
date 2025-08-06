<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'conexao.php'; // Certifique-se de que está referenciando corretamente a conexão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ra = $_POST["ra"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT); // Criptografando a senha

    // Validação do e-mail institucional
    if (!preg_match("/@al\.educacao\.sp\.gov\.br$/", $email)) {
        echo "Erro: O e-mail deve pertencer ao domínio @al.educacao.sp.gov.br.";
        exit;
    }

    // Preparando a inserção no banco de dados para evitar SQL Injection
    $stmt = $mysqli->prepare("INSERT INTO alunos (ra, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $ra, $email, $senha);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $mysqli->close();
}
?>