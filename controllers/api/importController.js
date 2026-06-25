const Article = require('../../models/Article');
const Category = require('../../models/Category');
const slugify = require('slugify');

/**
 * POST /api/articles/import
 * Headers: x-api-token: <token>
 * Body: { title, content, excerpt, category_slug, featured_image, status, meta_title, meta_description, meta_keywords, tags, source }
 */
exports.importArticle = async (req, res) => {
  try {
    const { title, content, excerpt, category_slug, featured_image, status = 'published',
            meta_title, meta_description, meta_keywords, source = 'api' } = req.body;

    if (!title || !content) {
      return res.status(400).json({ success: false, message: 'title and content are required' });
    }

    let category_id = null;
    if (category_slug) {
      const cat = await Category.findBySlug(category_slug);
      if (cat) category_id = cat.id;
    }

    const id = await Article.create({
      title, content, excerpt, category_id, featured_image,
      status, meta_title, meta_description, meta_keywords,
      source, author_id: null
    });

    const article = await Article.findById(id);
    res.json({ success: true, message: 'Article imported', data: { id, slug: article.slug } });
  } catch (e) {
    res.status(500).json({ success: false, message: e.message });
  }
};

/**
 * POST /api/articles/import-batch
 * Import multiple articles at once
 */
exports.importBatch = async (req, res) => {
  const { articles } = req.body;
  if (!Array.isArray(articles) || articles.length === 0) {
    return res.status(400).json({ success: false, message: 'articles array is required' });
  }
  const results = [];
  for (const item of articles) {
    try {
      let category_id = null;
      if (item.category_slug) {
        const cat = await Category.findBySlug(item.category_slug);
        if (cat) category_id = cat.id;
      }
      const id = await Article.create({ ...item, category_id, source: item.source || 'api' });
      results.push({ success: true, id, title: item.title });
    } catch (e) {
      results.push({ success: false, title: item.title, error: e.message });
    }
  }
  res.json({ success: true, results });
};

exports.getCategories = async (req, res) => {
  const categories = await Category.getAll();
  res.json({ success: true, data: categories.map(c => ({ id: c.id, name: c.name, slug: c.slug })) });
};
