# Products Compare - Laravel Project

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A Laravel application for comparing products with a comprehensive set of features.

## 🚀 Quick Start

Follow these steps to get your development environment running:

### 1. Install Dependencies

```bash
composer install
```

This will install all PHP dependencies defined in `composer.json`.

### 2. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Then open `.env` and update the following settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=products_compare
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

This generates a secure application key for encryption.

### 4. Create Database

Create a new database named `products_compare`:

**MySQL:**
```sql
CREATE DATABASE products_compare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Or via command line:**
```bash
mysql -u your_username -p -e "CREATE DATABASE products_compare"
```

### 5. Run Migrations

```bash
php artisan migrate
```

This will create all necessary database tables.

### 6. Run Tests

Verify everything is working correctly:

```bash
php artisan test
```

All tests should pass ✅

### 7. Seed Database

Populate the database with sample data:

```bash
php artisan db:seed
```

### 8. Start Development Server

```bash
php artisan serve
```

Your application will be available at: **http://localhost:8000**

## 🎉 You're All Set!

Open your browser and navigate to [http://localhost:8000](http://localhost:8000) to see your application running.

## ⚠️ Known Issues

**Known issue:** On the very first *“Add to compare”* followed by an immediate navigation to */compare*, the compare list may appear *empty*.  
This is a race condition between the session cookie creation (*POST /api/compare/add*) and the page navigation.  
Subsequent attempts work as expected. 