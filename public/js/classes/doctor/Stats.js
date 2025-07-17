import { MasterFetch } from '/js/promises/MasterFetch.js';

export class Stats {
  static async getPatientToday() {
    const result = await MasterFetch.call('patientToday');
    return result.patienttoday;
  }

  static async getMoneyMonth() {
    const result = await MasterFetch.call('getMoneyMonth');
    return result.moneyMonth;
  }

  static async getAttendance() {
    const result = await MasterFetch.call('getAttendance');
    return result.attendance
  }

  static async getNewPatientMonth() {
    const result = await MasterFetch.call('newPatientMonth');
    return result.newpatientmonth
  }

  static async getPresenceRate() {
    const result = await MasterFetch.call('attendanceRate');
    return result.attendance
  }

  static async getConsultationPerMonth() {
    const result = await MasterFetch.call('appointmentMonth');
    return result.total
  }

  static async getPatientPerMonth() {
    const result = await MasterFetch.call('newPatientMonth');
    return result.newpatientmonth;
  }

static async getCreditStats(){
  const result = await MasterFetch.call('doctorCreditStat');
  return result
}
static async getOfficeCreditStats(){
  const result = await MasterFetch.call('officeCreditStat');
  return result
}
static async getAppointmentSixMonth(){
  const result = await MasterFetch.call('AppointmentStat');
  return result
}
static async getStatPatientSix(){
  const result = await MasterFetch.call('doctorPatientStat');
  return result
}
}