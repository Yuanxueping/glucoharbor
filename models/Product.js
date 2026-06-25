const db = require('../config/database');
const slugify = require('slugify');

class Product {
  static async findAll({ page = 1, limit = 12, categoryId, status = 'active' } = {}) {
    const offset = (page - 1) * limit;
    let where = ['1=1'];
    const params = [];
    if (status) { where.push('p.status = ?'); params.push(status); }
    if (categoryId) { where.push('p.category_id = ?'); params.push(categoryId); }
    const whereStr = where.join(' AND ');
    const [rows] = await db.query(
      `SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE ${whereStr} ORDER BY p.sort_order, p.created_at DESC LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await db.query(`SELECT COUNT(*) as total FROM products p WHERE ${whereStr}`, params);
    return { rows, total, pages: Math.ceil(total / limit), page };
  }

  static async findById(id) {
    const [rows] = await db.query(
      'SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE p.id = ?',
      [id]
    );
    return rows[0] || null;
  }

  static async findBySlug(slug) {
    const [rows] = await db.query(
      "SELECT p.*, pc.name as category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE p.slug = ? AND p.status = 'active'",
      [slug]
    );
    return rows[0] || null;
  }

  static async create(data) {
    const slug = data.slug || slugify(data.name, { lower: true, strict: true });
    const [result] = await db.query(
      `INSERT INTO products (name, slug, short_desc, description, price, original_price, currency, image, gallery, category_id, affiliate_url, badge, status, sort_order, meta_title, meta_description)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [data.name, slug, data.short_desc || null, data.description || null,
       data.price || null, data.original_price || null, data.currency || 'USD',
       data.image || null, data.gallery || null, data.category_id || null,
       data.affiliate_url || null, data.badge || null, data.status || 'active',
       data.sort_order || 0, data.meta_title || null, data.meta_description || null]
    );
    return result.insertId;
  }

  static async update(id, data) {
    await db.query(
      `UPDATE products SET name=?, slug=?, short_desc=?, description=?, price=?, original_price=?, currency=?, image=?, gallery=?, category_id=?, affiliate_url=?, badge=?, status=?, sort_order=?, meta_title=?, meta_description=? WHERE id=?`,
      [data.name, data.slug, data.short_desc || null, data.description || null,
       data.price || null, data.original_price || null, data.currency || 'USD',
       data.image || null, data.gallery || null, data.category_id || null,
       data.affiliate_url || null, data.badge || null, data.status || 'active',
       data.sort_order || 0, data.meta_title || null, data.meta_description || null, id]
    );
  }

  static async delete(id) {
    await db.query('DELETE FROM products WHERE id = ?', [id]);
  }

  static async getCategories() {
    const [rows] = await db.query('SELECT * FROM product_categories ORDER BY name');
    return rows;
  }

  static async createCategory(data) {
    const slug = data.slug || slugify(data.name, { lower: true, strict: true });
    const [result] = await db.query('INSERT INTO product_categories (name, slug, description) VALUES (?, ?, ?)', [data.name, slug, data.description || null]);
    return result.insertId;
  }

  static async countAll() {
    const [rows] = await db.query('SELECT COUNT(*) as cnt FROM products');
    return rows[0].cnt;
  }
}

module.exports = Product;
