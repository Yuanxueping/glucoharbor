const db = require('../config/database');
const slugify = require('slugify');

class Category {
  static async getAll() {
    const [rows] = await db.query(
      'SELECT c.*, p.name as parent_name, (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = "published") as article_count FROM categories c LEFT JOIN categories p ON c.parent_id = p.id ORDER BY c.sort_order, c.name'
    );
    return rows;
  }

  static async findById(id) {
    const [rows] = await db.query('SELECT * FROM categories WHERE id = ?', [id]);
    return rows[0] || null;
  }

  static async findBySlug(slug) {
    const [rows] = await db.query('SELECT * FROM categories WHERE slug = ?', [slug]);
    return rows[0] || null;
  }

  static async create(data) {
    const slug = data.slug || slugify(data.name, { lower: true, strict: true });
    const [result] = await db.query(
      'INSERT INTO categories (name, slug, description, parent_id, icon, sort_order, meta_title, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
      [data.name, slug, data.description || null, data.parent_id || null, data.icon || null, data.sort_order || 0, data.meta_title || null, data.meta_description || null]
    );
    return result.insertId;
  }

  static async update(id, data) {
    await db.query(
      'UPDATE categories SET name=?, slug=?, description=?, parent_id=?, icon=?, sort_order=?, meta_title=?, meta_description=? WHERE id=?',
      [data.name, data.slug, data.description || null, data.parent_id || null, data.icon || null, data.sort_order || 0, data.meta_title || null, data.meta_description || null, id]
    );
  }

  static async delete(id) {
    await db.query('UPDATE articles SET category_id = NULL WHERE category_id = ?', [id]);
    await db.query('DELETE FROM categories WHERE id = ?', [id]);
  }

  static async count() {
    const [rows] = await db.query('SELECT COUNT(*) as cnt FROM categories');
    return rows[0].cnt;
  }
}

module.exports = Category;
