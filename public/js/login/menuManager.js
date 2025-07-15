window.addEventListener('load', () => {
    const connexion = document.getElementById('connexion');

    fetch('../src/core/loginRouter.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'checkConnection' })
    })
    .then(res => res.json())
    .then(data => {
        if (!connexion) return;
 
        if (data.connected) {
            
            if (data.role === 'patient') {
                connexion.textContent = `Espace Famille ${data.username}`;
                connexion.href = '/Ardentes/public/patient-dashboard.php';
            } else if (data.role === 'doctor') {
                connexion.textContent = `Espace Dr ${data.username}`;
                connexion.href = '/Ardentes/public/doctor-dashboard.php';
            } else {
                connexion.textContent = `Espace Secrétaire ${data.username}`;
                connexion.href = '/Ardentes/public/secretary-dashboard.php';
            }

            if (connexion.parentElement && !document.getElementById('logout-link')) {
                const logoutLi = document.createElement('li');
                logoutLi.className = 'nav-item';

                const logoutLink = document.createElement('a');
                logoutLink.className = 'nav-link text-danger';
                logoutLink.href = '/Ardentes/public/index.php';
                logoutLink.id = 'logout-link';
                logoutLink.textContent = 'Déconnexion';

                logoutLi.appendChild(logoutLink);
                connexion.parentElement.insertAdjacentElement('afterend', logoutLi);

                logoutLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    fetch('../src/core/loginRouter.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'logout' })
                    }).then(() => {
                        window.location.href = '/Ardentes/public/index.php';
                    });
                });
            }
        }
    })
    .catch(err => {
        console.error('Erreur fetch menuStatus:', err);
    });
});
