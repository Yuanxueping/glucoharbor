const express = require('express');
const router = express.Router();
const homeCtrl = require('../controllers/frontend/homeController');
const articleCtrl = require('../controllers/frontend/articleController');
const productCtrl = require('../controllers/frontend/productController');
const pageCtrl = require('../controllers/frontend/pageController');

router.get('/', homeCtrl.index);
router.get('/search', homeCtrl.search);
router.get('/sitemap.xml', pageCtrl.sitemap);
router.get('/articles', articleCtrl.list);
router.get('/category/:slug', articleCtrl.byCategory);
router.get('/article/:slug', articleCtrl.detail);
router.get('/products', productCtrl.list);
router.get('/product/:slug', productCtrl.detail);
router.get('/page/:slug', pageCtrl.show);

module.exports = router;
