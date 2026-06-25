const Product = require('../../models/Product');

exports.index = async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const { rows, total, pages } = await Product.findAll({ page, limit: 20, status: null });
  const categories = await Product.getCategories();
  const flash = req.session.flash;
  delete req.session.flash;
  res.render('admin/products/index', { title: 'Products', products: rows, total, pages, page, categories, flash });
};

exports.newForm = async (req, res) => {
  const categories = await Product.getCategories();
  res.render('admin/products/edit', { title: 'New Product', product: null, categories, error: null });
};

exports.create = async (req, res) => {
  try {
    const data = { ...req.body };
    if (req.file) data.image = '/uploads/' + req.file.filename;
    await Product.create(data);
    req.session.flash = { type: 'success', msg: 'Product created.' };
    res.redirect('/admin/products');
  } catch (e) {
    const categories = await Product.getCategories();
    res.render('admin/products/edit', { title: 'New Product', product: req.body, categories, error: e.message });
  }
};

exports.editForm = async (req, res) => {
  const product = await Product.findById(req.params.id);
  if (!product) return res.redirect('/admin/products');
  const categories = await Product.getCategories();
  res.render('admin/products/edit', { title: 'Edit Product', product, categories, error: null });
};

exports.update = async (req, res) => {
  try {
    const product = await Product.findById(req.params.id);
    const data = { ...req.body };
    if (req.file) data.image = '/uploads/' + req.file.filename;
    else data.image = product.image;
    await Product.update(req.params.id, data);
    req.session.flash = { type: 'success', msg: 'Product updated.' };
    res.redirect('/admin/products');
  } catch (e) {
    const categories = await Product.getCategories();
    res.render('admin/products/edit', { title: 'Edit Product', product: { ...req.body, id: req.params.id }, categories, error: e.message });
  }
};

exports.delete = async (req, res) => {
  await Product.delete(req.params.id);
  req.session.flash = { type: 'success', msg: 'Product deleted.' };
  res.redirect('/admin/products');
};

exports.createCategory = async (req, res) => {
  try {
    await Product.createCategory(req.body);
    req.session.flash = { type: 'success', msg: 'Product category created.' };
  } catch (e) {
    req.session.flash = { type: 'danger', msg: e.message };
  }
  res.redirect('/admin/products');
};
