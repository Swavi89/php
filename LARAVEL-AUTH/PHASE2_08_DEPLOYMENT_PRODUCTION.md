# Phase 2: Laravel Breeze with Bootstrap - Part 8: Deployment & Production

## Table of Contents
1. [Production Environment Setup](#production-environment-setup)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Environment Configuration](#environment-configuration)
4. [Security Hardening](#security-hardening)
5. [Performance Optimization](#performance-optimization)
6. [Deployment Process](#deployment-process)
7. [Post-Deployment Verification](#post-deployment-verification)
8. [Monitoring & Maintenance](#monitoring--maintenance)

---

## Production Environment Setup

### Server Requirements

**Minimum Server Specifications:**

```
Web Server:
├─ Apache 2.4+ or Nginx 1.18+
├─ PHP 8.3+
│  ├─ OpenSSL PHP Extension
│  ├─ PDO PHP Extension
│  ├─ Mbstring PHP Extension
│  ├─ Tokenizer PHP Extension
│  ├─ XML PHP Extension
│  ├─ Ctype PHP Extension
│  ├─ JSON PHP Extension
│  └─ BCMath PHP Extension
├─ MySQL 8.0+ or PostgreSQL 13+
├─ Composer 2.x
├─ Node.js 18+ and npm (for building assets)
└─ SSL Certificate (required for HTTPS)

Recommended:
├─ 2+ CPU cores
├─ 4+ GB RAM
├─ 20+ GB SSD storage
└─ Redis for caching (optional but recommended)
```

### Hosting Options

**Option 1: Shared Hosting**
```
Pros:
✅ Affordable ($5-20/month)
✅ Easy to set up
✅ Managed infrastructure

Cons:
❌ Limited control
❌ May not support latest PHP
❌ Performance limitations

Recommended for: Small projects, personal sites
```

**Option 2: VPS (Virtual Private Server)**
```
Providers: DigitalOcean, Linode, Vultr, AWS Lightsail
Cost: $5-50/month

Pros:
✅ Full control
✅ Scalable
✅ Good performance
✅ Root access

Cons:
❌ Requires server management
❌ Need security knowledge

Recommended for: Medium projects, production apps
```

**Option 3: Platform-as-a-Service**
```
Providers: Laravel Forge, Heroku, Platform.sh, Cloudways

Pros:
✅ Easy deployment
✅ Managed infrastructure
✅ Auto-scaling
✅ Built-in monitoring

Cons:
❌ More expensive
❌ Some limitations

Recommended for: Professional projects, teams
```

---

## Pre-Deployment Checklist

### Code Preparation

**1. Version Control**
```powershell
# Ensure all code committed
git status

# Tag release version
git tag -a v1.0.0 -m "Production release v1.0.0"
git push origin v1.0.0
```

**2. Remove Development Dependencies**
```powershell
# Install only production packages
composer install --no-dev --optimize-autoloader

# Build production assets
npm run build
```

**3. Code Review**
```
□ No debug code (dd(), dump(), var_dump())
□ No commented-out code
□ No hardcoded credentials
□ No sensitive data in git
□ All routes properly protected
□ All forms have CSRF
□ All validation implemented
```

**4. Testing**
```
□ All manual tests passed
□ No console errors
□ All features working
□ Responsive design verified
□ Cross-browser tested
□ Performance acceptable
```

### Database Preparation

**1. Backup Development Database**
```powershell
# Export database
mysqldump -u root -p breeze_auth_demo > backup_dev.sql
```

**2. Fresh Production Migration**
```powershell
# Test fresh migration
php artisan migrate:fresh

# Verify all tables created
php artisan tinker
```

```php
Schema::getTables();
exit
```

**3. Seed Production Data (if needed)**
```powershell
php artisan db:seed --class=ProductionSeeder
```

---

## Environment Configuration

### Production .env File

**File:** `.env`

**Critical changes for production:**

```env
# Application
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=production_database_name
DB_USERNAME=production_db_user
DB_PASSWORD=strong_random_password_here

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Session & Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AWS (if using)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_SLACK_WEBHOOK_URL=
```

**Important notes:**

```
⚠️ Never commit .env to git!
⚠️ APP_DEBUG must be false in production
⚠️ Use strong random APP_KEY
⚠️ Use HTTPS (APP_URL must start with https://)
⚠️ Use strong database credentials
⚠️ Configure real mail service
```

### Generate Application Key

```powershell
php artisan key:generate
```

**This creates unique encryption key for:**
- Session encryption
- Password hashing
- Secure cookies
- Remember tokens

---

## Security Hardening

### 1. HTTPS/SSL Configuration

**Why HTTPS is required:**
```
Without HTTPS:
├─ Passwords transmitted in plain text
├─ Session tokens visible
├─ CSRF tokens exposed
├─ Man-in-the-middle attacks possible
└─ ❌ EXTREMELY INSECURE

With HTTPS:
├─ All traffic encrypted
├─ Passwords secure
├─ Session security
├─ Browser trust
└─ ✅ SECURE
```

**Force HTTPS:**

**File:** `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

**Apache .htaccess:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [L,R=301]
    
    # Laravel routing
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Nginx configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/key.pem;
    
    root /var/www/yourapp/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 2. File Permissions

**Set proper permissions:**

```bash
# Application files
chmod -R 755 /var/www/yourapp

# Storage and cache need write access
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Set ownership
chown -R www-data:www-data /var/www/yourapp
```

**Security best practices:**
```
✅ Never 777 permissions
✅ Storage writable only by web server
✅ .env not web-accessible
✅ Only /public accessible from web
```

### 3. Hide Sensitive Files

**File:** `public/.htaccess`

```apache
# Deny access to sensitive files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "composer\.(json|lock)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes
```

### 4. Security Headers

**File:** `app/Http/Middleware/SecurityHeaders.php`

**Create middleware:**

```powershell
php artisan make:middleware SecurityHeaders
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}
```

**Register middleware:**

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

### 5. Rate Limiting Enhancement

**File:** `app/Providers/RouteServiceProvider.php`

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiting(): void
{
    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->input('email').$request->ip());
    });
    
    RateLimiter::for('register', function (Request $request) {
        return Limit::perMinute(3)->by($request->ip());
    });
}
```

**Apply to routes:**

```php
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:login');
    
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('throttle:register');
```

---

## Performance Optimization

### 1. Cache Configuration

**File:** `.env`

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

**Install Redis (Ubuntu):**

```bash
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

**Install PHP Redis extension:**

```bash
sudo apt install php8.3-redis
```

### 2. Optimize Autoloader

```powershell
composer install --optimize-autoloader --no-dev
```

**This creates optimized class map for faster autoloading**

### 3. Cache Routes and Config

```powershell
# Cache routes
php artisan route:cache

# Cache config
php artisan config:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache
```

**⚠️ Important:**
```
After any config changes in production:
php artisan config:clear
php artisan config:cache
```

### 4. Asset Optimization

**Build optimized assets:**

```powershell
npm run build
```

**This creates:**
- Minified CSS
- Minified JavaScript
- Fingerprinted files (cache busting)
- Optimized images

**Result:**
```
public/build/
├── manifest.json
├── assets/
│   ├── app-[hash].css    (minified)
│   └── app-[hash].js     (minified)
```

### 5. Database Query Optimization

**Enable query caching:**

```php
// In controllers
$users = User::remember(60)->get(); // Cache for 60 minutes
```

**Use eager loading to prevent N+1 queries:**

```php
// Bad (N+1 query problem)
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // Query for each user!
}

// Good (Eager loading)
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // No extra queries
}
```

### 6. Enable OPcache

**File:** `/etc/php/8.3/fpm/php.ini`

```ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
```

**Restart PHP-FPM:**

```bash
sudo systemctl restart php8.3-fpm
```

---

## Deployment Process

### Manual Deployment Steps

**1. Upload files to server:**

```bash
# Using Git (recommended)
cd /var/www/yourapp
git clone https://github.com/yourusername/yourapp.git .
git checkout v1.0.0

# Or using FTP/SFTP
# Upload all files except:
# - node_modules/
# - .env
# - storage/ (create fresh)
# - vendor/ (install fresh)
```

**2. Install dependencies:**

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data /var/www/yourapp
```

**3. Build assets (if not built locally):**

```bash
npm install
npm run build
```

**4. Configure environment:**

```bash
# Copy and edit .env
cp .env.example .env
nano .env  # Edit with production settings

# Generate key
php artisan key:generate
```

**5. Run migrations:**

```bash
# ⚠️ WARNING: This will affect database!
php artisan migrate --force

# Or rollback and fresh (destroys data!)
php artisan migrate:fresh --force
```

**6. Optimize for production:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**7. Link storage:**

```bash
php artisan storage:link
```

**8. Restart services:**

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

### Automated Deployment (Laravel Forge/Envoyer)

**Deployment script:**

```bash
cd /home/forge/yourdomain.com

# Maintenance mode
php artisan down --retry=60

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers
php artisan queue:restart

# Exit maintenance mode
php artisan up
```

---

## Post-Deployment Verification

### Deployment Checklist

**1. Application Health:**
```
□ Website loads (https://yourdomain.com)
□ SSL certificate valid (green padlock)
□ No PHP errors
□ No 404 errors
□ Assets loading (CSS, JS, images)
```

**2. Authentication:**
```
□ Registration works
□ Login works
□ Logout works
□ Password reset works
□ Email verification works
```

**3. Database:**
```
□ Migrations ran successfully
□ All tables exist
□ Can create users
□ Data persists
```

**4. Email:**
```
□ Password reset emails send
□ Verification emails send
□ Email templates correct
□ From address correct
```

**5. Performance:**
```
□ Page load < 2 seconds
□ No slow queries
□ Cache working
□ Assets compressed
```

**6. Security:**
```
□ HTTPS working
□ APP_DEBUG=false
□ .env not accessible
□ Security headers set
□ Rate limiting active
```

### Testing in Production

**Test registration:**
```
1. Visit https://yourdomain.com/register
2. Create account
3. Verify email sent
4. Verify login works
```

**Test password reset:**
```
1. Logout
2. Click "Forgot Password"
3. Enter email
4. Check reset email arrives
5. Reset password
6. Login with new password
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Monitor errors:**
```bash
# Real-time error monitoring
tail -f /var/log/nginx/error.log
```

---

## Monitoring & Maintenance

### 1. Application Monitoring

**Laravel Telescope (Development/Staging):**

```powershell
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**⚠️ Only enable in development/staging, not production!**

**Production monitoring tools:**
```
- Laravel Pulse (official, lightweight)
- Bugsnag (error tracking)
- Sentry (error monitoring)
- New Relic (APM)
- DataDog (infrastructure monitoring)
```

### 2. Log Management

**File:** `config/logging.php`

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'error',
        'days' => 14,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
    ],
],
```

**Monitor logs:**

```bash
# View latest logs
tail -100 storage/logs/laravel.log

# Watch logs live
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

### 3. Database Backups

**Automated backup script:**

```bash
#!/bin/bash
# File: /home/youruser/backup-db.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/youruser/backups"
DB_NAME="production_database_name"
DB_USER="production_db_user"
DB_PASS="production_db_password"

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Delete backups older than 30 days
find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -delete
```

**Make executable:**

```bash
chmod +x /home/youruser/backup-db.sh
```

**Schedule with cron:**

```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /home/youruser/backup-db.sh
```

### 4. Security Updates

**Regular maintenance:**

```bash
# Update Composer packages
composer update

# Update npm packages
npm update

# Check for security vulnerabilities
composer audit
npm audit
```

**Create maintenance schedule:**
```
Weekly:
□ Review logs for errors
□ Check disk space
□ Monitor performance

Monthly:
□ Update dependencies
□ Review security advisories
□ Test backups
□ Review user accounts

Quarterly:
□ Full security audit
□ Performance optimization
□ Code review
□ Update documentation
```

---

## Rollback Strategy

### Quick Rollback Steps

**If deployment fails:**

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Rollback to previous version
git checkout previous-tag-or-commit

# 3. Reinstall dependencies
composer install --no-dev

# 4. Rollback migrations (if needed)
php artisan migrate:rollback --step=1

# 5. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 6. Exit maintenance mode
php artisan up
```

### Database Rollback

**Restore from backup:**

```bash
# Decompress backup
gunzip backup_20251215_020000.sql.gz

# Restore database
mysql -u root -p production_database_name < backup_20251215_020000.sql
```

---

## Production Troubleshooting

### Issue 1: 500 Internal Server Error

**Check:**
```bash
# Laravel log
tail -50 storage/logs/laravel.log

# Web server log
tail -50 /var/log/nginx/error.log

# PHP-FPM log
tail -50 /var/log/php8.3-fpm.log
```

**Common causes:**
- Permission issues (storage/ not writable)
- Missing .env file
- Invalid configuration
- PHP memory limit

### Issue 2: Database Connection Failed

**Check:**
```
□ Database credentials in .env correct
□ Database server running
□ Firewall allows database connection
□ Database exists
□ User has permissions
```

### Issue 3: Assets Not Loading

**Check:**
```
□ npm run build executed
□ public/build/ directory exists
□ Web server serves static files
□ Permissions correct on public/
```

### Issue 4: Emails Not Sending

**Check:**
```
□ MAIL_* settings in .env
□ Mail server credentials correct
□ Firewall allows SMTP
□ Queue worker running (if using queues)
```

---

## Final Checklist

### Pre-Launch

```
□ All code tested
□ Database migrated
□ .env configured for production
□ APP_DEBUG=false
□ HTTPS enabled
□ SSL certificate installed
□ Email tested
□ Backups configured
□ Monitoring setup
□ Error tracking enabled
□ Performance optimized
□ Security headers set
□ Rate limiting active
□ Documentation updated
□ Team trained
```

### Post-Launch

```
□ Monitor logs first 24 hours
□ Check error rates
□ Monitor performance
□ Test all critical paths
□ Verify emails sending
□ Check database performance
□ Monitor server resources
□ Test backups
□ Update status page
□ Inform stakeholders
```

---

## Congratulations! 🎉

**You have successfully:**
- ✅ Installed Laravel Breeze
- ✅ Integrated Bootstrap 5
- ✅ Converted all views
- ✅ Implemented complete authentication
- ✅ Added profile management
- ✅ Tested thoroughly
- ✅ Deployed to production

**Phase 2 Complete!**

---

## Next Steps: Phase 3

**Ready for advanced features?**

📝 **Continue to Phase 3:**
[PHASE3_01_OVERVIEW.md](PHASE3_01_OVERVIEW.md)

**Phase 3 includes:**
- Email verification implementation
- Password reset enhancement
- Rate limiting advanced
- Two-factor authentication (2FA)
- Security hardening
- Advanced features

---

## Quick Reference

### Production Commands

```bash
# Deployment
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Maintenance
php artisan down
php artisan up

# Cache clearing
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Monitoring
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log
```

### Important Files

```
.env                    # Environment config (never commit!)
public/.htaccess        # Apache rewrite rules
storage/logs/           # Application logs
bootstrap/cache/        # Framework cache
config/                 # Configuration files
```

### Support Resources

```
Laravel Documentation:  https://laravel.com/docs
Laravel Forums:         https://laracasts.com/discuss
Laravel Discord:        https://discord.gg/laravel
Stack Overflow:         https://stackoverflow.com/questions/tagged/laravel
```

---

**Phase 2 Documentation Complete!** You now have a production-ready Laravel Breeze application with Bootstrap 5.
