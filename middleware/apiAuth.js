const Setting = require('../models/Setting');

module.exports = async (req, res, next) => {
  const token = req.headers['x-api-token'] || req.query.api_token;
  const storedToken = await Setting.get('api_token');
  if (!storedToken || !token || token !== storedToken) {
    return res.status(401).json({ success: false, message: 'Unauthorized: Invalid API token' });
  }
  next();
};
