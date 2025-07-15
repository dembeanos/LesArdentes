import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';
import { Popup } from '/Ardentes/public/js/components/Popup.js';
import { CsrfManager } from '/Ardentes/public/js/classes/CsrfManager.js';

export class Patients {
  constructor() {
    this.patients = [];
    this.init();
  }

  // 🔹 Format français d'une date
  formatDate(dateString) {
    if (!dateString) return '—';
    const d = new Date(dateString);
    if (isNaN(d)) return '—';
    return d.toLocaleDateString('fr-FR');
  }

  // 🔹 Init de la classe
  async init() {
    await this.getAllPatients();
    this.attachEditPatientFormSubmit();
  }

  // 🔹 Ajout d’un nouveau patient
  async addNewPatient(data) {
    const response = await MasterFetch.call('addPatientDoctor', data);
    if (!response || response.error) return;

    const formNew = document.getElementById('formDoctorNewPatient');
    if (formNew) formNew.reset();

    const content = `
      <p>Patient créé avec succès !</p>
      <p><strong>Login :</strong> ${response.login}</p>
      <p><strong>Mot de passe :</strong> ${response.password}</p>
    `;
    new Popup([], [], [], content).run();

    await this.getAllPatients();
  }

  // 🔹 Récupération de tous les patients
  async getAllPatients() {
    const data = await MasterFetch.call('getPatient');
    if (!data || !Array.isArray(data)) return;

    this.patients = data.map(p => ({
      ...p,
      _dateObj: p.birthdate ? new Date(p.birthdate) : null
    }));

    this.renderPatientTable(this.patients);
    this.attachPatientFilter();
    this.attachPatientButtonListeners();
  }

  // 🔹 Rendu du tableau patients
  renderPatientTable(list) {
    const tbody = document.getElementById('listOfPatient');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!list.length) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center fst-italic">Aucun patient trouvé</td></tr>`;
      return;
    }

    list.forEach(p => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p.lastname}</td>
        <td>${p.firstname}</td>
        <td>${p._dateObj ? p._dateObj.toLocaleDateString('fr-FR') : ''}</td>
        <td>${p.phone || '—'}</td>
        <td>${p.email || '—'}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-primary me-1"
            title="Voir"
            data-bs-toggle="modal"
            data-bs-target="#modalViewPatient"
            data-patientid="${p.patientid}"
            data-patient-nom="${p.lastname}"
            data-patient-prenom="${p.firstname}"
            data-patient-naissance="${p.birthdate}"
            data-patient-phone="${p.phone || ''}"
            data-patient-email="${p.email || ''}"
            data-patient-address="${p.address || ''}"
            data-patient-allergy="${p.allergy || ''}"
            data-patient-blood-group="${p.blood_group || ''}"
            data-patient-gender="${p.gender || ''}"
            data-patient-medical-history="${p.medical_history || ''}"
            data-patient-social-security="${p.social_security || ''}">
            <i class="fas fa-eye"></i>
          </button>

          <button class="btn btn-sm btn-outline-secondary me-1"
            title="Modifier"
            data-bs-toggle="modal"
            data-bs-target="#modalEditPatient"
            data-patientid="${p.patientid}"
            data-patient-nom="${p.lastname}"
            data-patient-prenom="${p.firstname}"
            data-patient-naissance="${p.birthdate}"
            data-patient-phone="${p.phone || ''}"
            data-patient-email="${p.email || ''}"
            data-patient-address="${p.address || ''}"
            data-patient-allergy="${p.allergy || ''}"
            data-patient-blood-group="${p.blood_group || ''}"
            data-patient-gender="${p.gender || ''}"
            data-patient-medical-history="${p.medical_history || ''}"
            data-patient-social-security="${p.social_security || ''}">
            <i class="fas fa-edit"></i>
          </button>

          <button class="btn btn-sm btn-warning me-1"
            title="Accès sécurisé"
            data-action="lock"
            data-patientid="${p.patientid}">
            <i class="fas fa-key"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // 🔹 Filtrage live sur input
  attachPatientFilter() {
    const input = document.getElementById('search-patient-global');
    if (!input) return;

    input.addEventListener('input', () => {
      const v = input.value.trim().toLowerCase();
      const filtered = this.patients.filter(p =>
        p.firstname.toLowerCase().includes(v) ||
        p.lastname.toLowerCase().includes(v) ||
        String(p.patientid).includes(v)
      );
      this.renderPatientTable(filtered);
    });
  }

  // 🔹 Boutons Voir / Modifier / Accès sécurisé
  attachPatientButtonListeners() {
    const tbody = document.getElementById('listOfPatient');
    if (!tbody) return;

    tbody.addEventListener('click', e => {
      e.preventDefault();
      const btn = e.target.closest('button');
      if (!btn) return;
      const data = btn.dataset;

      if (btn.title === 'Voir') return this.fillViewModal(data);
      if (btn.title === 'Modifier') return this.fillEditModal(data);
      if (data.action === 'lock') {
        const payload = {
          patientId: data.patientid
        }
        this.generateAccess(payload);
        return;
      }
    });
  }

  // 🔹 Affiche les infos dans la modale "Voir"
  fillViewModal(data) {
    document.getElementById('view-patient-nom').textContent = data.patientNom || '—';
    document.getElementById('view-patient-prenom').textContent = data.patientPrenom || '—';
    document.getElementById('view-patient-naissance').textContent = this.formatDate(data.patientNaissance);
    document.getElementById('view-patient-phone').textContent = data.patientPhone || '—';
    document.getElementById('view-patient-email').textContent = data.patientEmail || '—';
    document.getElementById('view-patient-address').textContent = data.patientAddress || '—';
    document.getElementById('view-patient-allergy').textContent = data.patientAllergy || '—';
    document.getElementById('view-patient-blood-group').textContent = data.patientBloodGroup || '—';
    document.getElementById('view-patient-gender').textContent = data.patientGender || '—';
    document.getElementById('view-patient-medical-history').textContent = data.patientMedicalHistory || '—';
    document.getElementById('view-patient-social-security').textContent = data.patientSocialSecurity || '—';
  }

  // 🔹 Pré-remplit le formulaire d’édition
  fillEditModal(data) {
    document.getElementById('edit-patient-id').value = data.patientid || '';
    document.getElementById('edit-patient-nom').value = data.patientNom || '';
    document.getElementById('edit-patient-prenom').value = data.patientPrenom || '';
    document.getElementById('edit-patient-naissance').value = data.patientNaissance || '';
    document.getElementById('edit-patient-phone').value = data.patientPhone || '';
    document.getElementById('edit-patient-email').value = data.patientEmail || '';
    document.getElementById('edit-patient-address').value = data.patientAddress || '';
    document.getElementById('edit-patient-allergy').value = data.patientAllergy || '';
    document.getElementById('edit-patient-blood-group').value = data.patientBloodGroup || '';
    document.getElementById('edit-patient-gender').value = data.patientGender || '';
    document.getElementById('edit-patient-medical-history').value = data.patientMedicalHistory || '';
    document.getElementById('edit-patient-social-security').value = data.patientSocialSecurity || '';
  }

  // 🔹 Mise à jour d’un patient

  attachEditPatientFormSubmit() {
    const form = document.getElementById('formEditPatient');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const csrf = new CsrfManager('formDoctorEditPatient');
      await csrf.prepare();

      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      if (data.patientid) {
        data.patientId = data.patientid;
        delete data.patientid;
      }

      const success = await this.updatePatient(data);

      if (success) {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditPatient'));
        if (modal) modal.hide();

        await this.getAllPatients();
      }
    });
  }

  async updatePatient(data) {
    console.log(data)
    const response = await MasterFetch.call('updatePatient', data);
    if (!response || response.error) {
      return false;
    }
    await this.getAllPatients();
    return true;
  }

async generateAccess(data) {
  if (this._generating) return;
  this._generating = true;

  const response = await MasterFetch.call('generateAccess', data);
  if (!response || response.error) {
    this._generating = false;
    return false;
  }

  const content = `
    <p>Accès généré avec succès !</p>
    <p><strong>Login :</strong> ${response.login}</p>
    <p><strong>Mot de passe :</strong> ${response.password}</p>
  `;

  new Popup([], [], [], content).run();

  await this.getAllPatients();

  this._generating = false;
  return true;
}

}
