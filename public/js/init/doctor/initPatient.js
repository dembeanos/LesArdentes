
import { Patients } from '/js/classes/doctor/Patients.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';

(async function initPatients() {
  
  const patientManager = new Patients();
  await patientManager.init();

  const formNew = document.getElementById('formDoctorNewPatient');
  if (formNew) {
    formNew.addEventListener('submit', async e => {
  e.preventDefault();
  
    const csrf = new CsrfManager('formDoctorNewPatient');
    await csrf.prepare(); 

    const data = {
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
      formName: document.getElementById('formDoctorNewPatient_formName').value,
      csrfToken: document.getElementById('formDoctorNewPatient_csrfToken').value
    };

    await patientManager.addNewPatient(data);
 
});
  }

  
  const tbody = document.getElementById('patients-tbody-global');

})();