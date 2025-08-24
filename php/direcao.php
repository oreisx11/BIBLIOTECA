<?php
// direcao.php - Arquivo para funções relacionadas à direção
// Uso similar a aluno.php, ex: 'direcao.php?action=login' etc.

include 'config.php';

$action = strtolower(trim($_POST['action'] ?? $_GET['action'] ?? ''));

switch ($action) {
    case 'login':
        handleLoginDirecao();
        break;
    case 'logout':
        handleLogout('diretor');
        break;
    case 'register_book':
        handleRegisterBook();
        break;
    case 'change_password':
        handleChangePassword('diretor');
        break;
    case 'get_schedules':
        handleGetSchedules();
        break;
    case 'update_schedule_status':
        handleUpdateScheduleStatus();
        break;
    default:
        jsonResponse(['error' => 'Ação inválida'], 400);
}

// Funções específicas para direção

function handleLoginDirecao() {
    // Verifica se o método da requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    global $pdo;
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Valida campos obrigatórios
    if (empty($email) || empty($senha)) {
        jsonResponse(['error' => 'Campos obrigatórios faltando'], 400);
    }

    // Busca o usuário no banco de dados
    $stmt = $pdo->prepare("SELECT id_diretor, senha FROM diretores WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica credenciais
    if (!$user || $senha !== $user['senha']) {
    jsonResponse(['error' => 'Credenciais inválidas'], 401);
}

    // Inicia sessão e armazena ID do diretor
    startSession();
    $_SESSION['diretor_id'] = $user['id_diretor'];
    header("Location: ../html/PAG_DIRE.html");
} 

// Cadastro de livro
function handleRegisterBook() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
        exit;
    }
    
    if (!isLoggedIn('diretor')) {
        header("Location: ../html/PAG_DIRE.html");
        exit;
    }

    global $pdo;
    $titulo     = $_POST['titulo'] ?? '';
    $autor      = $_POST['autor'] ?? '';
    $indicacao  = $_POST['indicacao'] ?? '';
    $disciplina = $_POST['disciplina'] ?? '';

    if (empty($titulo) || empty($autor) || empty($indicacao) || empty($disciplina)) {
        jsonResponse(['error' => 'Campos obrigatórios faltando'], 400);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO livros (titulo, autor, indicacao, disciplina) 
         VALUES (:titulo, :autor, :indicacao, :disciplina)"
    );

    try {
        $stmt->execute([
            'titulo'     => $titulo,
            'autor'      => $autor,
            'indicacao'  => $indicacao,
            'disciplina' => $disciplina
        ]);
        header("Location: ../html/PAG_DIRE.html?sucesso=1");
        exit;
    } catch (PDOException $e) {
        header("Location: ../html/PAG_DIRE.html?erro=db");
        exit;
    }
}

function handleGetSchedules() {
    // Verifica autenticação
    if (!isLoggedIn('diretor')) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $status = $_GET['status'] ?? ''; // Opcional: 'pendente', 'emprestado'

    // Consulta base para agendamentos pendentes ou emprestados
    $query = "
        SELECT a.*, al.nome AS aluno_nome, l.titulo AS livro_titulo 
        FROM agendamentos a 
        JOIN alunos al ON a.id_aluno = al.id_aluno
        JOIN livros l ON a.id_livro = l.id_livro
        WHERE a.status IN ('pendente', 'emprestado')
    ";
    $params = [];

    // Adiciona filtro de status se fornecido
    if (!empty($status)) {
        $query .= " AND a.status = :status";
        $params['status'] = $status;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(['schedules' => $schedules]);
}

function handleChangePassword($type) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    if (!isLoggedIn($type)) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nova_senha) || empty($confirmar_senha)) {
        jsonResponse(['error' => 'Campos obrigatórios faltando'], 400);
    }

    if ($nova_senha !== $confirmar_senha) {
        jsonResponse(['error' => 'Senhas não coincidem'], 400);
    }

    $id = getLoggedUserId($type);
    $table = $type === 'aluno' ? 'alunos' : 'diretores';
    $id_field = $type === 'aluno' ? 'id_aluno' : 'id_diretor';

    $stmt = $pdo->prepare("SELECT senha FROM $table WHERE $id_field = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $hashedNova = hashPassword($nova_senha);
    $stmt = $pdo->prepare("UPDATE $table SET senha = :senha WHERE $id_field = :id");
    try {
        $stmt->execute(['senha' => $hashedNova, 'id' => $id]);
        header("Location: ../html/PAG_DIRE.html?sucesso=1");
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Erro ao alterar senha: ' . $e->getMessage()], 500);
    }
}

function handleUpdateScheduleStatus() {
    // Verifica se o método da requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Método não permitido'], 405);
    }

    // Verifica autenticação
    if (!isLoggedIn('diretor')) {
        jsonResponse(['error' => 'Não autenticado'], 401);
    }

    global $pdo;
    $id_agendamento = $_POST['id_agendamento'] ?? 0;
    $novo_status = $_POST['novo_status'] ?? ''; // 'emprestado' ou 'devolvido'

    // Valida parâmetros
    if (empty($id_agendamento) || !in_array($novo_status, ['emprestado', 'devolvido'])) {
        jsonResponse(['error' => 'Parâmetros inválidos'], 400);
    }

    // Atualiza o status no banco de dados
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = :status WHERE id_agendamento = :id");
    try {
        $stmt->execute(['status' => $novo_status, 'id' => $id_agendamento]);
        if ($stmt->rowCount() == 0) {
            jsonResponse(['error' => 'Agendamento não encontrado'], 404);
        }
        jsonResponse(['success' => 'Status atualizado']);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Erro ao atualizar: ' . $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        handleLoginDirecao();
    } elseif ($action === 'register_book') {
        handleRegisterBook();
    } elseif ($action === 'change_password') {
        handleChangePassword();
    }
}

?>