document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.comment-form').forEach(form => {
      form.addEventListener('submit', async e => {
        e.preventDefault();
  
        const postId = form.dataset.postId;
        const url    = form.action;
        const data   = new FormData(form);
  
        // Envoi en AJAX
        const resp = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: data
        });
  
        if (!resp.ok) {
          console.error('Erreur AJAX', resp);
          return;
        }
  
        const json = await resp.json();
  
        // Construire le HTML du commentaire
        const commentHtml = `
          <div class="d-flex align-items-start mb-3">
            <img 
              src="${json.user.avatar}" 
              alt="Avatar de ${json.user.nom}" 
              class="rounded-circle me-3" 
              style="width:40px;height:40px;object-fit:cover;"
            >
            <div>
              <strong>${json.user.nom}</strong>
              <p class="mb-1">${json.content}</p>
              <small class="text-muted">${json.createdAt}</small>
            </div>
          </div>`;
  
        // L’insérer dans le modal-body du post correspondant
        const modalBody = document
          .querySelector(`#commentsModal${postId} .modal-body`);
        modalBody.insertAdjacentHTML('beforeend', commentHtml);
  
        // Réinitialiser le champ de saisie
        form.querySelector('input[name="comment[content]"]').value = '';
  
        // Mettre à jour le compteur de commentaires
        const btn = document.querySelector(
          `button[data-bs-target="#commentsModal${postId}"]`
        );
        const count = modalBody.querySelectorAll('.d-flex.align-items-start').length;
        btn.textContent = `Commentaires (${count})`;
      });
    });
  });
  