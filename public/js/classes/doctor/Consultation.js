import { MasterFetch } from '/js/promises/MasterFetch.js';
import { Popup } from '/js/components/Popup.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';

export class Consultation {
    constructor(appointmentId, patientId) {
        this.appointmentId = appointmentId;
        this.patientId = patientId;
        this.modalEl = document.getElementById('modalConsultation');
        this.bsModal = new bootstrap.Modal(this.modalEl);
        this.historyContainer = this.modalEl.querySelector('#consultation-history .border');
        this.form = this.modalEl.querySelector('#consultation-form');
        this.absentBtn = this.modalEl.querySelector('#btn-mark-absent');
        this.submitBtn = this.modalEl.querySelector('[form="consultation-form"]');
    }

    async openModal() {
        
        const res = await MasterFetch.call('getConsultationHistory', { appointmentId: this.appointmentId });
        this.renderHistory(Array.isArray(res.data) ? res.data : []);
        
        this.modalEl.querySelector('#consultation-appointment-id').value = this.appointmentId;
        
        this.absentBtn.onclick = () => {
            MasterFetch.call('markAbsent', { appointmentId: this.appointmentId });
            this.bsModal.hide();
        };
        this.form.onsubmit = async e => {
            e.preventDefault();
            const csrf = new CsrfManager('formDoctorAddConsultation');
            await csrf.prepare();

            const fd = new FormData(this.form);
            const data = {
                formName: this.form.querySelector('#formDoctorAddConsultation_formName').value,
                csrfToken: this.form.querySelector('#formDoctorAddConsultation_csrfToken').value,
                appointmentId: this.appointmentId,
                patientId: this.patientId,
                title: fd.get('title'),
                symptoms: fd.get('symptoms'),
                diagnosis: fd.get('diagnosis'),
                prescription: fd.get('prescription'),
            };

            const data2 = {
                appointmentId: this.appointmentId,
                price: fd.get('price')
            }

            console.log(data)
            await MasterFetch.call('addConsultation', data);
            await MasterFetch.call('pushCredit', data2);
            this.bsModal.hide();
        };
        
        this.bsModal.show();
    }

    async viewConsultation(consultationId) {
        const res = await MasterFetch.call('getConsultationDetail', { consultationId });

        if (!res || res.error || !res.data) {
            new Popup([], ['Consultation introuvable.']).run();
            return;
        }

        const c = res.data;
        const date = new Date(c.consultation_date).toLocaleString('fr-FR');
        const format = txt => (txt || '-').replace(/\n/g, '<br>');
        const html = `
      <dl class="row">
        <dt class="col-sm-4">Date</dt><dd class="col-sm-8">${date}</dd>
        <dt class="col-sm-4">Titre</dt><dd class="col-sm-8">${format(c.title)}</dd>
    <dt class="col-sm-4">Diagnostic</dt><dd class="col-sm-8">${format(c.diagnosis)}</dd>
    <dt class="col-sm-4">Symptomes</dt><dd class="col-sm-8">${format(c.symptoms)}</dd>
    <dt class="col-sm-4">Prescription</dt><dd class="col-sm-8">${format(c.prescription)}</dd>
  </dl>
    `;

        new Popup([], [], [], html).run();
    }
    renderHistory(entries) {
        if (!entries.length) {
            this.historyContainer.innerHTML = '<p class="fst-italic small">Aucun antécédent.</p>';
            return;
        }

        const select = document.createElement('select');
        select.className = 'form-select';
        select.innerHTML = entries.map(h => `
    <option value="${h.consultationid}">
      ${new Date(h.consultation_date).toLocaleDateString('fr-FR')} — ${h.title || 'Sans titre'}
    </option>
  `).join('');

        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline-primary mt-2';
        btn.innerHTML = '<i class="fas fa-eye"></i> Voir détails';
        btn.onclick = () => {
            const id = select.value;
            if (id) this.viewConsultation(id);
        };

        this.historyContainer.innerHTML = '';
        this.historyContainer.appendChild(select);
        this.historyContainer.appendChild(btn);
    }
}