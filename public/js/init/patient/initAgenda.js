import { Calendar } from "../../classes/patient/Calendar.js";

const doctorSelect = document.getElementById('doctorSelect');
const calendarEl = document.getElementById('calendar');

let calendar = new FullCalendar.Calendar(calendarEl, {
  initialView: 'timeGridWeek',
  locale: 'fr',
  slotMinTime: "07:00:00",
  slotMaxTime: "22:00:00",
  selectable: true,
  editable: false,
  timeZone: 'local',
  events: [],
  dateClick: async function(info) {
    const doctorId = doctorSelect.value;
    if (!doctorId) return alert("Veuillez d'abord choisir un médecin.");

    const calendarClass = new Calendar();
    calendarClass.openAppointmentModal(doctorId, info.date);
  }
});


calendar.render();

if (doctorSelect) {
  const doctorList = new Calendar();
  doctorList.generateOptions().then(fragment => {
    if (fragment) doctorSelect.appendChild(fragment);
  });
}

doctorSelect.addEventListener('change', async () => {
  const doctorId = doctorSelect.value;
  const calendarClass = new Calendar();

  const events = await calendarClass.getAvailable(doctorId);

  calendar.removeAllEvents();
  calendar.addEventSource(events);
});
