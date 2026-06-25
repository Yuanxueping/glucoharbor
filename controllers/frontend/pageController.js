const Page = require('../../models/Page');
const Article = require('../../models/Article');
const Category = require('../../models/Category');

exports.show = async (req, res) => {
  const page = await Page.findBySlug(req.params.slug);
  if (!page) return res.status(404).render('frontend/404', { title: 'Not Found' });
  res.render('frontend/page', {
    title: page.meta_title || page.title,
    metaDescription: page.meta_description,
    page
  });
};

exports.sitemap = async (req, res) => {
  const [articles, categories] = await Promise.all([
    Article.getSitemap(),
    Category.getAll()
  ]);
  res.header('Content-Type', 'application/xml');
  res.render('frontend/sitemap', { articles, categories, appUrl: process.env.APP_URL || 'https://glucoharbor.com' });
};
