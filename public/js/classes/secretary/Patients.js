import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';
import { Popup } from '/Ardentes/public/js/components/Popup.js';

export class Patients {
  constructor() {
    this.patients = [];
    this.init();
  }

  async init() {
    
    await this.getAllPatients();
    
    this.attachNewPatientFormListener();
  }

  // ------------------------------------
  // Méthode d'ajout d'un nouveau patient
  // ------------------------------------
  async addNewPatient(data) {
    const response = await MasterFetch.call('addPatient', data);
    if (!response || response.error) return;

    const formNew = document.getElementById('formSecretaryNewPatient');
    if (formNew) formNew.reset();

    const content = `
      <p>Patient créé avec succès !</p>
      <p><strong>Login :</strong> ${response.login}</p>
      <p><strong>Mot de passe :</strong> ${response.password}</p>
    `;
    new Popup([], [], [], content).run();

    
    await this.getAllPatients();
  }

  // ------------------------------------
  // Recherche patient + suggestions
  // ------------------------------------
  async searchPatientWithDoctors(searchInput, container, doctorSelect, hiddenInput) {
    const term = searchInput.value.trim();
    if (term.length < 3) {
      this.reset(container, doctorSelect, hiddenInput);
      return;
    }

    const response = await MasterFetch.call('searchPatientWithDoctor', { term });
    if (!response || response.error) {
      this.reset(container, doctorSelect, hiddenInput);
      return;
    }

    this.renderSuggestions(response, container, searchInput, doctorSelect, hiddenInput);
  }

  renderSuggestions(results, container, input, doctorSelect, hiddenInput) {
    container.innerHTML = '';
    container.classList.add('dropdown-menu', 'show');
    container.style.position = 'absolute';
    container.style.width = input.offsetWidth + 'px';

    if (!hiddenInput) {
      hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.id = 'selectedPatientId';
      hiddenInput.name = 'patientId';
      input.parentNode.appendChild(hiddenInput);
    }

    if (!results.length) {
      container.innerHTML = '<button class="dropdown-item disabled">Aucun patient trouvé</button>';
      this.resetDoctors(doctorSelect, hiddenInput);
      return;
    }

    results.forEach(patient => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'dropdown-item';
      btn.textContent = patient.patient_fullname;
      btn.addEventListener('click', () => {
        input.value = patient.patient_fullname;
        hiddenInput.value = patient.patientid;
        this.fillDoctors(patient.doctors, doctorSelect);
        container.classList.remove('show');
      });
      container.appendChild(btn);
    });
  }

  fillDoctors(doctors, doctorSelect) {
    this.resetDoctors(doctorSelect);
    doctors.forEach(doc => {
      const opt = document.createElement('option');
      opt.value = doc.doctorId;
      opt.textContent = doc.doctorName;
      doctorSelect.appendChild(opt);
    });
  }

  resetDoctors(doctorSelect, hiddenInput = null) {
    doctorSelect.innerHTML = '<option value="" disabled selected>-- Choisir un médecin --</option>';
    if (hiddenInput) hiddenInput.value = '';
  }

  reset(container, doctorSelect, hiddenInput = null) {
    container.innerHTML = '';
    container.classList.remove('show');
    this.resetDoctors(doctorSelect, hiddenInput);
  }

  // ------------------------------------
  // Chargement et rendu de la liste
  // ------------------------------------
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

  renderPatientTable(list) {
    const tbody = document.getElementById('patients-tbody-global');
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
            data-patient-id="${p.patientid}"
            data-patient-nom="${p.lastname}"
            data-patient-prenom="${p.firstname}"
            data-patient-naissance="${p.birthdate}"
            data-patient-phone="${p.phone || ''}"
            data-patient-email="${p.email || ''}"
            data-patient-address="${p.address || ''}">
            <i class="fas fa-eye"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary me-1"
            title="Modifier"
            data-bs-toggle="modal"
            data-bs-target="#modalEditPatient"
            data-patient-id="${p.patientid}"
            data-patient-nom="${p.lastname}"
            data-patient-prenom="${p.firstname}"
            data-patient-naissance="${p.birthdate}"
            data-patient-phone="${p.phone || ''}"
            data-patient-email="${p.email || ''}"
            data-patient-address="${p.address || ''}">
            <i class="fas fa-edit"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  attachPatientFilter() {
    const input = document.getElementById('search-patient-global');
    if (!input) return;

    input.oninput = () => {
      const v = input.value.trim().toLowerCase();
      const filtered = this.patients.filter(p =>
        p.firstname.toLowerCase().includes(v) ||
        p.lastname.toLowerCase().includes(v) ||
        String(p.id).includes(v)
      );
      this.renderPatientTable(filtered);
    };
  }

  attachPatientButtonListeners() {
    const tbody = document.getElementById('patients-tbody-global');
    if (!tbody) return;

    tbody.addEventListener('click', e => {
      e.preventDefault();
      const btn = e.target.closest('button');
      if (!btn) return;
      const data = btn.dataset;

      if (btn.title === 'Voir') this.fillViewModal(data);
      if (btn.title === 'Modifier') this.fillEditModal(data);
    });
  }

  fillViewModal(data) {
    document.getElementById('view-patient-nom').textContent = data.patientNom;
    document.getElementById('view-patient-prenom').textContent = data.patientPrenom;
    document.getElementById('view-patient-naissance').textContent = new Date(data.patientNaissance).toLocaleDateString('fr-FR');
    document.getElementById('view-patient-phone').textContent = data.patientPhone || '—';
    document.getElementById('view-patient-email').textContent = data.patientEmail || '—';
    document.getElementById('view-patient-address').textContent = data.patientAddress || '—';
  }

  fillEditModal(data) {
    document.getElementById('edit-patient-nom').value = data.patientNom;
    document.getElementById('edit-patient-id').value = data.patientId;
    document.getElementById('edit-patient-prenom').value = data.patientPrenom;
    document.getElementById('edit-patient-naissance').value = data.patientNaissance;
    document.getElementById('edit-patient-phone').value = data.patientPhone || '';
    document.getElementById('edit-patient-email').value = data.patientEmail || '';
    document.getElementById('edit-patient-address').value = data.patientAddress || '';
  }

  // ------------------------------------
  // Liaison form d'ajout patient
  // ------------------------------------
  attachNewPatientFormListener() {
    const form = document.getElementById('formSecretaryNewPatient');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      await this.addNewPatient(data);
    });
  }


   async updatePatient(data) {
    console.log('donnée envoyé:', data)
    const response = await MasterFetch.call('updatePatientSecretary', data);
    if (!response || response.error) {
      return false;
    }
    return true;
  }

}
