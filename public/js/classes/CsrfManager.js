import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';

export class CsrfManager {
  constructor(formName = null) {
    if (formName) {
      this.form = document.querySelector(`form [name="formName"][value="${formName}"]`)?.closest('form') ?? null;
      this.singleFormMode = true;
    } else {
      this.forms = document.querySelectorAll('form');
      this.singleFormMode = false;
    }
  }

  async prepare() {
    if (!this.singleFormMode || !this.form) return false;

    const formNameInput = this.form.querySelector('[name="formName"]');
    const csrfInput = this.form.querySelector('[name="csrfToken"]');

    if (!formNameInput || !csrfInput) return false;

    const formName = formNameInput.value.trim();

    const resRaw = await MasterFetch.call('getValidToken', {
      formName,
      csrfToken: csrfInput.value,
    });

    let res;
    if (typeof resRaw === 'string') {
      try {
        res = JSON.parse(resRaw);
      } catch (e) {
        console.error('Erreur parsing JSON:', e);
        return false;
      }
    } else {
      res = resRaw;
    }

    const token = res[formName];
    if (token) {
      csrfInput.value = token;
      return true;
    } else {
      console.warn(`Token CSRF non reçu pour ${formName}`);
      return false;
    }
  }

  
}
