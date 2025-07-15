import { CsrfManager } from '/Ardentes/public/js/classes/CsrfManager.js';
import { OpenHours } from '/Ardentes/public/js/classes/secretary/OpenHours.js';

const btnChange = document.getElementById('btnChangeHours');

if (btnChange) {
  btnChange.addEventListener('click', async (e) => {
    e.preventDefault();

    const csrf = new CsrfManager('formSecretaryChangeOpenHours');
    await csrf.prepare();

    const formName = document.getElementById('formSecretaryChangeOpenHours_formName');
    const csrfToken = document.getElementById('formSecretaryChangeOpenHours_csrfToken');
    const morningStart = document.getElementById('opening-time');
    const morningEnd = document.getElementById('closing-time');
    const afternoonStart = document.getElementById('opening-afternoon');
    const afternoonEnd = document.getElementById('closing-afternoon');
    const closedDays = document.getElementById('closed-days');

    const closedWeekdays = [];
    if (document.getElementById('closed-sunday').checked) closedWeekdays.push(0);
    if (document.getElementById('closed-saturday').checked) closedWeekdays.push(6);
    

    const openHours = new OpenHours();
    openHours.sendOpenHours(
      formName.value,
      csrfToken.value,
      morningStart.value,
      morningEnd.value,
      afternoonStart.value,
      afternoonEnd.value,
      closedWeekdays,
      closedDays.value
    );
  });
}
