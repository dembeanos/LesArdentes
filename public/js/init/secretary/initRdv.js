import { Doctors } from '/js/classes/secretary/Doctors.js';
import { Patients } from '/js/classes/secretary/Patients.js';
import { Appointment } from '/js/classes/secretary/Appointment.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';

//Get la liste de tout les medecins du cabinet pour incrémentation des select généraux
const doctorSelect = document.getElementById('doctorSelect');
if (doctorSelect) {
  const doctorList = new Doctors();
  doctorList.generateOptions().then(fragment => {
    if (fragment) doctorSelect.appendChild(fragment);
  });
}

// Recherche de patient avec son ou ses medecin/s associé/s et enregistrement nouveau rdv
const modal = document.getElementById('modalAddRdv');
if (modal) {
  
  modal.addEventListener('shown.bs.modal', () => {
    const searchInput = document.getElementById('searchPatientWithDoctors');
    const patientList = document.getElementById('patientListAddRdv');
    const doctorSelect = document.getElementById('doctorListAddRdv');
    const patientIdInput = document.getElementById('selectedPatientId');
    const patientManager = new Patients();

    searchInput.addEventListener('input', () => {
      console.log('Recherche déclenchée avec :', searchInput.value);
      patientManager.searchPatientWithDoctors(searchInput, patientList, doctorSelect, patientIdInput);
    });
  });

  //Ajout RDV
  const btnSaveRdv = document.getElementById('saveRdv');
  if (btnSaveRdv) {
    btnSaveRdv.addEventListener('click', async (event) => {
      event.preventDefault();
      
       const csrf = new CsrfManager('formSecretaryAddRdv');
       await csrf.prepare();
      
      const formName = document.getElementById('formSecretaryAddRdv_formName');
      const csrfToken = document.getElementById('formSecretaryAddRdv_csrfToken');
      const patient = document.getElementById('selectedPatientId');
      const doctor = document.getElementById('doctorListAddRdv');
      const date = document.getElementById('rdv-date');
      const hour = document.getElementById('rdv-heure');
      const reason = document.getElementById('rdv-type');
      const comment = document.getElementById('rdv-notes');

      console.log(csrfToken.value)
      const appointment = new Appointment();

      appointment.addAppointment(formName, csrfToken, patient, doctor, date, hour, reason, comment);

       const modalInstance = bootstrap.Modal.getInstance(modal);
    modalInstance.hide();

    await appointment.getAllAppointment();
    appointment.attachButtonListeners();
    });
  }
}



  const appointment = new Appointment();
  await appointment.getAllAppointment();
  appointment.attachButtonListeners();




