const db = require('../config/database');
const bcrypt = require('bcryptjs');

class User {
  static async findById(id) {
    const [rows] = await db.query('SELECT id, username, email, role, created_at FROM users WHERE id = ?', [id]);
    return rows[0] || null;
  }

  static async findByUsername(username) {
    const [rows] = await db.query('SELECT * FROM users WHERE username = ?', [username]);
    return rows[0] || null;
  }

  static async create({ username, email, password, role = 'editor' }) {
    const hash = await bcrypt.hash(password, 12);
    const [result] = await db.query(
      'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)',
      [username, email, hash, role]
    );
    return result.insertId;
  }

  static async verifyPassword(plain, hash) {
    return bcrypt.compare(plain, hash);
  }

  static async updatePassword(id, newPassword) {
    const hash = await bcrypt.hash(newPassword, 12);
    await db.query('UPDATE users SET password = ? WHERE id = ?', [hash, id]);
  }

  static async count() {
    const [rows] = await db.query('SELECT COUNT(*) as cnt FROM users');
    return rows[0].cnt;
  }
}

module.exports = User;
