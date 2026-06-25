const Product = require('../../models/Product');
const Category = require('../../models/Category');

exports.list = async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const perPage = parseInt(res.locals.settings.products_per_page) || 12;
  const categoryId = req.query.category || null;
  const { rows, total, pages } = await Product.findAll({ page, limit: perPage, status: 'active', categoryId });
  const categories = await Product.getCategories();
  res.render('frontend/products', {
    title: 'Health Products',
    products: rows,
    categories,
    total, pages, page,
    currentCategoryId: categoryId
  });
};

exports.detail = async (req, res) => {
  const product = await Product.findBySlug(req.params.slug);
  if (!product) return res.status(404).render('frontend/404', { title: 'Not Found' });
  res.render('frontend/product', {
    title: product.meta_title || product.name,
    metaDescription: product.meta_description || product.short_desc,
    product
  });
};
