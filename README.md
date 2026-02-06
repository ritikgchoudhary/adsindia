# AdsIndia - Vue.js + Laravel API Project

A modern affiliate marketing platform built with Vue.js frontend and Laravel API backend.

## 🚀 Project Structure

```
/www/wwwroot/a22-com.site/
├── Old Project/          # Original project backup
├── assets/               # Static assets (CSS, JS, images)
├── core/                 # Laravel API backend
├── frontend/             # Vue.js frontend
├── public/               # Built Vue.js files (generated)
├── index.php             # Entry point
└── .htaccess             # Apache configuration
```

## 🛠️ Tech Stack

- **Frontend**: Vue.js 3, Vue Router, Pinia, Vite
- **Backend**: Laravel 11, PHP 8.2+
- **Database**: MySQL/MariaDB
- **Web Server**: Apache/Nginx

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL/MariaDB

### Backend Setup

```bash
cd core
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env
# Update VITE_API_URL in .env
npm run dev
```

### Production Build

```bash
cd frontend
npm run build
```

## 🔧 Configuration

### Environment Variables

**Backend** (`core/.env`):
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Frontend** (`frontend/.env`):
```env
VITE_API_URL=http://localhost:8000/api
```

## 📝 Features

- ✅ User Authentication (Login/Register)
- ✅ Campaign Management
- ✅ Dashboard
- ✅ Contact Forms
- ✅ Blog System
- ✅ Responsive Design
- ✅ API Integration

## 🗂️ Routes

### Frontend Routes
- `/` - Homepage
- `/user/login` - Login
- `/user/register` - Register
- `/user/dashboard` - Dashboard
- `/campaigns` - Campaigns listing
- `/contact` - Contact page

### API Routes
- `/api/*` - All API endpoints

## 🚀 Deployment

1. Build Vue.js app: `cd frontend && npm run build`
2. Set proper permissions: `chmod -R 755 public`
3. Configure web server (Apache/Nginx)
4. Update environment variables
5. Run migrations: `php artisan migrate`

## 📄 License

This project is proprietary software.

## 👥 Contributors

- Development Team

## 📞 Support

For support, contact the development team.
