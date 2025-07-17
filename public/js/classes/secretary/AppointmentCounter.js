import { MasterFetch } from '/js/promises/MasterFetch.js';

export class AppointmentCounter {
  constructor() {
    this.count = 0;
    this.init();
  }

  async init() {
    const result = await MasterFetch.call('countAppointments');
    if (result && typeof result.totalappointment === 'number') {
      return result.totalappointment
    }
    return null;
  }
}
