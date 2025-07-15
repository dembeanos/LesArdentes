import { AppointmentCounter } from '../../classes/secretary/AppointmentCounter.js';
import { UnreadMessages } from '../../classes/secretary/UnreadMessages.js';

const rdvCountElem = document.getElementById('rdvCount');
const unreadMessageElem = document.getElementById('unreadMessage');

if (rdvCountElem) {
  const totalRdv = new AppointmentCounter();
  totalRdv.init().then(count => {
    if (count !== null) rdvCountElem.textContent = count;
  });
}

if (unreadMessageElem) {
  const unread = new UnreadMessages();
  unread.init().then(count => {
    if (count !== null) unreadMessageElem.textContent = count;
  });
}
