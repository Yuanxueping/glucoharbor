require('dotenv').config();
const express = require('express');
const path = require('path');
const session = require('express-session');
const cookieParser = require('cookie-parser');
const compression = require('compression');
const morgan = require('morgan');
const helmet = require('helmet');
const dayjs = require('dayjs');
const fs = require('fs');

const Setting = require('./models/Setting');
const Page = require('./models/Page');
const Category = require('./models/Category');

const app = express();

// Security & performance middleware
app.use(helmet({
  contentSecurityPolicy: false, // allow CDNs
  crossOriginEmbedderPolicy: false
}));
app.use(compression());
if (process.env.NODE_ENV !== 'production') app.use(morgan('dev'));

// Body parsing
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(cookieParser());

// Session
app.use(session({
  secret: process.env.SESSION_SECRET || 'glucoharbor_secret_2024',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.NODE_ENV === 'production',
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 days
  }
}));

// Static files
const uploadsDir = path.join(__dirname, 'public/uploads');
if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });
app.use(express.static(path.join(__dirname, 'public')));

// View engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Global template locals
app.use(async (req, res, next) => {
  try {
    const [settings, footerPages, navCategories] = await Promise.all([
      Setting.getAll(),
      Page.getFooterPages(),
      Category.getAll()
    ]);
    res.locals.settings = settings;
    res.locals.footerPages = footerPages;
    res.locals.navCategories = navCategories;
    res.locals.appUrl = process.env.APP_URL || 'https://glucoharbor.com';
    res.locals.currentPath = req.path;
    res.locals.dayjs = dayjs;
    res.locals.metaDescription = settings.site_description || '';
    res.locals.metaKeywords = '';
    res.locals.adminUser = req.session ? req.session.user : null;
    next();
  } catch (e) {
    res.locals.settings = {};
    res.locals.footerPages = [];
    res.locals.navCategories = [];
    res.locals.appUrl = '';
    res.locals.dayjs = dayjs;
    next();
  }
});

// Routes
app.use('/admin', require('./routes/admin'));
app.use('/api', require('./routes/api'));
app.use('/', require('./routes/frontend'));

// 404 handler
app.use((req, res) => {
  res.status(404).render('frontend/404', { title: '404 - Page Not Found' });
});

// Error handler
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).render('frontend/500', { title: '500 - Server Error', error: process.env.NODE_ENV !== 'production' ? err : {} });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`GlucoHarbor running on port ${PORT}`);
  console.log(`Admin: http://localhost:${PORT}/admin`);
});

module.exports = app;
