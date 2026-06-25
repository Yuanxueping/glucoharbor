const Article = require('../../models/Article');
const Category = require('../../models/Category');
const Product = require('../../models/Product');

exports.index = async (req, res) => {
  const [articleCount, publishedCount, categoryCount, productCount, recentArticles] = await Promise.all([
    Article.countAll(),
    Article.countPublished(),
    Category.count(),
    Product.countAll(),
    Article.getRecent(8)
  ]);
  res.render('admin/dashboard', {
    title: 'Dashboard',
    stats: { articleCount, publishedCount, categoryCount, productCount },
    recentArticles
  });
};
