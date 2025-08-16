//CONEXÃO AO BANCO DA DIREÇÃO
document.querySelector("form").addEventListener("submit", async (e) => {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch(form.action, {
      method: "POST",
      body: data,
    });
    const json = await res.json();
    alert(json.message); // Exibe a resposta do PHP
    if (json.status === "success") window.location.href = "aluno.php"; // redireciona
  });

          // Função para alternar o menu
          function toggleMenu() {
            document.querySelector(".menuV").classList.toggle("active");
        }

        // Funçao da tela de cadastro de livros ADM
        function toggleMenu() {
            document.querySelector(".menuV").classList.toggle("active");
          }
      
          function mostrarCadastro() {
            document.getElementById("telaCadastro").style.display = "block";
          }
      
          function fecharCadastro() {
            document.getElementById("telaCadastro").style.display = "none";
          }

           function mostrarAlterador() {
            document.getElementById("telaAlterador").style.display = "block";
          }
      
          function fecharAlterador() {
            document.getElementById("telaAlterador").style.display = "none";
          }

          // Função para mostrar os livros agendados dos Alunos
          // URL base da API (ajuste conforme seu XAMPP)
const API_URL = 'http://localhost/BIBLIOTECA/php/aluno.php';

// Função para alternar o menu (assumindo que o menuV está presente)
function toggleMenu() {
    document.querySelector('.menuV').classList.toggle('active');
}

// Função para mostrar a seção de agendamentos
function mostrarAgendamentos() {
    const section = document.getElementById('agendamentosSection');
    if (section.style.display === 'none' || !section.style.display) {
        loadAgendamentos(); // Carrega os agendamentos ao abrir
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

// Função para carregar e exibir os agendamentos do aluno logado
function loadAgendamentos() {
    const idAluno = sessionStorage.getItem('id_aluno'); // Assume que o ID do aluno está na sessão
    if (!idAluno) {
        document.getElementById('agendamentosMessage').textContent = 'Usuário não autenticado. Faça login.';
        return;
    }

    fetch(`${API_URL}processa_busca_agendados.php`, {
        method: 'POST',
        body: new URLSearchParams({ id_aluno: idAluno })
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.querySelector('#agendamentosTable tbody');
        tbody.innerHTML = '';
        if (data.status === 'success' && data.agendamentos.length > 0) {
            data.agendamentos.forEach(agendamento => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${agendamento.id_agendamento}</td>
                    <td>${agendamento.titulo_livro}</td>
                    <td>${agendamento.data_agendamento}</td>
                    <td>${agendamento.status}</td>
                `;
                tbody.appendChild(row);
            });
        } else {
            document.getElementById('agendamentosMessage').textContent = 'Nenhum agendamento encontrado.';
        }
    })
    .catch(error => {
        document.getElementById('agendamentosMessage').textContent = 'Erro ao carregar agendamentos.';
    });
}

// Função para buscar livros com filtros
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch(`${API_URL}processa_busca_livros.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultsDiv = document.getElementById('searchResults');
        resultsDiv.innerHTML = '';
        if (data.status === 'success' && data.livros.length > 0) {
            const ul = document.createElement('ul');
            data.livros.forEach(livro => {
                const li = document.createElement('li');
                li.textContent = `${livro.titulo} - ${livro.autor} (${livro.indicacao})`;
                ul.appendChild(li);
            });
            resultsDiv.appendChild(ul);
        } else {
            resultsDiv.textContent = 'Nenhum livro encontrado.';
        }
    })
    .catch(error => {
        document.getElementById('searchResults').textContent = 'Erro ao buscar livros.';
    });
});

// Função para limpar filtros
function clearFilters() {
    document.getElementById('searchForm').reset();
    document.getElementById('searchResults').innerHTML = '';
}
