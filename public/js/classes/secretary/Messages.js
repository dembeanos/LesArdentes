import { MasterFetch } from '/js/promises/MasterFetch.js';

export class Messages {

  async sendMessage(formName, csrfToken, doctorId, object, content) {

    const data = {
      formName: formName.value,
      csrfToken: csrfToken.value,
      doctorId: doctorId.value,
      object: object.value,
      content: content.value
    }

    const response = await MasterFetch.call('sendMessageSecretary', data);
    if (!response || response.error) return;

    const formNew = document.getElementById('formSecretarySendMessage');
    if (formNew) formNew.reset();
  }

  async loadReceivedMessages(container) {
    const response = await MasterFetch.call('getSecretaryMessage');
    if (!response || response.error) {
      container.innerHTML = `<tr><td colspan="4" class="text-center text-muted fst-italic">Aucun message reçu</td></tr>`;
      return;
    }

    container.innerHTML = '';
    const modalsContainer = document.getElementById('modalsContainer');
    modalsContainer.innerHTML = ''; // Vide modales existantes

    response.forEach(msg => {
      // Ligne tableau
      const tr = document.createElement('tr');
      tr.innerHTML = `
      <td>${new Date(msg.date).toLocaleDateString('fr-FR')}</td>
      <td>${msg.sender}</td>
      <td>${msg.subject}</td>
      <td>
        <button
          class="btn btn-sm btn-outline-primary"
          data-bs-toggle="modal"
          data-bs-target="#modalMsg${msg.senderid}"
          title="Voir / Répondre">
          <i class="fas fa-eye"></i> Voir
        </button>
      </td>
    `;
      container.appendChild(tr);

      // Modale associée
      // Modale associée
      const modalDiv = document.createElement('div');
      modalDiv.classList.add('modal', 'fade');
      modalDiv.id = `modalMsg${msg.senderid}`;
      modalDiv.tabIndex = -1;
      modalDiv.setAttribute('aria-labelledby', `modalMsg${msg.senderid}Label`);
      modalDiv.setAttribute('aria-hidden', 'true');

      modalDiv.innerHTML = `
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMsg${msg.senderid}Label">
          Message de ${msg.sender} - ${new Date(msg.date).toLocaleString('fr-FR')}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Objet :</label>
          <p class="fw-semibold">${msg.subject}</p>
        </div>
        <div>
          <label class="form-label">Message :</label>
          <p>${msg.content}</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
`;
      modalsContainer.appendChild(modalDiv);

    });
  }

}