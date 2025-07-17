import { MasterFetch } from '/js/promises/MasterFetch.js';

export class Doctors {
  async getList() {
    const result = await MasterFetch.call('getDoctors');
    if (result && Array.isArray(result.doctors)) {
      return result.doctors;
    }
    return null;
  }

  async generateOptions() {
    const doctors = await this.getList();
    if (!doctors) return null;

    const fragment = document.createDocumentFragment();

    doctors.forEach(doc => {
      const option = document.createElement('option');
      option.value = doc.doctorid;
      option.textContent = doc.fullname ?? 'Médecin inconnu';
      fragment.appendChild(option);
    });

    return fragment;
  }
}
