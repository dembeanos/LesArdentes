function ongletActivation(targetId) {
  document.querySelectorAll('.sidebar .nav-link').forEach(link =>link.classList.remove('active'));
  document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));
  
  document.querySelector(`.sidebar .nav-link[data-target='${targetId}']`).classList.add('active');
  
  document.getElementById(targetId).classList.add('active');
}

const initList = {
  'accueil': '/js/init/doctor/initHome.js',
  'patients-section': '/js/init/doctor/initPatient.js',
  'rendez-vous': '/js/init/doctor/initRdv.js',
  'statistiques': '/js/init/doctor/initCharts.js',
  'contact-secretariat': '/js/init/doctor/initSecretary.js',
}

function launchScript(src) {
  if(!document.querySelector(`script[src='${src}']`)) {
    const script = document.createElement('script');
    script.src = src;
    script.type = 'module';
    document.body.appendChild(script);
  }
}

launchScript('/js/init/doctor/initGlobal.js');
launchScript('/js/init/doctor/initHome.js');

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
 
document.querySelector('.sidebar .nav-link').click();