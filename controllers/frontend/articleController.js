const Article = require('../../models/Article');
const Category = require('../../models/Category');

exports.list = async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const perPage = parseInt(res.locals.settings.articles_per_page) || 12;
  const { rows, total, pages } = await Article.findAll({ page, limit: perPage, status: 'published' });
  const categories = await Category.getAll();
  res.render('frontend/articles', {
    title: 'Health Articles',
    articles: rows,
    categories,
    total, pages, page,
    currentCategory: null
  });
};

exports.byCategory = async (req, res) => {
  const category = await Category.findBySlug(req.params.slug);
  if (!category) return res.status(404).render('frontend/404', { title: 'Not Found' });
  const page = parseInt(req.query.page) || 1;
  const perPage = parseInt(res.locals.settings.articles_per_page) || 12;
  const { rows, total, pages } = await Article.findAll({ page, limit: perPage, status: 'published', categoryId: category.id });
  const categories = await Category.getAll();
  res.render('frontend/articles', {
    title: category.name,
    metaDescription: category.meta_description || category.description,
    articles: rows,
    categories,
    total, pages, page,
    currentCategory: category
  });
};

exports.detail = async (req, res) => {
  const article = await Article.findBySlug(req.params.slug);
  if (!article) return res.status(404).render('frontend/404', { title: 'Not Found' });
  const [related, categories] = await Promise.all([
    Article.getRelated(article.id, article.category_id, 4),
    Category.getAll()
  ]);
  await Article.incrementViews(article.id);
  res.render('frontend/article', {
    title: article.meta_title || article.title,
    metaDescription: article.meta_description || article.excerpt,
    metaKeywords: article.meta_keywords,
    article,
    related,
    categories
  });
};
