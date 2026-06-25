const db = require('../config/database');
const slugify = require('slugify');

class Page {
  static async getAll() {
    const [rows] = await db.query('SELECT * FROM pages ORDER BY sort_order, title');
    return rows;
  }

  static async getFooterPages() {
    const [rows] = await db.query("SELECT id, title, slug FROM pages WHERE status='published' AND in_footer=1 ORDER BY sort_order");
    return rows;
  }

  static async findById(id) {
    const [rows] = await db.query('SELECT * FROM pages WHERE id = ?', [id]);
    return rows[0] || null;
  }

  static async findBySlug(slug) {
    const [rows] = await db.query("SELECT * FROM pages WHERE slug = ? AND status = 'published'", [slug]);
    return rows[0] || null;
  }

  static async create(data) {
    const slug = data.slug || slugify(data.title, { lower: true, strict: true });
    const [result] = await db.query(
      'INSERT INTO pages (title, slug, content, status, in_footer, sort_order, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [data.title, slug, data.content || null, data.status || 'published', data.in_footer ? 1 : 0, data.sort_order || 0, data.meta_title || null, data.meta_description || null]
    );
    return result.insertId;
  }

  static async update(id, data) {
    await db.query(
      'UPDATE pages SET title=?, slug=?, content=?, status=?, in_footer=?, sort_order=?, meta_title=?, meta_description=? WHERE id=?',
      [data.title, data.slug, data.content || null, data.status || 'published', data.in_footer ? 1 : 0, data.sort_order || 0, data.meta_title || null, data.meta_description || null, id]
    );
  }

  static async delete(id) {
    await db.query('DELETE FROM pages WHERE id = ?', [id]);
  }
}

module.exports = Page;
