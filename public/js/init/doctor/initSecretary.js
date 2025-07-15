import { Messages } from '../../classes/doctor/Messages.js';
import { CsrfManager } from '/Ardentes/public/js/classes/CsrfManager.js';


  const formSend = document.getElementById('formSendMessage');
  const btnSend = document.getElementById('sendMessageButton');
  if (btnSend) {
    btnSend.addEventListener('click', async e => {
      const csrf = new CsrfManager('formSendMessage');
      await csrf.prepare();

      const data = {
        formName:   'formSendMessage',
        csrfToken:  document.getElementById('formSendMessage_csrfToken')?.value || '',
        object:     document.getElementById('cs-sujet').value.trim(),
        content:    document.getElementById('cs-message').value.trim(),
      };
      const mgr = new Messages();
      await mgr.sendMessage(data);
      formSend.reset();
    });
  }

  // 2️⃣ Chargement de l’inbox à l’ouverture de l’onglet
  const inboxTab = document.getElementById('cs-inbox-tab');
  const tableBody = document.querySelector('#cs-inbox tbody');
  const collapseContainer = document.getElementById('cs-messages-collapse');
  if (inboxTab && tableBody && collapseContainer) {
    inboxTab.addEventListener('shown.bs.tab', async () => {
      const mgr = new Messages();
      await mgr.loadReceivedMessages(tableBody, collapseContainer);
    });
  }

