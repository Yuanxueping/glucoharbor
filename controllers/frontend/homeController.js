const Article = require('../../models/Article');
const Category = require('../../models/Category');
const Product = require('../../models/Product');
const Setting = require('../../models/Setting');

exports.index = async (req, res) => {
  const [featured, recent, categories, products] = await Promise.all([
    Article.getFeatured(5),
    Article.findAll({ page: 1, limit: 9, status: 'published' }),
    Category.getAll(),
    Product.findAll({ page: 1, limit: 4, status: 'active' })
  ]);
  res.render('frontend/index', {
    title: res.locals.settings.site_tagline || 'Health Information',
    featured: featured,
    articles: recent.rows,
    categories,
    products: products.rows
  });
};

exports.search = async (req, res) => {
  const q = req.query.q || '';
  const page = parseInt(req.query.page) || 1;
  const { rows, total, pages } = await Article.findAll({ page, limit: 12, status: 'published', search: q });
  res.render('frontend/search', {
    title: `Search: ${q}`,
    query: q,
    articles: rows,
    total, pages, page
  });
};
