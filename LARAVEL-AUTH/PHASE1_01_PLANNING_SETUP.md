# Phase 1: Manual Authentication - Part 1: Planning & Setup

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Planning Phase](#planning-phase)
4. [Laravel Installation](#laravel-installation)
5. [Environment Configuration](#environment-configuration)
6. [Testing Installation](#testing-installation)

---

## Overview

**Phase 1 Goals:**
- Build authentication system from scratch WITHOUT packages
- Understand core authentication concepts
- Implement session-based authentication manually
- Learn security best practices
- Prepare foundation for advanced features

**What You'll Build:**
- User registration system
- Login/logout functionality
- Session management
- Password hashing (bcrypt)
- CSRF protection
- Middleware for route protection
- Authentication logging

**Why Manual First?**
- Deep understanding of authentication flow
- Learn security fundamentals
- Appreciate what packages do for you
- Debug issues confidently
- Customize authentication logic

---

## Prerequisites

### Required Software

**1. PHP 8.x (Minimum 8.1)**

Check if installed:
```powershell
php -v
```

Expected output:
```
PHP 8.3.26 (cli) (built: Nov 20 2024 19:47:33) (NTS Visual C++ 2019 x64)
```

**Download:** https://windows.php.net/download/

**Installation Tips:**
- Download "Non-Thread Safe" (NTS) for development
- Choose x64 for 64-bit Windows
- Extract to `C:\php-8.3.26\`
- Add to System PATH environment variable

**2. Composer (PHP Package Manager)**

Check if installed:
```powershell
composer --version
```

Expected output:
```
Composer version 2.8.6 2024-11-05 10:24:44
```

**Download:** https://getcomposer.org/download/

**3. MySQL/MariaDB**

Check if installed:
```powershell
mysql --version
```

**Download Options:**
- MySQL: https://dev.mysql.com/downloads/mysql/
- XAMPP: https://www.apachefriends.org/ (includes MySQL)
- Laragon: https://laragon.org/ (includes everything)

**4. Code Editor**

Recommended: Visual Studio Code
- Download: https://code.visualstudio.com/

**Useful Extensions:**
- PHP Intelephense
- Laravel Blade Snippets
- Laravel Snippets
- PHP Debug

### Knowledge Prerequisites

**Basic Understanding Of:**
- PHP syntax and OOP
- HTML/CSS
- Basic SQL
- HTTP methods (GET, POST)
- Sessions and cookies concept

**Nice to Have:**
- MVC pattern knowledge
- Command line basics
- Git basics

---

## Planning Phase

### Architecture Overview

```
┌─────────────────────────────────────────────────┐
│              Browser (Client)                    │
└───────────────┬─────────────────────────────────┘
                │
                │ HTTP Request (email, password)
                │
┌───────────────▼─────────────────────────────────┐
│              Routes (web.php)                    │
│  - /register (GET, POST)                        │
│  - /login (GET, POST)                           │
│  - /logout (POST)                               │
│  - /dashboard (GET) - Protected                 │
└───────────────┬─────────────────────────────────┘
                │
                │ Route to Controller
                │
┌───────────────▼─────────────────────────────────┐
│     ManualAuthController                        │
│  - showRegister()                               │
│  - register()                                   │
│  - showLogin()                                  │
│  - login()                                      │
│  - logout()                                     │
│  - dashboard()                                  │
└───────────────┬─────────────────────────────────┘
                │
    ┌───────────┴───────────┐
    │                       │
┌───▼──────┐         ┌──────▼────┐
│  Model   │         │  Session  │
│  (User)  │         │  Storage  │
└───┬──────┘         └───────────┘
    │
    │ Query Database
    │
┌───▼─────────────────────────────┐
│     Database (MySQL)             │
│  - users table                   │
│    * id                          │
│    * name                        │
│    * email (unique)              │
│    * password (hashed)           │
│    * created_at                  │
│    * updated_at                  │
└──────────────────────────────────┘
```

### Authentication Flow

#### Registration Flow:
```
1. User visits /register → showRegister()
2. User fills form (name, email, password)
3. Form submits POST /register → register()
4. Validate input (required, email format, unique email, password length)
5. Hash password with bcrypt
6. Insert user into database
7. Create session (auto-login)
8. Redirect to /dashboard
```

#### Login Flow:
```
1. User visits /login → showLogin()
2. User enters email + password
3. Form submits POST /login → login()
4. Validate input
5. Find user by email
6. Verify password (Hash::check)
7. If valid: Create session
8. Redirect to /dashboard
9. If invalid: Redirect back with error
```

#### Logout Flow:
```
1. User clicks logout button
2. POST /logout → logout()
3. Destroy session
4. Redirect to /login
```

#### Protected Route Access:
```
1. User visits /dashboard
2. Middleware checks session
3. If authenticated: Show dashboard
4. If not: Redirect to /login
```

### Security Considerations

**1. Password Security:**
- ✅ Use bcrypt hashing (never store plain text)
- ✅ Minimum 8 characters
- ✅ Hash::make() and Hash::check()

**2. CSRF Protection:**
- ✅ Laravel provides @csrf token
- ✅ Validates on POST/PUT/DELETE
- ✅ Prevents cross-site request forgery

**3. Session Security:**
- ✅ Secure session configuration
- ✅ HttpOnly cookies
- ✅ Session regeneration on login

**4. Input Validation:**
- ✅ Validate all user input
- ✅ Sanitize data
- ✅ Use Laravel's validator

**5. SQL Injection Prevention:**
- ✅ Use Laravel's Query Builder
- ✅ Never concatenate SQL queries

### File Structure Plan

```
auth-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   │       └── ManualAuthController.php
│   │   └── Middleware/
│   │       └── ManualAuthMiddleware.php
│   └── Models/
│       └── User.php
├── database/
│   └── migrations/
│       └── 2014_10_12_000000_create_users_table.php
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── manual/
│       │   │   ├── register.blade.php
│       │   │   ├── login.blade.php
│       │   │   └── dashboard.blade.php
│       └── layouts/
│           └── app.blade.php
└── routes/
    └── web.php
```

---

## Laravel Installation

### Step 1: Create New Laravel Project

Open PowerShell and navigate to your projects directory:

```powershell
# Navigate to your development folder
cd D:\

# Create new Laravel project
composer create-project laravel/laravel auth-app

# Navigate into project
cd auth-app
```

**What Happens:**
- Composer downloads Laravel and ~107 dependencies
- Creates project structure
- Generates application key
- Sets up environment file (.env)

**Expected Output:**
```
Installing laravel/laravel (v11.x)
  - Installing laravel/laravel (v11.x): Extracting archive
Created project in D:\auth-app
> @php artisan key:generate --ansi
Application key set successfully.
```

**Time:** 2-5 minutes (depending on internet speed)

### Step 2: Verify Installation

```powershell
# Check Laravel version
php artisan --version
```

Expected:
```
Laravel Framework 11.47.0
```

### Step 3: Understand Project Structure

```
auth-app/
├── app/                    # Application core
│   ├── Http/              # Controllers, Middleware
│   ├── Models/            # Database models
│   └── Providers/         # Service providers
├── bootstrap/             # Framework bootstrap
├── config/                # Configuration files
├── database/              # Migrations, seeders
├── public/                # Public assets (index.php)
├── resources/             # Views, assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── views/            # Blade templates
├── routes/                # Route definitions
│   └── web.php           # Web routes
├── storage/               # Logs, cache, sessions
├── tests/                 # Automated tests
├── vendor/                # Composer dependencies
├── .env                   # Environment variables
├── artisan               # CLI tool
├── composer.json         # PHP dependencies
└── package.json          # Node dependencies
```

---

## Environment Configuration

### Step 1: Configure .env File

Open `.env` in VS Code:

```powershell
code .env
```

### Step 2: Update Database Configuration

**Default .env database section:**
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

**Update to MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth_demo
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

**Configuration Explained:**
- `DB_CONNECTION` - Database driver (mysql, pgsql, sqlite)
- `DB_HOST` - Database server address (127.0.0.1 = localhost)
- `DB_PORT` - MySQL default port (3306)
- `DB_DATABASE` - Database name (we'll create this)
- `DB_USERNAME` - MySQL username (default: root)
- `DB_PASSWORD` - Your MySQL password (leave empty if no password)

### Step 3: Create Database

**Option A: Using MySQL Command Line**

```powershell
# Login to MySQL
mysql -u root -p

# Enter your password, then:
CREATE DATABASE laravel_auth_demo;
SHOW DATABASES;
EXIT;
```

**Option B: Using phpMyAdmin**

1. Open http://localhost/phpmyadmin
2. Click "New" in left sidebar
3. Database name: `laravel_auth_demo`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

**Option C: Using Laragon/XAMPP GUI**

1. Open Laragon → Database → MySQL
2. Create new database: `laravel_auth_demo`

### Step 4: Update App Configuration

**Set Application Name:**
```env
APP_NAME="Laravel Auth Demo"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost
```

**Configuration Explained:**
- `APP_NAME` - Displayed in browser title, emails
- `APP_ENV` - Environment (local, staging, production)
- `APP_DEBUG` - Show detailed errors (true for development)
- `APP_TIMEZONE` - Default timezone for timestamps
- `APP_URL` - Base URL of your application

### Step 5: Session Configuration

**Session Driver (default is fine):**
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

**Important:** We'll use database sessions for better tracking

### Step 6: Test Database Connection

```powershell
php artisan migrate
```

**Expected Output:**
```
   INFO  Preparing database.

  Creating migration table .................................. 32ms DONE

   INFO  Running migrations.

  2014_10_12_000000_create_users_table ....................... 45ms DONE
  2014_10_12_100000_create_password_reset_tokens_table ....... 28ms DONE
  2019_08_19_000000_create_failed_jobs_table ................. 31ms DONE
  2019_12_14_000001_create_personal_access_tokens_table ...... 42ms DONE
```

**If Successful:** Database connected! Tables created.

**If Error:** Check database credentials in .env

---

## Testing Installation

### Step 1: Start Development Server

```powershell
php artisan serve
```

**Expected Output:**
```
   INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

### Step 2: Visit Application

Open browser: http://127.0.0.1:8000

**You should see:**
- Laravel welcome page
- No errors

### Step 3: Verify Database Tables

```powershell
# In separate terminal
mysql -u root -p laravel_auth_demo -e "SHOW TABLES;"
```

**Expected Tables:**
```
+-------------------------------+
| Tables_in_laravel_auth_demo   |
+-------------------------------+
| failed_jobs                   |
| migrations                    |
| password_reset_tokens         |
| personal_access_tokens        |
| users                         |
+-------------------------------+
```

### Step 4: Check Laravel Configuration

```powershell
php artisan about
```

**Expected Output:**
```
Environment ................................................ local
Debug Mode ................................................ ENABLED
URL ..................................... http://localhost:8000
Maintenance Mode ............................................. OFF

Database
Default Driver ............................................ mysql
Database ................................ laravel_auth_demo

Cache
Default Driver ............................................. file
```

---

## Common Installation Issues

### Issue 1: Composer Not Found

**Error:**
```
'composer' is not recognized as an internal or external command
```

**Solution:**
1. Download Composer: https://getcomposer.org/download/
2. Run installer
3. Restart PowerShell
4. Verify: `composer --version`

### Issue 2: PHP Not Found

**Error:**
```
'php' is not recognized as an internal or external command
```

**Solution:**
1. Add PHP to System PATH
2. System Properties → Environment Variables
3. Edit PATH → Add `C:\php-8.3.26\`
4. Restart PowerShell
5. Verify: `php -v`

### Issue 3: Database Connection Failed

**Error:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Solutions:**
1. Check MySQL is running
2. Verify DB_PASSWORD in .env
3. Create database: `CREATE DATABASE laravel_auth_demo;`
4. Test connection: `mysql -u root -p`

### Issue 4: Port 8000 Already in Use

**Error:**
```
Failed to listen on 127.0.0.1:8000
```

**Solution:**
```powershell
# Use different port
php artisan serve --port=8001

# Or find and kill process using port 8000
netstat -ano | findstr :8000
taskkill /PID <process_id> /F
```

### Issue 5: Application Key Not Set

**Error:**
```
No application encryption key has been specified.
```

**Solution:**
```powershell
php artisan key:generate
```

---

## Next Steps

✅ **Completed:**
- Laravel installed
- Database configured
- Server running
- Environment setup

📝 **Next Document:**
[PHASE1_02_DATABASE_MIGRATIONS.md](PHASE1_02_DATABASE_MIGRATIONS.md)

**You will learn:**
- Database migration creation
- users table structure
- Migration commands
- Rollback and reset
- User model setup

---

## Quick Reference

### Essential Commands

```powershell
# Create project
composer create-project laravel/laravel auth-app

# Database
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Drop all tables and re-migrate
php artisan migrate:rollback     # Undo last migration

# Server
php artisan serve                # Start server
php artisan serve --port=8001    # Custom port

# Configuration
php artisan config:clear         # Clear config cache
php artisan cache:clear          # Clear application cache
php artisan route:clear          # Clear route cache

# Info
php artisan --version            # Laravel version
php artisan about                # System info
php artisan route:list           # List all routes
```

### File Locations

| What | Where |
|------|-------|
| Environment variables | `.env` |
| Web routes | `routes/web.php` |
| Controllers | `app/Http/Controllers/` |
| Models | `app/Models/` |
| Views | `resources/views/` |
| Migrations | `database/migrations/` |
| Configuration | `config/` |
| Public assets | `public/` |

---

**Installation Complete!** Proceed to Part 2 for database and migrations setup.
