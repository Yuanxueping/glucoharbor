const Category = require('../../models/Category');

exports.index = async (req, res) => {
  const categories = await Category.getAll();
  res.render('admin/categories/index', { title: 'Categories', categories, error: null, flash: req.session.flash });
  delete req.session.flash;
};

exports.create = async (req, res) => {
  try {
    await Category.create(req.body);
    req.session.flash = { type: 'success', msg: 'Category created.' };
  } catch (e) {
    req.session.flash = { type: 'danger', msg: e.message };
  }
  res.redirect('/admin/categories');
};

exports.update = async (req, res) => {
  try {
    await Category.update(req.params.id, req.body);
    req.session.flash = { type: 'success', msg: 'Category updated.' };
  } catch (e) {
    req.session.flash = { type: 'danger', msg: e.message };
  }
  res.redirect('/admin/categories');
};

exports.delete = async (req, res) => {
  await Category.delete(req.params.id);
  req.session.flash = { type: 'success', msg: 'Category deleted.' };
  res.redirect('/admin/categories');
};

exports.getOne = async (req, res) => {
  const cat = await Category.findById(req.params.id);
  res.json(cat);
};
