
function ongletActivation(targetId) {
  document.querySelectorAll('.sidebar .nav-link').forEach(link =>link.classList.remove('active'));
  document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));
  
  document.querySelector(`.sidebar .nav-link[data-target='${targetId}']`).classList.add('active');
  
  document.getElementById(targetId).classList.add('active');
}

const initList = {
  'rdv-section': '/Ardentes/public/js/init/secretary/initRdv.js',
  'patients-section': '/Ardentes/public/js/init/secretary/initPatient.js',
  'notify-section': '/Ardentes/public/js/init/secretary/initNotification.js',
  'hours-section': '/Ardentes/public/js/init/secretary/initHour.js'
}


function launchScript(src) {
  if(!document.querySelector(`script[src='${src}']`)) {
    const script = document.createElement('script');
    script.src = src;
    script.type = 'module';
    document.body.appendChild(script);
  }
}

launchScript('/Ardentes/public/js/init/secretary/initGlobal.js');
launchScript('/Ardentes/public/js/init/secretary/initRdv.js')

document.querySelectorAll('.sidebar .nav-link').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const target = this.getAttribute('data-target');
    ongletActivation(target);
    if (initList[target]) {
      launchScript(initList[target]);
    }
  });
}); 

