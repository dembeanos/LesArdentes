import { Popup } from '/Ardentes/public/js/components/Popup.js';

const login = document.getElementById('login');
const password = document.getElementById('password');
const btnSubmit = document.getElementById('btnSubmit');

btnSubmit.addEventListener('click', async (event) => {
  event.preventDefault();

  const data = {
    action: 'login',
    login: login.value,
    password: password.value
  };

  try {
    const response = await fetch('.././src/core/loginRouter.php', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });

    if (!response.ok) {
      throw new Error(`HTTP error ${response.status}`);
    }

    const result = await response.json();
    console.log(result)

    const notifications = result.notifications ?? {};

    const popup = new Popup(
      notifications.input ?? [],
      notifications.popup ?? [],
      notifications.console ?? [],
      null
    );
    popup.run();

    if (notifications.redirect && notifications.redirect.length > 0) {
      const redirectUrl = notifications.redirect[0];

      const redirectPopup = new Popup(
        [],
        ['Connexion réussie. Redirection en cours...'],
        [],
        null
      );
      redirectPopup.run();

      setTimeout(() => {
        window.location.href = redirectUrl;
      }, 2000);
    }

  } catch (error) {
    console.error('Erreur fetch:', error);

    const errorPopup = new Popup(
      [],
      ['Erreur réseau ou serveur. Veuillez réessayer plus tard.'],
      [error.message],
      null
    );
    errorPopup.run();
  }
});
