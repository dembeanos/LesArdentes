import { Popup } from '/Ardentes/public/js/components/Popup.js';

export class MasterFetch {

  static normalizeInput(input) {
    if (!input) return [];
    if (Array.isArray(input)) return input;

    const messages = [];
    for (const field in input) {
      const types = input[field];
      for (const type in types) {
        types[type].forEach(text => {
          messages.push({ field, type, text });
        });
      }
    }
    return messages;
  }

 static async call(action, data = {}) {
  try {
    const response = await fetch('/Ardentes/src/core/MasterRouter.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ...data }),
      credentials: 'include'
    });

    if (!response.ok) {
      let errorMsg = `HTTP error! status: ${response.status}`;
      try {
        const errJson = await response.clone().json();
        if (errJson.error) errorMsg += ` - ${errJson.error}`;
      } catch {}
      throw new Error(errorMsg);
    }

    const json = await response.json();

    const notifications = json.notifications ?? {};
    const normalizedInput = this.normalizeInput(notifications.input);

    if (
      normalizedInput.length > 0 ||
      (Array.isArray(notifications.popup) && notifications.popup.length > 0) ||
      (Array.isArray(notifications.console) && notifications.console.length > 0) ||
      notifications.modale
    ) {
      const popup = new Popup(
        normalizedInput,
        notifications.popup ?? [],
        notifications.console ?? [],
        notifications.modale ?? null
      );
      popup.run();
    }

    if (Array.isArray(notifications.redirect) && notifications.redirect.length > 0) {
      window.location.href = notifications.redirect[0];
      return;
    }

    return json.data ?? null;

  } catch (err) {
    console.error('%c[MasterFetch ERROR]', 'color: red;', err);
    const popup = new Popup(
      [],
      [`Erreur interne : ${err.message}`],
      [`[MasterFetch] ${err.stack}`]
    );
    popup.run();
    throw err;
  }
}
}
