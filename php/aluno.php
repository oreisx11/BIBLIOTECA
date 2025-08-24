<?php
// aluno.php - Arquivo para funcionalidades dos alunos
// Inclua este arquivo nos HTMLs relevantes via PHP ou use como API (ex.: via AJAX ou form action)
// Assuma que os forms nos HTMLs apontam para endpoints como 'aluno.php?action=register' (use $_GET['action'])

include 'config.php'; // Inclua a configuração do DB

$action = strtolower(trim($_GET['action'] ?? $_POST['action'] ?? ''));

switch ($action) {
    case 'register':
        handleRegisterAluno();
        break;
    case 'login':
        handleLoginAluno();
        break;
    case 'logout':
        handleLogout('aluno');
        break;
    case 'search_books':
        handleSearchBooks();
        break;
    case 'schedule_book':
        handleScheduleBook();
        break;
    case 'my_schedules':
        handleMySchedules();
        break;
    case 'change_password':
        handleChangePassword('aluno');
        break;
    default:
        jsonResponse(['error' => 'Ação inválida'], 400);
}

// Funções específicas para alunos

function handleRegisterAluno() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    global $pdo;
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        jsonResponse(['error' => 'Campos obrigatórios faltando'], 400);
    }

    // Verificar se email ou nome já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE email = :email OR nome = :nome");
    $stmt->execute(['email' => $email, 'nome' => $nome]);
    if ($stmt->fetchColumn() > 0) {
        jsonResponse(['error' => 'Nome ou email já cadastrado'], 409);
    }

    $hashedSenha = hashPassword($senha);
    $stmt = $pdo->prepare("INSERT INTO alunos (nome, email, senha) VALUES (:nome, :email, :senha)");
    try {
        $stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $hashedSenha]);
        jsonResponse(['success' => 'Cadastro realizado com sucesso']);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Erro ao cadastrar: ' . $e->getMessage()], 500);
    }
}

function handleLoginAluno() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    global $pdo;
    $email = $_GET['email'] ?? '';
    $senha = $_GET['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        jsonResponse(['error' => 'Campos obrigatórios faltando'], 400);
    }

    $stmt = $pdo->prepare("SELECT id_aluno, senha FROM alunos WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !verifyPassword($senha, $user['senha'])) {
        jsonResponse(['error' => 'Credenciais inválidas'], 401);
    }

    startSession();
    $_SESSION['aluno_id'] = $user['id_aluno'];
    header("Location: ../html/PAG_ALUNO.html");
}

function handleLogout($type) {
    startSession();
    unset($_SESSION[$type . '_id']);
    session_destroy();
    jsonResponse(['success' => 'Logout realizado']);
}

function handleSearchBooks() {
    if (!isLoggedIn('aluno')) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $autor = $_GET['autor'] ?? '';
    $titulo = $_GET['titulo'] ?? '';
    $indicacao = $_GET['indicacao'] ?? 'Todas as séries';
    $disciplina = $_GET['disciplina'] ?? 'Todas as disciplinas';

    $query = "SELECT * FROM livros WHERE 1=1";
    $params = [];

    if (!empty($autor)) {
        $query .= " AND autor LIKE :autor";
        $params['autor'] = "%$autor%";
    }
    if (!empty($titulo)) {
        $query .= " AND titulo LIKE :titulo";
        $params['titulo'] = "%$titulo%";
    }
    if ($indicacao !== 'Todas as séries') {
        $query .= " AND indicacao = :indicacao";
        $params['indicacao'] = $indicacao;
    }
    if ($disciplina !== 'Todas as disciplinas') {
        $query .= " AND disciplina = :disciplina";
        $params['disciplina'] = $disciplina;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['books' => $books]);
}

function handleScheduleBook() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    if (!isLoggedIn('aluno')) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $id_livro = $_POST['id_livro'] ?? 0;

    if (empty($id_livro)) {
        jsonResponse(['error' => 'ID do livro obrigatório'], 400);
    }

    // Verificar se o livro existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM livros WHERE id_livro = :id");
    $stmt->execute(['id' => $id_livro]);
    if ($stmt->fetchColumn() == 0) {
        jsonResponse(['error' => 'Livro não encontrado'], 404);
    }

    // Verificar se já agendado pelo aluno
    $id_aluno = getLoggedUserId('aluno');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE id_aluno = :aluno AND id_livro = :livro AND status != 'devolvido'");
    $stmt->execute(['aluno' => $id_aluno, 'livro' => $id_livro]);
    if ($stmt->fetchColumn() > 0) {
        jsonResponse(['error' => 'Livro já agendado'], 409);
    }

    $stmt = $pdo->prepare("INSERT INTO agendamentos (id_aluno, id_livro, data_agendamento) VALUES (:aluno, :livro, NOW())");
    try {
        $stmt->execute(['aluno' => $id_aluno, 'livro' => $id_livro]);
        jsonResponse(['success' => 'Agendamento realizado']);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Erro ao agendar: ' . $e->getMessage()], 500);
    }
}

function handleMySchedules() {
    if (!isLoggedIn('aluno')) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $id_aluno = getLoggedUserId('aluno');
    $stmt = $pdo->prepare("
        SELECT a.*, l.titulo, l.autor 
        FROM agendamentos a 
        JOIN livros l ON a.id_livro = l.id_livro 
        WHERE a.id_aluno = :id
    ");
    $stmt->execute(['id' => $id_aluno]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['schedules' => $schedules]);
}
?>