import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';
import { Consultation } from '/Ardentes/public/js/classes/doctor/Consultation.js';

export class TodaysSchedule {
  constructor() {
    this.tbody = document.getElementById('today-appointments-tbody');
    this.appointments = [];
    this.modalElement = null;
    this.modalInstance = null;
  }

  // Charge les RDV du jour
  async loadTodaysAppointments() {
    const data = await MasterFetch.call('getTodaysAppointments');
    this.appointments = Array.isArray(data) ? data.map(a => ({ ...a, _date: new Date(a.appointmentdate) })) : [];
    this.renderTable(this.appointments);
    this.attachButtonListeners();
  }

  // Affiche le tableau
  renderTable(list) {
    if (!this.tbody) return;
    this.tbody.innerHTML = list.length ? '' : `<tr><td colspan="5" class="text-center fst-italic">Aucun rendez-vous aujourd'hui</td></tr>`;
    list.forEach(app => {
      const time = app._date.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
      const actions = this.getActionButtons(app);
      this.tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>${time}</td>
          <td>${app.patientname}</td>
          <td>${app.reason}</td>
          <td><span class="badge ${this.getBadgeClass(app.status)}">${this.getStatusText(app.status)}</span></td>
          <td class="text-center">${actions}</td>
        </tr>
      `);
    });
  }

  // Génère les boutons selon le status
  getActionButtons(app) {
  const id = app.appointmentid;
  const patientId = app.patientid;
  let btns = '';
  if (app.status === 'reserved') {
    btns += `<button class="btn btn-sm btn-success me-1" data-action="start" data-id="${id}" title="Démarrer"><i class="fas fa-play"></i></button>`;
  }
  if (app.status === 'progress') {
    btns += `<button class="btn btn-sm btn-success me-1" data-action="end" data-id="${id}" title="Terminer"><i class="fas fa-check"></i></button>`;
  }
  if (['reserved','progress'].includes(app.status)) {
    btns += `<button class="btn btn-sm btn-warning me-1" data-action="absent" data-id="${id}" title="Absent"><i class="fas fa-user-slash"></i></button>`;
  }
  // Ajoute le patientId dans le bouton consult
if (patientId) {
  btns += `<button class="btn btn-sm btn-success me-1" data-action="consult" data-id="${id}" data-patientid="${patientId}" title="Consultation"><i class="fas fa-stethoscope"></i></button>`;
}
  return btns;
}


  // Écoute des actions
attachButtonListeners() {
  this.tbody.onclick = async e => {
    e.preventDefault();
    const btn = e.target.closest('button');
    if (!btn) return;
    const act = btn.dataset.action;
    const appointmentId = btn.dataset.id;
const patientId = btn.dataset.patientid;

    switch(act) {
      case 'start': await MasterFetch.call('startConsultation', { appointmentId }); break;
      case 'end': await MasterFetch.call('endConsultation', { appointmentId }); break;
      case 'absent': await MasterFetch.call('markAbsent', { appointmentId }); break;
case 'consult':
  if (patientId && patientId !== 'undefined') {
    new Consultation(appointmentId, patientId).openModal();
  } else {
    console.error('❌ patientId est invalide pour consultation', { appointmentId, patientId });
  }
  break;
    }
    await this.loadTodaysAppointments();
  };
}



  getBadgeClass(status) {
    const map = {
      reserved: 'bg-info',
      progress: 'bg-primary',
      ended: 'bg-success',
      absent: 'bg-secondary',
      canceled: 'bg-danger'
    };
    return map[status] || 'bg-light text-dark';
  }

  getStatusText(status) {
    const labels = {
      reserved: 'Réservé',
      progress: 'En cours',
      ended: 'Terminé',
      absent: 'Absent',
      canceled: 'Annulé',
      passed: 'Retard'
    };
    return labels[status] || status;
  }
}
