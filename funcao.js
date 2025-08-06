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
    if (json.status === "success") window.location.href = "painel.php"; // redireciona
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
            document.getElementById("AlteradorSenha").style.display = "block";
          }
      
          function fecharAlterador() {
            document.getElementById("AlteradorSenha").style.display = "none";
          }

           function mostrarAgendado() {
            document.getElementById("LivroAgendado").style.display = "block";
          }
      
          function fecharAgendado() {
            document.getElementById("LivroAgendado").style.display = "none";
          }
