import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';

export class OpenHours {

  async sendOpenHours(formName, csrfToken, morningStart, morningEnd, afternoonStart, afternoonEnd, closedWeekdays, closedDays) {

    const data = {
      formName,
      csrfToken,
      morningStart,
      morningEnd,
      afternoonStart,
      afternoonEnd,
      closedWeekdays, // [0, 6] (dimanche et samedi)
      closedDays      // "14/07/2025, 25/12/2025"
    };

    const response = await MasterFetch.call('updateOpenHours', data);
    if (!response || response.error) return;

    const form = document.getElementById('formSecretaryChangeOpenHours');
    if (form) form.reset();
  }

}
