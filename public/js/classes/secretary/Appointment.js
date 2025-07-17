import { MasterFetch } from '/js/promises/MasterFetch.js';

export class Appointment {

  async addAppointment (formName, csrfToken, patient, doctor, date, hour, reason, comment){

    const appointmentDate = `${date.value} ${hour.value}:00`;

    const data= {
    formName : formName.value,
    csrfToken : csrfToken.value,
    patientId : patient.value,
    doctorId : doctor.value,
    appointmentDate : appointmentDate,
    reason : reason.value,
    comment : comment.value,
    }

    await MasterFetch.call('addAppointment', data);
  }
  // Récupérer tous les RDV et préparer la liste avec un objet Date JS
  async getAllAppointment() {
    const data = await MasterFetch.call('getAllAppointment');
    if (!data || !Array.isArray(data)) return;

    // Ajout d'un objet Date pour simplifier la comparaison
    this.appointments = data.map(app => ({
      ...app,
      _dateObj: new Date(app.appointmentdate)
    }));

    this.renderTable(this.appointments);
    this.attachFilterListeners();
    this.attachButtonListeners();
  }

  // Afficher le tableau selon une liste de RDV
  renderTable(list) {
    const tbody = document.getElementById('rdv-tbody');
    tbody.innerHTML = '';

    if (list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center fst-italic">Aucun rendez-vous trouvé</td></tr>`;
      return;
    }

    list.forEach(app => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${app.patientname}</td>
        <td>${this.formatDate(app._dateObj)} ${this.formatTime(app._dateObj)}</td>
        <td>${app.doctorname}</td>
        <td class="fw-bold ${this.getStatusClass(app.status)}">${this.getStatusText(app.status)}</td>
        <td class="text-center">
          ${this.getActionButtons(app)}
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Gestion des filtres + mise à jour du tableau
  attachFilterListeners() {
    const byName = document.getElementById('search-rdv');
    const byDate = document.getElementById('filter-date');
    const byDoc = document.getElementById('doctorSelect');
    const byStatus = document.getElementById('filter-status');

    const filterAndRender = () => {
      let filtered = this.appointments;

      if (byName.value) {
        const v = byName.value.trim().toLowerCase();
        filtered = filtered.filter(a => a.patientname.toLowerCase().includes(v));
      }

      if (byDate.value) {
        const targetDate = new Date(byDate.value);
        filtered = filtered.filter(a => a._dateObj.toDateString() === targetDate.toDateString());
      }

      if (byDoc.value) {
        filtered = filtered.filter(a => a.doctorname === byDoc.value);
      }

      if (byStatus.value) {
        filtered = filtered.filter(a => this.getStatusText(a.status) === byStatus.value);
      }

      this.renderTable(filtered);
    };

    [byName, byDate, byDoc, byStatus].forEach(el => el.addEventListener('input', filterAndRender));
  }

  // Boutons d’action selon le status du rdv, sans bouton Modifier
  getActionButtons(app) {
    const id = app.appointmentid;
    switch (app.status) {
      case 'reserved':
      case 'pending':
        return `
          <button class="btn btn-success btn-sm me-1" data-id="${id}" title="Valider">Valider</button>
          <button class="btn btn-danger btn-sm me-1" data-id="${id}" title="Annuler">Annuler</button>
        `;
      case 'progress':
        return `
          <button class="btn btn-success btn-sm me-1" data-id="${id}" title="Clôturer">Clôturer</button>
          <button class="btn btn-danger btn-sm me-1" data-id="${id}" title="Annuler">Annuler</button>
        `;
      case 'ended':
      case 'passed':
        return `<button class="btn btn-warning btn-sm me-1" data-id="${id}" title="Absent">Absent</button>`;
      case 'canceled':
        return `<span class="text-muted">Annulé</span>`;
      default:
        return '—';
    }
  }

  // Format date et heure
  formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('fr-FR');
  }

  formatTime(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
  }

  // Classes CSS des statuts pour affichage
  getStatusClass(status) {
    switch (status) {
      case 'pending': return 'text-warning';
      case 'validated': return 'text-success';
      case 'canceled': return 'text-danger';
      case 'reserved': return 'text-success';
      case 'passed': return 'text-secondary';
      case 'ended': return 'text-muted';
      case 'absent': return 'text-warning';
      default: return '';
    }
  }

  // Texte affiché selon le status
  getStatusText(status) {
    switch (status) {
      case 'pending': return 'En attente';
      case 'validated': return 'Validé';
      case 'canceled': return 'Annulé';
      case 'reserved': return 'Réservé';
      case 'passed': return 'Passé';
      case 'ended': return 'Clôturé';
      case 'absent': return 'Absent';
      default: return status;
    }
  }

  attachButtonListeners() {
    document.getElementById('rdv-tbody').addEventListener('click', async e => {
      const btn = e.target.closest('button');
      if (!btn) return;
      const id = btn.dataset.id;

      switch (btn.title) {
        case 'Valider':
          await this.validateAppointment(id);
          break;
        case 'Annuler':
          await this.cancelAppointment(id);
          break;
        case 'Clôturer':
          await this.closeAppointment(id);
          break;
        case 'Absent':
          await this.absentAppointment(id);
          break;
      }
    });
  }

  async validateAppointment(id) {
    const data = { appointmentId: id, status: 'progress' };
    const response = await MasterFetch.call('changeAppointmentStatus', data);
    if (response && !response.error) await this.getAllAppointment();
  }

  async cancelAppointment(id) {
    const data = { appointmentId: id, status: 'canceled' };
    const response = await MasterFetch.call('changeAppointmentStatus', data);
    if (response && !response.error) await this.getAllAppointment();
  }

  async closeAppointment(id) {
    const data = { appointmentId: id, status: 'ended' };
    const response = await MasterFetch.call('changeAppointmentStatus', data);
    if (response && !response.error) await this.getAllAppointment();
  }

  async absentAppointment(id) {
    const data = { appointmentId: id, status: 'absent' };
    const response = await MasterFetch.call('changeAppointmentStatus', data);
    if (response && !response.error) await this.getAllAppointment();
  }
}
