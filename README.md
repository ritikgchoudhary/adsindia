# A22.com - Affiliate Platform

Vue.js 3 Frontend + Laravel API Backend

## 🚀 Quick Start

### Backend Setup (Laravel)
```bash
cd core
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan optimize:clear
```

### Frontend Setup (Vue.js)
```bash
cd frontend
npm install
npm run build  # For production
# OR
npm run dev    # For development
```

## 📁 Project Structure

```
├── core/              # Laravel API Backend
├── frontend/          # Vue.js 3 Frontend
├── assets/            # Static assets (CSS, JS, images)
├── public/            # Vue.js build output
└── index.php          # Entry point
```

## 🔧 Auto Commit

### Option 1: Post-Commit Hook (Automatic Push)
After each `git commit`, changes are automatically pushed to GitHub.

### Option 2: Manual Auto-Commit Script
```bash
./auto-commit.sh
```

This script will:
- Add all changes
- Commit with timestamp
- Push to GitHub

## 🌐 API Endpoints

### Public Endpoints
- `GET /api/general-setting` - General settings
- `GET /api/sections/{key}` - Get sections (footer, account_modal, etc.)
- `GET /api/policies` - Get all policies
- `GET /api/custom-pages` - Get custom pages
- `POST /api/login` - User login
- `POST /api/register` - User registration

### Protected Endpoints (Requires Auth Token)
- `GET /api/dashboard` - User dashboard
- `GET /api/user-info` - User information
- `GET /api/logout` - Logout

## 🔐 Demo Account

**Username:** `demo`  
**Email:** `demo@example.com`  
**Password:** `demo123`

## 📝 Development

### Laravel API
```bash
cd core
php artisan serve  # Runs on http://localhost:8000
```

### Vue.js Frontend
```bash
cd frontend
npm run dev  # Runs on http://localhost:3000
```

## 🚀 Production Build

```bash
# Build Vue.js app
cd frontend
npm run build

# Files will be in ../public/
```

## 📦 Tech Stack

- **Frontend:** Vue.js 3, Vue Router, Pinia, Axios, Vite
- **Backend:** Laravel (API only)
- **Authentication:** Laravel Sanctum
- **Database:** MySQL/MariaDB

## 📄 License

Proprietary
