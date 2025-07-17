import { MasterFetch } from'/js/promises/MasterFetch.js';


export class Patient {


async getPatientInfo(fields) {
  const result = await MasterFetch.call('getPatientInfo');
  if (result) {
    const patient= result.patient;
    fields.lastName.value = patient.lastname;
    fields.firstName.value = patient.firstname;
    fields.email.value = patient.email;
    fields.phone.value = patient.phone;
    fields.address.value = patient.address;
    return true;
  }
  return false;
}

async updatePatientInfo(data){
    const update = await MasterFetch.call('updatePatientInfo', data)
    if (update){
        return true;
    }
    return false;
}

async updatePswd(formPswd,data){
    const update = await MasterFetch.call('updatePassword', data)
    if (update){
        formPswd.reset();
        return true;
    }
    return false;
}


}