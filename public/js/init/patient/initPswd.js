import { Patient } from "/js/classes/patient/Patient.js";
import { CsrfManager} from "/js/classes/CsrfManager.js";

const formPswd = document.getElementById("formPatientPswd");
const formName = document.getElementById("formPatientPswd_formName");
const csrfToken = document.getElementById("formPatientPswd_csrfToken");
const currentPswd = document.getElementById("pwd-current");
const newPswd = document.getElementById("pwd-new");
const confirmPswd = document.getElementById("pwd-confirm");

if (formPswd){
    formPswd.addEventListener('submit', async (event) => {

        event.preventDefault();
        const csrf = new CsrfManager('formPatientPswd');
        await csrf.prepare();

        const data = {
            formName : formName.value,
            csrfToken : csrfToken.value,
            backPassword : currentPswd.value,
            newPassword : newPswd.value,
            confirmPassword : confirmPswd.value,
        }

        const patient= new Patient();
        patient.updatePswd(formPswd, data);
    })
}