import { TodaysSchedule } from '/Ardentes/public/js/classes/doctor/TodaySchedule.js';

window.addEventListener('load', async () => {
  const schedule = new TodaysSchedule();
  await schedule.loadTodaysAppointments();
  schedule.attachButtonListeners();
});

