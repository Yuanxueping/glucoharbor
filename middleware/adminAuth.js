module.exports = (req, res, next) => {
  if (req.session && req.session.user) {
    res.locals.adminUser = req.session.user;
    return next();
  }
  req.session.returnTo = req.originalUrl;
  res.redirect('/admin/login');
};
