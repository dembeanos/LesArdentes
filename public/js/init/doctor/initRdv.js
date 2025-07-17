import { DoctorAppointments } from '/js/classes/doctor/DoctorAppointments.js';
import { CsrfManager } from '/js/classes/CsrfManager.js';
import { MasterFetch } from '/js/promises/MasterFetch.js';

const tbody = document.getElementById('doctor-appointments-tbody-global');

await DoctorAppointments.getAllRdv(tbody);

  tbody.addEventListener('click', async e => {
    const btn = e.target.closest('button');
    if (!btn) return;

    const action = btn.dataset.action;
    const tr = btn.closest('tr');
    const id = tr.dataset.appointmentid;
    const patientId = tr.dataset.patientid;
    const comment = tr.dataset.comment;

    if (action === 'confirm') {
      const data = {
        appointmentId : id,
        status :'ended'
      }
      await DoctorAppointments.updateAppointmentStatus(data, tbody);
    }
    if (action === 'cancel') {
      const data = {
        appointmentId : id,
        status :'canceled'
      }
      await DoctorAppointments.updateAppointmentStatus(data, tbody);
    }

    function convertDate(frenchDate) {
  const [d, m, y] = frenchDate.split('/');
  return `${y}-${m}-${d}`;
}
    if (action === 'edit') {
      const [date, time, patient, type, comment] = tr.querySelectorAll('td');
      document.getElementById('modify-appointment-date').value = convertDate(date.textContent.trim());
      document.getElementById('modify-appointment-time').value = time.textContent.trim();
      document.getElementById('search-modify-appointment-patient').value = patient.textContent.trim();
      document.getElementById('modify-appointment-type').value = type.textContent.trim();
      document.getElementById('modify-appointment-comment').value = tr.dataset.comment || '';
      document.getElementById('formModifyAppointment_appointmentId').value = id;
      document.getElementById('modify-selectedPatientId').value = patientId;
    }
  });


(() => {
  const modalAdd = document.getElementById('modalAddAppointment');
  if (!modalAdd) return;

  let patientIdAdd = null;
  const searchInput = document.getElementById('add-appointment-patient');
  const suggestions = document.getElementById('patient-suggestions');

  searchInput.addEventListener('input', async () => {
    const term = searchInput.value.trim();
    if (term.length < 3) {
      suggestions.innerHTML = '';
      return;
    }

    const resp = await MasterFetch.call('searchPatient', { term });
    console.log('reponse php',resp)
    suggestions.innerHTML = '';
    suggestions.classList.add('show');
    suggestions.style.width = `${searchInput.offsetWidth}px`;

    resp.forEach(p => {
  console.log('👤 Patient injecté :', p.patient_fullname);
  const b = document.createElement('button');
  b.type = 'button';
  b.className = 'dropdown-item';
  b.textContent = p.patient_fullname;
  b.onclick = () => {
    searchInput.value = p.patient_fullname;
    patientIdAdd = p.patientid;
    suggestions.classList.remove('show');
  };
  suggestions.appendChild(b);
});
  });

  document.getElementById('formAddAppointment').addEventListener('submit', async e => {
    e.preventDefault();
    const csrf = new CsrfManager('formAddAppointment');
    await csrf.prepare();

    const date = document.getElementById('add-appointment-date').value;
    const time = document.getElementById('add-appointment-time').value;
    const type = document.getElementById('add-appointment-type').value;
    const comment = document.getElementById('add-appointment-comment').value;
    const formName = document.getElementById('formAddAppointment_formName').value;
    const token = document.getElementById('formAddAppointment_csrfToken').value;

    const data = {
      patientId: patientIdAdd,
      appointmentDate: `${date} ${time}:00`,
      type : type, 
      comment : comment,
      formName : formName, 
      csrfToken: token
    }
    await DoctorAppointments.addAppointment(data, tbody);
  });
})

()

  document.getElementById('formModifyAppointment').addEventListener('submit', async e => {
    e.preventDefault();

    const csrf = new CsrfManager('formModifyAppointment');
    await csrf.prepare();

  const id = document.getElementById('formModifyAppointment_appointmentId').value;
  const date = document.getElementById('modify-appointment-date').value;
  const time = document.getElementById('modify-appointment-time').value;
  const type = document.getElementById('modify-appointment-type').value;
  const comment = document.getElementById('modify-appointment-comment').value;
  const formName = document.getElementById('formModifyAppointment_formName').value;
  const token = document.getElementById('formModifyAppointment_csrfToken').value;

  const patientId = document.getElementById('modify-selectedPatientId').value;


    const data = {
      appointmentId: id,
      patientId: patientId,
      appointmentDate: `${date} ${time}:00`,
      type : type, 
      comment : comment,
      formName : formName, 
      csrfToken: token
    }

    await DoctorAppointments.modifyAppointment(data, tbody);
  });
