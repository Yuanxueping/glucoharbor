const Article = require('../../models/Article');
const Category = require('../../models/Category');
const path = require('path');
const fs = require('fs');

exports.index = async (req, res) => {
  const page = parseInt(req.query.page) || 1;
  const status = req.query.status || '';
  const search = req.query.search || '';
  const { rows, total, pages } = await Article.findAll({ page, limit: 20, status: status || null, search });
  const categories = await Category.getAll();
  res.render('admin/articles/index', { title: 'Articles', articles: rows, total, pages, page, status, search, categories });
};

exports.newForm = async (req, res) => {
  const categories = await Category.getAll();
  res.render('admin/articles/edit', { title: 'New Article', article: null, categories, error: null });
};

exports.create = async (req, res) => {
  try {
    const data = { ...req.body, author_id: req.session.user.id };
    if (req.file) data.featured_image = '/uploads/' + req.file.filename;
    else if (data.featured_image_url) data.featured_image = data.featured_image_url;
    const id = await Article.create(data);
    req.session.flash = { type: 'success', msg: 'Article created successfully.' };
    res.redirect('/admin/articles/' + id + '/edit');
  } catch (e) {
    const categories = await Category.getAll();
    res.render('admin/articles/edit', { title: 'New Article', article: req.body, categories, error: e.message });
  }
};

exports.editForm = async (req, res) => {
  const article = await Article.findById(req.params.id);
  if (!article) return res.redirect('/admin/articles');
  const categories = await Category.getAll();
  res.render('admin/articles/edit', { title: 'Edit Article', article, categories, error: null });
};

exports.update = async (req, res) => {
  try {
    const article = await Article.findById(req.params.id);
    if (!article) return res.redirect('/admin/articles');
    const data = { ...req.body };
    if (req.file) data.featured_image = '/uploads/' + req.file.filename;
    else if (data.featured_image_url) data.featured_image = data.featured_image_url;
    else data.featured_image = article.featured_image;
    await Article.update(req.params.id, data);
    req.session.flash = { type: 'success', msg: 'Article updated successfully.' };
    res.redirect('/admin/articles/' + req.params.id + '/edit');
  } catch (e) {
    const categories = await Category.getAll();
    res.render('admin/articles/edit', { title: 'Edit Article', article: { ...req.body, id: req.params.id }, categories, error: e.message });
  }
};

exports.delete = async (req, res) => {
  await Article.delete(req.params.id);
  req.session.flash = { type: 'success', msg: 'Article deleted.' };
  res.redirect('/admin/articles');
};

exports.uploadImage = async (req, res) => {
  if (!req.file) return res.json({ success: false, message: 'No file uploaded' });
  res.json({ success: true, url: '/uploads/' + req.file.filename });
};
