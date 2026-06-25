const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const adminAuth = require('../middleware/adminAuth');

const authCtrl = require('../controllers/admin/authController');
const dashCtrl = require('../controllers/admin/dashboardController');
const articleCtrl = require('../controllers/admin/articleController');
const categoryCtrl = require('../controllers/admin/categoryController');
const productCtrl = require('../controllers/admin/productController');
const pageCtrl = require('../controllers/admin/pageController');
const settingCtrl = require('../controllers/admin/settingController');

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, path.join(__dirname, '../public/uploads')),
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, Date.now() + '-' + Math.round(Math.random() * 1e9) + ext);
  }
});
const upload = multer({
  storage,
  limits: { fileSize: parseInt(process.env.UPLOAD_MAX_SIZE) || 10485760 },
  fileFilter: (req, file, cb) => {
    const allowed = /jpeg|jpg|png|gif|webp/;
    cb(null, allowed.test(file.mimetype));
  }
});

// Auth
router.get('/login', authCtrl.loginPage);
router.post('/login', authCtrl.login);
router.get('/logout', authCtrl.logout);

// Protected routes
router.use(adminAuth);

router.get('/', dashCtrl.index);

// Articles
router.get('/articles', articleCtrl.index);
router.get('/articles/new', articleCtrl.newForm);
router.post('/articles', upload.single('featured_image'), articleCtrl.create);
router.get('/articles/:id/edit', articleCtrl.editForm);
router.post('/articles/:id', upload.single('featured_image'), articleCtrl.update);
router.post('/articles/:id/delete', articleCtrl.delete);
router.post('/upload/image', upload.single('image'), articleCtrl.uploadImage);

// Categories
router.get('/categories', categoryCtrl.index);
router.post('/categories', categoryCtrl.create);
router.get('/categories/:id/json', categoryCtrl.getOne);
router.post('/categories/:id', categoryCtrl.update);
router.post('/categories/:id/delete', categoryCtrl.delete);

// Products
router.get('/products', productCtrl.index);
router.get('/products/new', productCtrl.newForm);
router.post('/products', upload.single('image'), productCtrl.create);
router.get('/products/:id/edit', productCtrl.editForm);
router.post('/products/:id', upload.single('image'), productCtrl.update);
router.post('/products/:id/delete', productCtrl.delete);
router.post('/product-categories', productCtrl.createCategory);

// Pages
router.get('/pages', pageCtrl.index);
router.get('/pages/new', pageCtrl.newForm);
router.post('/pages', pageCtrl.create);
router.get('/pages/:id/edit', pageCtrl.editForm);
router.post('/pages/:id', pageCtrl.update);
router.post('/pages/:id/delete', pageCtrl.delete);

// Settings
router.get('/settings', settingCtrl.index);
router.post('/settings', settingCtrl.update);
router.post('/settings/generate-token', settingCtrl.generateApiToken);

module.exports = router;
