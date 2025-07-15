import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';


export class DoctorAppointments {
  static _appointments = [];

  // 🔹 Récupère et affiche les rendez-vous
  static async getAllRdv(tbody) {
    const resp = await MasterFetch.call('getAppointments');
    DoctorAppointments._appointments = resp.map(app => ({
      appointmentId: app.appointmentid,
      patientId: app.patientid,
      date: app.date_fr,
      time: app.time_fr,
      patientName: app.patientname,
      type: app.type,
      comment: app.comment || '',
      status: app.status,
      statusBadge: DoctorAppointments.getStatusBadge(app.status)
    }));

    DoctorAppointments.renderAppointmentTable(tbody);
  }

  // 🔹 Génère le tableau HTML
  static renderAppointmentTable(tbody) {
    tbody.innerHTML = '';
    if (!DoctorAppointments._appointments.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center fst-italic">Aucun rendez-vous</td></tr>`;
      return;
    }

    DoctorAppointments._appointments.forEach(app => {
      const tr = document.createElement('tr');
      tr.dataset.appointmentid = app.appointmentId;
      tr.dataset.patientid = app.patientId;
      tr.dataset.comment = app.comment || '';

      tr.innerHTML = `
        <td>${app.date}</td>
        <td>${app.time}</td>
        <td>${app.patientName}</td>
        <td>${app.type}</td>
        <td><span class="badge bg-${app.statusBadge}">${DoctorAppointments.getStatusText(app.status)}</span></td>
        <td>
          <button type='button' class="btn btn-sm btn-outline-success me-1" data-action="confirm" title="Confirmer">
            <i class="fas fa-check"></i>
          </button>
          <button type='button' class="btn btn-sm btn-outline-danger me-1" data-action="cancel" title="Annuler">
            <i class="fas fa-times"></i>
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            data-action="edit"
            data-bs-toggle="modal"
            data-bs-target="#modalModifyAppointment"
            title="Modifier">
            <i class="fas fa-edit"></i>
          </button>
        </td>
      `;

      tbody.appendChild(tr);
    });
  }

  // 🔹 Changement de statut
  static async updateAppointmentStatus(data, tbody) {

    const res = await MasterFetch.call('updateAppointmentStatus', data);

    if (res?.success) {
      await DoctorAppointments.getAllRdv(tbody); // 🔁 on recharge après action
    }
  }

  // 🔹 Ajout RDV
  static async addAppointment(data, tbody) {
    const res = await MasterFetch.call('addAppointmentDoctor', data);
    

    bootstrap.Modal.getInstance(document.getElementById('modalAddAppointment')).hide();
    await DoctorAppointments.getAllRdv(tbody); // 🔁 on recharge
  }

  // 🔹 Modification RDV
  static async modifyAppointment(data, tbody) {
    console.log(data)
    const res = await MasterFetch.call('modifyAppointmentDoctor', data);
   

    bootstrap.Modal.getInstance(document.getElementById('modalModifyAppointment')).hide();
    await DoctorAppointments.getAllRdv(tbody); // 🔁 on recharge
  }

  // 🔹 Couleur du badge
  static getStatusBadge(status) {
    return {
      pending: 'warning text-dark',
      validated: 'success',
      canceled: 'danger'
    }[status] || 'secondary';
  }

  // 🔹 Texte du statut
  static getStatusText(status) {
    return {
      pending: 'À confirmer',
      validated: 'Programmé',
      canceled: 'Annulé'
    }[status] || status;
  }
}
