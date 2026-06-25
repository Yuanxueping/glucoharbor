const db = require('../config/database');
const slugify = require('slugify');

class Article {
  static async findAll({ page = 1, limit = 12, categoryId, status = 'published', search, featured } = {}) {
    const offset = (page - 1) * limit;
    let where = ['1=1'];
    const params = [];

    if (status) { where.push('a.status = ?'); params.push(status); }
    if (categoryId) { where.push('a.category_id = ?'); params.push(categoryId); }
    if (featured !== undefined) { where.push('a.is_featured = ?'); params.push(featured ? 1 : 0); }
    if (search) {
      where.push('(a.title LIKE ? OR a.excerpt LIKE ?)');
      params.push(`%${search}%`, `%${search}%`);
    }

    const whereStr = where.join(' AND ');
    const [rows] = await db.query(
      `SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
       FROM articles a
       LEFT JOIN categories c ON a.category_id = c.id
       LEFT JOIN users u ON a.author_id = u.id
       WHERE ${whereStr}
       ORDER BY a.published_at DESC, a.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );
    const [[{ total }]] = await db.query(
      `SELECT COUNT(*) as total FROM articles a WHERE ${whereStr}`,
      params
    );
    return { rows, total, pages: Math.ceil(total / limit), page };
  }

  static async findById(id) {
    const [rows] = await db.query(
      `SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
       FROM articles a
       LEFT JOIN categories c ON a.category_id = c.id
       LEFT JOIN users u ON a.author_id = u.id
       WHERE a.id = ?`,
      [id]
    );
    return rows[0] || null;
  }

  static async findBySlug(slug) {
    const [rows] = await db.query(
      `SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
       FROM articles a
       LEFT JOIN categories c ON a.category_id = c.id
       LEFT JOIN users u ON a.author_id = u.id
       WHERE a.slug = ? AND a.status = 'published'`,
      [slug]
    );
    return rows[0] || null;
  }

  static async create(data) {
    const slug = await Article._uniqueSlug(data.slug || slugify(data.title, { lower: true, strict: true }));
    const publishedAt = data.status === 'published' ? (data.published_at || new Date()) : null;
    const [result] = await db.query(
      `INSERT INTO articles (title, slug, excerpt, content, featured_image, category_id, author_id, status, is_featured, source, meta_title, meta_description, meta_keywords, published_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [data.title, slug, data.excerpt || null, data.content || null, data.featured_image || null,
       data.category_id || null, data.author_id || null, data.status || 'draft',
       data.is_featured ? 1 : 0, data.source || 'manual',
       data.meta_title || null, data.meta_description || null, data.meta_keywords || null, publishedAt]
    );
    return result.insertId;
  }

  static async update(id, data) {
    const publishedAt = data.status === 'published' ? (data.published_at || new Date()) : null;
    await db.query(
      `UPDATE articles SET title=?, slug=?, excerpt=?, content=?, featured_image=?, category_id=?, status=?, is_featured=?, meta_title=?, meta_description=?, meta_keywords=?, published_at=? WHERE id=?`,
      [data.title, data.slug, data.excerpt || null, data.content || null, data.featured_image || null,
       data.category_id || null, data.status || 'draft', data.is_featured ? 1 : 0,
       data.meta_title || null, data.meta_description || null, data.meta_keywords || null, publishedAt, id]
    );
  }

  static async delete(id) {
    await db.query('DELETE FROM article_tags WHERE article_id = ?', [id]);
    await db.query('DELETE FROM articles WHERE id = ?', [id]);
  }

  static async incrementViews(id) {
    await db.query('UPDATE articles SET views = views + 1 WHERE id = ?', [id]);
  }

  static async getRelated(articleId, categoryId, limit = 4) {
    const [rows] = await db.query(
      `SELECT a.id, a.title, a.slug, a.featured_image, a.published_at, a.excerpt
       FROM articles a
       WHERE a.category_id = ? AND a.id != ? AND a.status = 'published'
       ORDER BY a.published_at DESC LIMIT ?`,
      [categoryId, articleId, limit]
    );
    return rows;
  }

  static async countAll() {
    const [rows] = await db.query('SELECT COUNT(*) as cnt FROM articles');
    return rows[0].cnt;
  }

  static async countPublished() {
    const [rows] = await db.query("SELECT COUNT(*) as cnt FROM articles WHERE status='published'");
    return rows[0].cnt;
  }

  static async getRecent(limit = 5) {
    const [rows] = await db.query(
      "SELECT id, title, slug, featured_image, published_at FROM articles WHERE status='published' ORDER BY published_at DESC LIMIT ?",
      [limit]
    );
    return rows;
  }

  static async getFeatured(limit = 5) {
    const [rows] = await db.query(
      `SELECT a.*, c.name as category_name, c.slug as category_slug
       FROM articles a LEFT JOIN categories c ON a.category_id = c.id
       WHERE a.status='published' AND a.is_featured=1
       ORDER BY a.published_at DESC LIMIT ?`,
      [limit]
    );
    return rows;
  }

  static async _uniqueSlug(slug) {
    const [rows] = await db.query('SELECT slug FROM articles WHERE slug LIKE ?', [`${slug}%`]);
    const existing = rows.map(r => r.slug);
    if (!existing.includes(slug)) return slug;
    let i = 2;
    while (existing.includes(`${slug}-${i}`)) i++;
    return `${slug}-${i}`;
  }

  static async getSitemap() {
    const [rows] = await db.query(
      "SELECT slug, updated_at FROM articles WHERE status='published' ORDER BY updated_at DESC"
    );
    return rows;
  }
}

module.exports = Article;
