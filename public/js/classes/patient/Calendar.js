import { MasterFetch } from '/js/promises/MasterFetch.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';

export class Calendar {
  async getList() {
    const result = await MasterFetch.call('getDoctorsPatient');
    return result?.doctors ?? null;
  }

  async generateOptions() {
    const doctors = await this.getList();
    if (!doctors) return null;

    const fragment = document.createDocumentFragment();
    doctors.forEach(doc => {
      const option = document.createElement('option');
      option.value = doc.doctorid;
      option.textContent = doc.fullname ?? 'Médecin inconnu';
      fragment.appendChild(option);
    });

    return fragment;
  }

 async getAvailable(doctorId) {
  const result = await MasterFetch.call('getAvailable', { doctorId });
  if (!result) return [];

  const colorMap = {
    consultation: '#27ae60',
    closed: '#2980b9',
    unavailable: '#c0392b'
  };

  const parseClosedDays = (str) => {
    try {
      return JSON.parse(str || '[]');
    } catch {
      return [];
    }
  };

  const events = [];
  const closed = result.closedOffice || {};
  const closedDays = parseClosedDays(closed.closed_weekdays);
  const openDays = [0,1,2,3,4,5,6].filter(d => !closedDays.includes(d));

  if (typeof closed.closed_days === 'string' && closed.closed_days.length) {
    closed.closed_days.split(',').forEach(date => {
      events.push({
        title: 'Cabinet Fermé',
        start: `${date}T07:00:00`,
        end: `${date}T22:00:00`,
        color: colorMap.closed,
        selectable: false,
        overlap: false
      });
    });
  }

  closedDays.forEach(day => {
    events.push({
      title: 'Fermé',
      daysOfWeek: [day],
      startTime: "07:00:00",
      endTime: "22:00:00",
      color: colorMap.closed,
      selectable: false,
      overlap: false
    });
  });

  const openings = closed.opening_hours || [];
  openDays.forEach(day => {
    let lastEnd = "07:00:00";

    openings.forEach(slot => {
      if (slot.startTime > lastEnd) {
        events.push({
          title: 'Fermé',
          daysOfWeek: [day],
          startTime: lastEnd,
          endTime: slot.startTime,
          color: colorMap.closed,
          selectable: false,
          overlap: false
        });
      }

      events.push({
        title: slot.title || 'Ouvert',
        daysOfWeek: [day],
        startTime: slot.startTime,
        endTime: slot.endTime,
        display: 'background',
        color: colorMap.consultation,
        selectable: true,
        overlap: false
      });

      lastEnd = slot.endTime;
    });

    if (lastEnd < "22:00:00") {
      events.push({
        title: 'Fermé',
        daysOfWeek: [day],
        startTime: lastEnd,
        endTime: "22:00:00",
        color: colorMap.closed,
        selectable: false,
        overlap: false
      });
    }
  });

  if (Array.isArray(result.notAvailable)) {
    result.notAvailable.forEach(evt => {
      events.push({
        title: evt.title || 'Indisponible',
        start: evt.start,
        end: evt.end,
        color: colorMap.unavailable,
        selectable: false,
        overlap: false
      });
    });
  }

  return events;
}



openAppointmentModal(doctorId, startISO) {
  const modalEl = document.getElementById('appointmentModal');
  const modal = new bootstrap.Modal(modalEl);
  const form = document.getElementById('formAddAppointmentPatient');

  const date = new Date(startISO);
  const pad = n => n.toString().padStart(2, '0');
  const formatted = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;

  // Remplir les champs du formulaire
  document.getElementById('appointmentDateTime').value = formatted;
  document.getElementById('appointmentDoctorId').value = doctorId;
  document.getElementById('appointmentStartISO').value = startISO;
  document.getElementById('appointmentMotif').value = '';

  modal.show();

  // On détache tous les submit précédents
  const newForm = form.cloneNode(true);
  form.parentNode.replaceChild(newForm, form);

  // Le handler utilise `this`, donc on le garde fléché
  newForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const csrf = new CsrfManager('formAddAppointmentPatient');
    await csrf.prepare();

    const motif = document.getElementById('appointmentMotif').value.trim();
    if (!motif) return;

    const data = {
      doctorId,
      appointmentDate: formatted,
      reason: motif,
      formName: document.getElementById('formAddAppointmentPatient_formName').value,
      csrfToken: document.getElementById('formAddAppointmentPatient_csrfToken').value
    };

    await MasterFetch.call('takeAppointment', data);

    modal.hide();
    this.getAvailable(doctorId);
    document.getElementById('doctorSelect').dispatchEvent(new Event('change'));

  });
}

}
