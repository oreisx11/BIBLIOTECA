async function searchBooks() {
  const query = document.getElementById('searchInput').value.trim();
  const booksContainer = document.getElementById('booksContainer');
  booksContainer.innerHTML = '<p class="text-primary text-center">Carregando...</p>';

  if (!query) {
    booksContainer.innerHTML = '<p class="text-danger text-center">Digite um termo para buscar.</p>';
    return;
  }

  try {
    const response = await fetch(`https://www.googleapis.com/books/v1/volumes?q=${encodeURIComponent(query)}`);
    const data = await response.json();

    booksContainer.innerHTML = '';

    if (!data.items) {
      booksContainer.innerHTML = '<p class="text-warning text-center">Nenhum livro encontrado.</p>';
      return;
    }

    data.items.slice(0, 9).forEach(book => {
      const volumeInfo = book.volumeInfo;

      const col = document.createElement('div');
      col.className = 'col-12 col-sm-6 col-md-4 d-flex';

      const thumbnail = volumeInfo.imageLinks?.thumbnail || 'placeholder.jpg';
      const title = volumeInfo.title || 'Sem título';
      const authors = volumeInfo.authors ? volumeInfo.authors.join(', ') : 'Autor desconhecido';
      const previewLink = volumeInfo.previewLink || '#';

      col.innerHTML = `
        <div class="card p-2 d-flex flex-column">
          <img src="${thumbnail}" class="card-img-top mb-2" alt="Capa do livro">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">${title}</h5>
            <p class="card-text">${authors}</p>
            <a href="${previewLink}" target="_blank" class="mt-auto btn btn-sm btn-primary">Ler online</a>
          </div>
        </div>
      `;

      booksContainer.appendChild(col);
    });
  } catch (error) {
    booksContainer.innerHTML = '<p class="text-danger text-center">Erro ao buscar livros. Tente novamente.</p>';
    console.error('Erro:', error);
  }
}
