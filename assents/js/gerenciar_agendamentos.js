// URL base da API (ajuste conforme seu XAMPP)
const API_URL = 'http://localhost/biblioteca/php/';

// Função para alternar o menu
function toggleMenu() {
    document.querySelector('.menuV').classList.toggle('active');
}

// Função para carregar e exibir agendamentos
function loadAgendamentos() {
    fetch(`${API_URL}get_agendamentos.php`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#agendamentosList tbody');
            tbody.innerHTML = '';
            if (data.status === 'success' && data.agendamentos.length > 0) {
                data.agendamentos.forEach(agendamento => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${agendamento.id_agendamento}</td>
                        <td>${agendamento.nome_aluno}</td>
                        <td>${agendamento.titulo_livro}</td>
                        <td>${agendamento.data_agendamento}</td>
                        <td>${agendamento.status}</td>
                        <td>
                            ${agendamento.status === 'pendente' ? `
                                <button class="btn btn-sm btn-success" onclick="aceitarEmprestimo(${agendamento.id_agendamento})">Aceitar Empréstimo</button>
                                <button class="btn btn-sm btn-danger" onclick="rejeitarAgendamento(${agendamento.id_agendamento})">Rejeitar</button>
                            ` : ''}
                            ${agendamento.status === 'emprestado' ? `
                                <button class="btn btn-sm btn-primary" onclick="confirmarDevolucao(${agendamento.id_agendamento})">Confirmar Devolução</button>
                            ` : ''}
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                document.getElementById('message').textContent = 'Nenhum agendamento encontrado.';
            }
        })
        .catch(error => {
            document.getElementById('message').textContent = 'Erro ao carregar agendamentos.';
        });
}

// Função para aceitar empréstimo
function aceitarEmprestimo(id) {
    if (confirm('Aceitar o empréstimo deste agendamento?')) {
        fetch(`${API_URL}get_agendamentos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('message').textContent = data.message;
            loadAgendamentos(); // Recarrega a tabela
        })
        .catch(error => {
            document.getElementById('message').textContent = 'Erro ao aceitar empréstimo.';
        });
    }
}

// Função para rejeitar agendamento
function rejeitarAgendamento(id) {
    if (confirm('Rejeitar este agendamento?')) {
        fetch(`${API_URL}get_agendamentos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('message').textContent = data.message;
            loadAgendamentos(); // Recarrega a tabela
        })
        .catch(error => {
            document.getElementById('message').textContent = 'Erro ao rejeitar agendamento.';
        });
    }
}

// Função para confirmar devolução
function confirmarDevolucao(id) {
    if (confirm('Confirmar a devolução deste agendamento?')) {
        fetch(`${API_URL}get_agendamentos.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('message').textContent = data.message;
            loadAgendamentos(); // Recarrega a tabela
        })
        .catch(error => {
            document.getElementById('message').textContent = 'Erro ao confirmar devolução.';
        });
    }
}

// Carrega os agendamentos ao iniciar a página
document.addEventListener('DOMContentLoaded', loadAgendamentos);