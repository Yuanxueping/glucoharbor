const db = require('../config/database');

class Setting {
  static async getAll() {
    const [rows] = await db.query('SELECT setting_key, setting_value FROM settings');
    const settings = {};
    rows.forEach(r => { settings[r.setting_key] = r.setting_value; });
    return settings;
  }

  static async get(key) {
    const [rows] = await db.query('SELECT setting_value FROM settings WHERE setting_key = ?', [key]);
    return rows.length ? rows[0].setting_value : null;
  }

  static async set(key, value) {
    await db.query(
      'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?',
      [key, value, value]
    );
  }

  static async setMultiple(data) {
    const entries = Object.entries(data);
    for (const [key, value] of entries) {
      await Setting.set(key, value);
    }
  }
}

module.exports = Setting;
