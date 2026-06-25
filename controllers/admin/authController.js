const User = require('../../models/User');

exports.loginPage = (req, res) => {
  if (req.session.user) return res.redirect('/admin');
  res.render('admin/login', { title: 'Admin Login', error: null });
};

exports.login = async (req, res) => {
  const { username, password } = req.body;
  try {
    const user = await User.findByUsername(username);
    if (!user || !(await User.verifyPassword(password, user.password))) {
      return res.render('admin/login', { title: 'Admin Login', error: 'Invalid username or password' });
    }
    req.session.user = { id: user.id, username: user.username, email: user.email, role: user.role };
    const returnTo = req.session.returnTo || '/admin';
    delete req.session.returnTo;
    res.redirect(returnTo);
  } catch (e) {
    res.render('admin/login', { title: 'Admin Login', error: 'Login failed. Please try again.' });
  }
};

exports.logout = (req, res) => {
  req.session.destroy(() => res.redirect('/admin/login'));
};
