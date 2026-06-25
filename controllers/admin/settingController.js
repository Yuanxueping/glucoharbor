const Setting = require('../../models/Setting');
const crypto = require('crypto');

exports.index = async (req, res) => {
  const settings = await Setting.getAll();
  const flash = req.session.flash;
  delete req.session.flash;
  res.render('admin/settings/index', { title: 'Settings', settings, flash });
};

exports.update = async (req, res) => {
  try {
    const data = { ...req.body };
    // Handle checkboxes
    ['adsense_enabled', 'afs_enabled'].forEach(k => {
      data[k] = data[k] ? '1' : '0';
    });
    await Setting.setMultiple(data);
    req.session.flash = { type: 'success', msg: 'Settings saved successfully.' };
  } catch (e) {
    req.session.flash = { type: 'danger', msg: 'Failed to save settings: ' + e.message };
  }
  res.redirect('/admin/settings');
};

exports.generateApiToken = async (req, res) => {
  const token = crypto.randomBytes(32).toString('hex');
  await Setting.set('api_token', token);
  res.json({ success: true, token });
};
