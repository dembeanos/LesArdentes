import { Stats } from '/js/classes/doctor/Stats.js';


const presenceRate = document.getElementById('presenceRate');
if (presenceRate) {
    const progressbar = document.getElementById('progressbar');
    const count = await Stats.getPresenceRate();
    progressbar.style.width = `${count}%`;
    presenceRate.textContent =  `${count}%`;
}

const consultationMonth = document.getElementById('consultationMonth');
if (consultationMonth) {
    const count = await Stats.getConsultationPerMonth();
    consultationMonth.textContent = count
}

const patientMonth = document.getElementById('patientMonth');
if (patientMonth) {
    const count = await Stats.getPatientPerMonth();
    patientMonth.textContent = count
}




const doctorCreditStatSixMonth = document.getElementById("doctorCreditStatSixMonth");
if (doctorCreditStatSixMonth){
    const result = await Stats.getCreditStats()
   const { months, values } = formatDataForChart(result, 'total');
  createLineChart('doctorCreditStatSixMonth', months, values, "Gains Médecin", 'rgba(75,192,192,1)');
}
const  officeCreditStatSixMonth= document.getElementById("officeCreditStatSixMonth");
if (officeCreditStatSixMonth){
    const result = await Stats.getOfficeCreditStats()
const { months, values } = formatDataForChart(result, 'total');
  createLineChart('officeCreditStatSixMonth', months, values, "Gains Cabinet", 'rgba(75,192,192,1)');
}
const appointmentStatSixMonth = document.getElementById("appointmentStatSixMonth");
if (appointmentStatSixMonth){
    const result = await Stats.getAppointmentSixMonth()
const { months, values } = formatDataForChart(result, 'total');
  createLineChart('appointmentStatSixMonth', months, values, "Evolution Rdv", 'rgba(75,192,192,1)');
}
const patientStatSixMonth = document.getElementById("patientStatSixMonth");
if (patientStatSixMonth){
    const result = await Stats.getStatPatientSix()
const { months, values } = formatDataForChart(result, 'total');
  createLineChart('patientStatSixMonth', months, values, "Evolution Patient", 'rgba(75,192,192,1)');
}


function formatDataForChart(rawData, valueKey = 'total') {
  const months = [];
  const values = [];

  rawData.forEach(item => {
    const date = new Date(item.month);
    const monthName = date.toLocaleString('fr-FR', { month: 'short' });
    months.push(monthName.charAt(0).toUpperCase() + monthName.slice(1).replace('.', ''));
    values.push(Number(item[valueKey]));
  });

  return { months, values };
}

function createLineChart(canvasId, labels, data, label, color) {
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;

  
  ctx.style.backgroundColor = 'white';
  ctx.style.borderRadius = '8px';

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        borderColor: color,
        backgroundColor: color.replace(/rgba?\((.+), ?1\)/, 'rgba($1, 0.2)'),
        fill: true,
        tension: 0.3,
        pointRadius: 4,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: { color: '#333' }
        }
      },
      scales: {
        x: {
          ticks: { color: '#333' },
          grid: { color: '#ddd' }
        },
        y: {
          beginAtZero: true,
          ticks: { 
            stepSize: 1,
            color: '#333'
          },
          grid: { color: '#ddd' }
        }
      }
    }
  });
}

