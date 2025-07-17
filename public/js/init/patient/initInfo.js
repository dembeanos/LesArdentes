import { Patient } from "/js/classes/patient/Patient.js";
import { CsrfManager} from "/js/classes/CsrfManager.js";

const formPatient = document.getElementById('formPatientInfo');
const formName = document.getElementById("formPatientInfo_formName");
const csrfToken = document.getElementById("formPatientInfo_csrfToken");
const lastName = document.getElementById("info-lastname");
const firstName = document.getElementById("info-firstname");
const email = document.getElementById("info-email");
const phone = document.getElementById("info-phone");
const address = document.getElementById("info-address");

if (lastName && firstName && email && phone && address) {
  formPatient.reset();

  const fields = { lastName, firstName, email, phone, address };
  const patient = new Patient();
  patient.getPatientInfo(fields)
}

formPatient.addEventListener('submit', async(e)=>{

    e.preventDefault();

    const csrf = new CsrfManager('formPatientInfo');
    await csrf.prepare();

    const data = {
        formName: formName.value,
        csrfToken : csrfToken.value,
        phone : phone.value,
        address : address.value,
    }

    const patient = new Patient();
    await patient.updatePatientInfo(data);

})
