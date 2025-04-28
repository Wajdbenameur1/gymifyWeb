document.addEventListener('DOMContentLoaded', () => {
    // Handler for comment forms in modals (index page)
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
  
        // L'insérer dans le modal-body du post correspondant
        const modalBody = document
          .querySelector(`#commentsModal${postId} .modal-body`);
        modalBody.insertAdjacentHTML('beforeend', commentHtml);
  
        // Réinitialiser le champ de saisie
        form.querySelector('input[name="comment[content]"]').value = '';
  
        // Mettre à jour le compteur de commentaires dans le bouton modal
        updateIndexCommentCount(postId);
      });
    });

    // Handler for comment forms in single post view (show.html.twig)
    document.querySelectorAll('.js-add-comment-form').forEach(form => {
      form.addEventListener('submit', async e => {
        e.preventDefault();
  
        const postId = form.dataset.postId;
        const url    = form.action;
        const data   = new FormData(form);
  
        // Disable form during submission
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
          submitButton.disabled = true;
          submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Envoi...';
        }
        
        try {
          // Send AJAX request
          const resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: data
          });
    
          if (!resp.ok) {
            const errorsContainer = document.getElementById(`comment-errors-${postId}`);
            if (errorsContainer) {
              errorsContainer.textContent = 'Une erreur est survenue lors de l\'envoi du commentaire.';
              errorsContainer.style.display = 'block';
              setTimeout(() => {
                errorsContainer.style.display = 'none';
              }, 4000);
            }
            return;
          }
    
          const json = await resp.json();
    
          // Get the comments container
          const commentsContainer = document.querySelector('.comments-container');
          if (!commentsContainer) return;
          
          // Remove "no comments" message if it exists
          const noCommentsMsg = commentsContainer.querySelector('.text-center.text-muted');
          if (noCommentsMsg && noCommentsMsg.textContent.includes('Aucun commentaire')) {
            noCommentsMsg.remove();
          }
          
          // Create the new comment element
          const commentElement = document.createElement('div');
          commentElement.className = 'd-flex align-items-start mb-3';
          commentElement.style.animation = 'highlight-new-item 2s ease-out';
          
          // Set the HTML for the new comment
          commentElement.innerHTML = `
            <img 
              src="${json.user.avatar || json.user.imageUrl || '/img/screen/user.png'}" 
              alt="Avatar de ${json.user.nom}"
              class="rounded-circle me-2 comment-avatar"
              style="width:32px; height:32px; object-fit:cover;"
            >
            <div class="comment-container w-100 position-relative">
              <strong>${json.user.nom}</strong>
              <p class="comment-content mb-1">${json.content}</p>
              <small class="text-muted">${json.createdAt}</small>
              <div class="comment-actions">
                <button class="btn-icon edit js-edit-comment-btn" 
                        data-comment-id="${json.id}"
                        data-token="${json.tokens?.edit || ''}"
                        data-tooltip="Modifier">
                  <i class="fas fa-edit"></i>
                </button>
                <form action="/comment/delete/${json.id}" 
                      method="post" 
                      class="js-delete-comment-form d-inline" 
                      data-post-id="${postId}">
                  <input type="hidden" name="_token" value="${json.tokens?.delete || ''}">
                  <button type="submit" class="btn-icon delete" data-tooltip="Supprimer">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </form>
              </div>
            </div>
          `;
          
          // Add it to the top of the comments
          if (commentsContainer.firstChild) {
            commentsContainer.insertBefore(commentElement, commentsContainer.firstChild);
          } else {
            commentsContainer.appendChild(commentElement);
          }
          
          // Reset the form
          form.querySelector('input[name="content"]').value = '';
        } catch (error) {
          console.error('Error submitting comment:', error);
        } finally {
          // Re-enable form
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Ajouter';
          }
        }
      });
    });
    
    // Handler for comment deletion
    document.body.addEventListener('click', function(e) {
      if (e.target.closest('.js-delete-comment-form button')) {
        const form = e.target.closest('.js-delete-comment-form');
        if (form) {
          e.preventDefault();
          if (confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
            const commentElement = form.closest('.d-flex.align-items-start');
            const postId = form.dataset.postId;
            
            fetch(form.action, {
              method: 'POST',
              body: new FormData(form),
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                commentElement.remove();
                // Décrémenter le compteur de commentaires
                decrementCommentCount(postId);
              } else {
                alert(data.message || 'Erreur lors de la suppression');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Une erreur est survenue');
            });
          }
        }
      }
    });
    
    // Function to update comment count in index page
    function updateIndexCommentCount(postId) {
      // Mettre à jour le compteur dans le bouton d'ouverture du modal
      const btn = document.querySelector(`button[data-bs-toggle="modal"][data-bs-target="#commentsModal${postId}"]`);
      if (btn) {
        const countElement = btn.querySelector('.comment-count');
        if (countElement) {
          // Incrémenter le compteur
          const currentCount = parseInt(countElement.textContent.trim()) || 0;
          countElement.textContent = currentCount + 1;
          
          // Ajouter une animation
          countElement.classList.add('highlight');
          setTimeout(() => {
            countElement.classList.remove('highlight');
          }, 800);
        } else {
          // S'il n'y a pas d'élément dédié au compteur, mettre à jour le texte du bouton
          const currentText = btn.textContent.trim();
          const match = currentText.match(/Commentaires?\s*\((\d+)\)/i);
          
          if (match) {
            // Incrémenter le compteur existant
            const newCount = parseInt(match[1]) + 1;
            btn.textContent = currentText.replace(/\(\d+\)/, `(${newCount})`);
          } else if (currentText.toLowerCase().includes('commentaire')) {
            // Ajouter un compteur s'il n'y a que le texte "Commentaire(s)"
            btn.textContent = `${currentText} (1)`;
          }
        }
      }
    }
    
    // Function to decrement comment count
    function decrementCommentCount(postId) {
      // Décrémenter le compteur dans le bouton d'ouverture du modal
      const btns = document.querySelectorAll(`button[data-bs-toggle="modal"][data-bs-target="#commentsModal${postId}"]`);
      btns.forEach(btn => {
        const countElement = btn.querySelector('.comment-count');
        if (countElement) {
          // Décrémenter le compteur
          const currentCount = parseInt(countElement.textContent.trim()) || 0;
          if (currentCount > 0) {
            countElement.textContent = currentCount - 1;
          }
        }
      });
    }
  });
  