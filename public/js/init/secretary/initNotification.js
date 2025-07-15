import { Messages } from '../../classes/secretary/Messages.js';
import { Doctors } from '../../classes/secretary/Doctors.js';
import { CsrfManager } from '/Ardentes/public/js/classes/CsrfManager.js';

const doctorSelect = document.getElementById('doctorSelectMessage');
if (doctorSelect) {
  const doctorList = new Doctors();
  doctorList.generateOptions().then(fragment => {
    if (fragment) {
      doctorSelect.appendChild(fragment);
    }
  });
}

const btnSend = document.getElementById('sendMessage');

btnSend.addEventListener('click', async (e)=> {
    e.preventDefault()

    const csrf = new CsrfManager('formSecretarySendMessage');
    await csrf.prepare();

    const formName = document.getElementById('formSecretarySendMessage_formName');
    const  csrfToken = document.getElementById('formSecretarySendMessage_csrfToken');
    const doctorId = document.getElementById('doctorSelectMessage');
    const object = document.getElementById('cs-sujet'); 
    const content = document.getElementById('cs-message');

    const message = new Messages();
    message.sendMessage(formName, csrfToken,doctorId, object, content)
});

const inboxTab = document.getElementById('cs-inbox-tab');
const messageListTbody = document.getElementById('receivedMessagelist');

if (inboxTab && messageListTbody) {
  inboxTab.addEventListener('shown.bs.tab', async () => {
    const messageManager = new Messages();
    await messageManager.loadReceivedMessages(messageListTbody);
  });
}
