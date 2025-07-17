import { Doctors } from '/js/classes/secretary/Doctors.js';
import { Patients } from '/js/classes/secretary/Patients.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';

const modalAdd = document.getElementById('modalAddPatientGlobal');
if (modalAdd) {
  modalAdd.addEventListener('shown.bs.modal', async () => {
    const sel = document.getElementById('doctorSelectNwP');
    if (!sel || sel.dataset.loaded === 'true') return;
    const frag = await new Doctors().generateOptions();
    if (frag) {
      sel.appendChild(frag);
      sel.dataset.loaded = 'true';
    }
  });
}

const formNew = document.getElementById('formSecretaryNewPatient');
if (formNew) {
  formNew.addEventListener('submit', async e => {
    e.preventDefault();
    const csrf = new CsrfManager('formSecretaryNewPatient');
    await csrf.prepare();
    const data = {
      doctorId: document.getElementById('doctorSelectNwP').value,
      lastName: document.getElementById('lastName').value.trim(),
      firstName: document.getElementById('firstName').value.trim(),
      birthDate: document.getElementById('birthDate').value,
      gender: document.getElementById('gender').value,
      bloodGroup: document.getElementById('bloodGroup').value,
      phone: document.getElementById('phone').value.trim(),
      email: document.getElementById('email').value.trim(),
      socialSecurity: document.getElementById('socialSecurity').value.trim(),
      address: document.getElementById('address').value.trim(),
      allergy: document.getElementById('allergy').value.trim(),
      medicalHistory: document.getElementById('medicalHistory').value.trim(),
      formName: formNew.querySelector('#formSecretaryNewPatient_formName').value,
      csrfToken: formNew.querySelector('#formSecretaryNewPatient_csrfToken').value
    };
    await patientManager.addNewPatient(data);
  });
}

const patientManager = new Patients();
await patientManager.getAllPatients();

const tbody = document.getElementById('patients-tbody-global');
if (tbody) {
  tbody.addEventListener('click', e => {
    const btn = e.target.closest('button[title="Modifier"]');
    if (!btn) return;
    patientManager.fillEditModal(btn.dataset);
  });
}
const modalEdit = document.getElementById('modalEditPatient');
const formEdit = document.getElementById('formSecretaryEditPatient');

if (modalEdit && formEdit) {
  formEdit.addEventListener('submit', async e => {
    e.preventDefault();

    const csrf = new CsrfManager('formSecretaryEditPatient');
    await csrf.prepare();

    const data = {
      patientId: document.getElementById('edit-patient-id').value,
      lastName: document.getElementById('edit-patient-nom').value.trim(),
      firstName: document.getElementById('edit-patient-prenom').value.trim(),
      birthDate: document.getElementById('edit-patient-naissance').value,
      phone: document.getElementById('edit-patient-phone').value.trim(),
      email: document.getElementById('edit-patient-email').value.trim(),
      address: document.getElementById('edit-patient-address').value.trim(),
      formName: document.getElementById('formSecretaryEditPatient_formName').value,
      csrfToken: document.getElementById('formSecretaryEditPatient_csrfToken').value,
    }
    console.log(data)
    await patientManager.updatePatient(data);

    
  });
}

