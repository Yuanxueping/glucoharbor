#!/bin/bash
# GlucoHarbor Setup Script for BaoTa (宝塔) server

set -e

echo "=== GlucoHarbor Setup ==="

# 1. Check Node.js
node --version || { echo "Node.js not found. Install via BaoTa panel."; exit 1; }

# 2. Install dependencies
echo "Installing dependencies..."
npm install --production

# 3. Create .env if not exists
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example"
  echo "IMPORTANT: Edit .env with your database credentials before continuing!"
  echo "Run: nano .env"
  exit 0
fi

# 4. Create required directories
mkdir -p public/uploads logs

# 5. Check database connection and run migrations
echo "Checking database..."
DB_NAME=$(grep DB_NAME .env | cut -d '=' -f2)
DB_USER=$(grep DB_USER .env | cut -d '=' -f2)
DB_PASS=$(grep DB_PASS .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
  echo "Please configure database settings in .env first"
  exit 1
fi

echo "Creating database $DB_NAME if not exists..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true

echo "Running database migrations..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < install.sql

# 6. Create admin user
echo ""
echo "Creating admin account..."
node -e "
require('dotenv').config();
const User = require('./models/User');
const Setting = require('./models/Setting');
(async () => {
  try {
    const count = await User.count();
    if (count === 0) {
      const username = process.env.ADMIN_USERNAME || 'admin';
      const email = process.env.ADMIN_EMAIL || 'admin@glucoharbor.com';
      const password = process.env.ADMIN_PASSWORD || 'Admin@123456';
      await User.create({ username, email, password, role: 'admin' });
      console.log('Admin user created: ' + username + ' / ' + password);
    } else {
      console.log('Admin user already exists.');
    }
    process.exit(0);
  } catch(e) { console.error(e.message); process.exit(1); }
})();
"

# 7. Install PM2 if not installed
npm list -g pm2 &>/dev/null || npm install -g pm2

# 8. Start with PM2
echo "Starting application with PM2..."
pm2 start ecosystem.config.js
pm2 save
pm2 startup

echo ""
echo "=== Setup Complete! ==="
echo "Site: http://YOUR_SERVER_IP:3000"
echo "Admin: http://YOUR_SERVER_IP:3000/admin"
echo ""
echo "Next steps:"
echo "1. Configure Nginx (see nginx.conf.example)"
echo "2. Set up SSL via BaoTa panel"
echo "3. Update APP_URL in .env to your domain"
echo "4. pm2 restart glucoharbor"
