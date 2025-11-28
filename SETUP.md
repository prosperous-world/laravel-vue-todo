# Quick Setup Guide

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+ and npm
- MySQL 8+

## Step-by-Step Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_todo
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate
```

### 4. Start Development Servers

**Terminal 1 - Laravel Backend:**
```bash
php artisan serve
```
Backend will run on: http://localhost:8000

**Terminal 2 - Vite Frontend:**
```bash
npm run dev
```
Frontend will run on: http://localhost:5173

### 5. Access the Application

Open your browser and navigate to: **http://localhost:5173**

### 6. Run Tests (Optional)

```bash
php artisan test
```

## Troubleshooting

### Issue: "Class not found" errors
**Solution:** Run `composer dump-autoload`

### Issue: Vite not connecting to backend
**Solution:** Make sure both servers are running and check CORS settings in `config/sanctum.php`

### Issue: Database connection error
**Solution:** Verify your `.env` database credentials match your MySQL setup

### Issue: 419 CSRF token mismatch
**Solution:** Clear cache: `php artisan cache:clear` and `php artisan config:clear`

## Production Build

```bash
# Build frontend assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

