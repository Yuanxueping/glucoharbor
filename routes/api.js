const express = require('express');
const router = express.Router();
const apiAuth = require('../middleware/apiAuth');
const importCtrl = require('../controllers/api/importController');

router.get('/categories', apiAuth, importCtrl.getCategories);
router.post('/articles/import', apiAuth, importCtrl.importArticle);
router.post('/articles/import-batch', apiAuth, importCtrl.importBatch);

module.exports = router;
