import { MasterFetch } from '/Ardentes/public/js/promises/MasterFetch.js';

export class UnreadMessages {
  constructor() {
    this.count = 0;
    this.init();
  }

  async init() {
    const result = await MasterFetch.call('getUnreadMessage');
    if (result && typeof result.unreadedMessages === 'number') {
      return result.unreadedMessages
    }
    return null;
  }
}