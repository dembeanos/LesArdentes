import { Stats } from '/js/classes/doctor/Stats.js';

window.addEventListener('load', async () => {
  const patientToday = document.getElementById('patientToday');
  if (patientToday) {
    const count = await Stats.getPatientToday();
    patientToday.textContent = count !== 0 ? count : '0';
  }

  const moneyMonth = document.getElementById('moneyMonth');
  if (moneyMonth) {
    const count = await Stats.getMoneyMonth();
    moneyMonth.textContent = count !== 0 ? `${count} €` : '0 €';
  }

  const attendance = document.getElementById('attendance');
  if (attendance) {
    const count = await Stats.getAttendance();
    attendance.textContent = count !== 0 ? count : '0';
  }

  const newPatientMonth = document.getElementById('newPatientMonth');
  if (newPatientMonth) {
    const count = await Stats.getNewPatientMonth();
    newPatientMonth.textContent = count !== 0 ? count : '0';
  }
});
