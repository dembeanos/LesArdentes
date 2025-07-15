export class Popup {
  constructor(input = [], popup = [], console = [], modale = null) {
    this.input = input ?? [];
    this.popup = popup ?? [];
    this.console = console ?? [];
    this.modale = modale;
  }

  showInputMessages() {
    this.input.forEach(msg => {
      const field = document.querySelector(`[name="${msg.field}"]`);
      if (!field) {
        console.warn(`Champ "${msg.field}" introuvable`);
        return;
      }

      
      const oldMsgs = field.parentNode.querySelectorAll('.input-error, .input-ok');
      oldMsgs.forEach(el => el.remove());

      const div = document.createElement('div');
      div.className = msg.type === 'error' ? 'input-error' : 'input-ok';
      div.innerText = msg.text;
      field.parentNode.appendChild(div);

      
      setTimeout(() => {
        div.classList.add('fade-out');
        setTimeout(() => div.remove(), 500);
      }, 4000);
    });
  }

  showPopupMessages() {
    this.popup.forEach(msg => {
      const notif = document.createElement('div');
      notif.className = 'popup-toast';
      notif.innerText = msg;

      document.body.appendChild(notif);

      setTimeout(() => {
        notif.classList.add('fade-out');
        setTimeout(() => notif.remove(), 500);
      }, 4000);
    });
  }

  showConsoleMessages() {
    const messages = Array.isArray(this.console) ? this.console : [this.console];
    messages.forEach(msg =>
      console.log('%c[Console] ' + msg, 'color: #0080ff; font-weight: bold;')
    );
  }

  showModale(content, title = 'Information') {
    const overlay = document.createElement('div');
overlay.className = 'modale-overlay';

const modale = document.createElement('div');
modale.className = 'modale-window';

const closeId = `modale-close-${Date.now()}`;

modale.innerHTML = `
  <div class="modale-header">
    <h2>${title}</h2>
  </div>
  <div class="modale-body">${content}</div>
  <div class="modale-footer">
    <button class="btn btn-primary" id="${closeId}">Fermer</button>
  </div>
`;

overlay.appendChild(modale);
document.body.appendChild(overlay);


document.getElementById(closeId).onclick = () => overlay.remove();
  }

  run() {
    if (this.input.length) this.showInputMessages();
    if (this.popup.length) this.showPopupMessages();
    if (this.console.length) this.showConsoleMessages();
    if (this.modale) this.showModale(this.modale);
  }
}
