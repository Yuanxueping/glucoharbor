const Page = require('../../models/Page');

exports.index = async (req, res) => {
  const pages = await Page.getAll();
  const flash = req.session.flash;
  delete req.session.flash;
  res.render('admin/pages/index', { title: 'Pages', pages, flash });
};

exports.newForm = (req, res) => {
  res.render('admin/pages/edit', { title: 'New Page', page: null, error: null });
};

exports.create = async (req, res) => {
  try {
    await Page.create(req.body);
    req.session.flash = { type: 'success', msg: 'Page created.' };
    res.redirect('/admin/pages');
  } catch (e) {
    res.render('admin/pages/edit', { title: 'New Page', page: req.body, error: e.message });
  }
};

exports.editForm = async (req, res) => {
  const page = await Page.findById(req.params.id);
  if (!page) return res.redirect('/admin/pages');
  res.render('admin/pages/edit', { title: 'Edit Page', page, error: null });
};

exports.update = async (req, res) => {
  try {
    await Page.update(req.params.id, req.body);
    req.session.flash = { type: 'success', msg: 'Page updated.' };
    res.redirect('/admin/pages');
  } catch (e) {
    res.render('admin/pages/edit', { title: 'Edit Page', page: { ...req.body, id: req.params.id }, error: e.message });
  }
};

exports.delete = async (req, res) => {
  await Page.delete(req.params.id);
  req.session.flash = { type: 'success', msg: 'Page deleted.' };
  res.redirect('/admin/pages');
};
