// Messages.js
import { MasterFetch } from '/js/promises/MasterFetch.js';

export class Messages {
  
    async sendMessage(data){

    const response = await MasterFetch.call('sendMessageToSecretary', data);
    if (!response || response.error) return;

    const formNew = document.getElementById('formSecretarySendMessage');
    if (formNew) formNew.reset();


    }

 
  async loadReceivedMessages(tableBody, collapseContainer) {
    const res = await MasterFetch.call('getMessage');
    const messages = res

    tableBody.innerHTML = '';
    collapseContainer.innerHTML = '';

    if (!Array.isArray(messages) || messages.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="4" class="text-center text-muted fst-italic">
            Aucun message reçu
          </td>
        </tr>`;
      return;
    }

    messages.forEach((msg) => {
      const id = `cs-msg-${msg.id}`;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${new Date(msg.date).toLocaleDateString('fr-FR')}</td>
        <td>${msg.sender}</td>
        <td>${msg.subject}</td>
        <td>
          <button
            class="btn btn-sm btn-outline-primary"
            data-bs-toggle="collapse"
            data-bs-target="#${id}"
            aria-expanded="false"
            aria-controls="${id}"
            title="Voir / Répondre">
            <i class="fas fa-eye"></i> Voir
          </button>
        </td>`;
      tableBody.appendChild(tr);

      // 2) zone collapsible
      const div = document.createElement('div');
      div.className = 'collapse mb-4';
      div.id = id;
      div.innerHTML = `
  <div class="card border rounded">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <div>
        <strong>Expéditeur :</strong> ${msg.sender} —
        <small class="text-muted">
          ${new Date(msg.date).toLocaleDateString('fr-FR')},
          ${new Date(msg.date).toLocaleTimeString('fr-FR')}
        </small>
      </div>
      <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#${id}">
        Fermer
      </button>
    </div>
    <div class="card-body">
      <h5><strong>Objet :</strong></h5>
      <p>${msg.subject}</p>
      <h5><strong>Message :</strong></h5>
      <p>${msg.content.replace(/\n/g, '</p><p>')}</p>
    </div>
  </div>`;

      collapseContainer.appendChild(div);
    });
  }
}
