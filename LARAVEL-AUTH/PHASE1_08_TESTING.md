# Phase 1: Manual Authentication - Part 8: Testing & Deployment

## Table of Contents
1. [Manual Testing Guide](#manual-testing-guide)
2. [Testing Checklist](#testing-checklist)
3. [Common Errors & Solutions](#common-errors-solutions)
4. [Debugging Workflow](#debugging-workflow)
5. [Security Checklist](#security-checklist)
6. [Production Deployment](#production-deployment)
7. [Performance Optimization](#performance-optimization)

---

## Manual Testing Guide

### Pre-Testing Setup

**1. Clear all caches:**
```powershell
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

**2. Fresh database:**
```powershell
php artisan migrate:fresh
```

**3. Start server:**
```powershell
php artisan serve
```

**4. Open browser:**
```
http://127.0.0.1:8000
```

---

## Testing Checklist

### ✅ Registration Flow

**Test Case 1: Successful Registration**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Visit `/register` | Registration form displayed |
| 2 | Enter name: "Test User" | Field accepts input |
| 3 | Enter email: "test@example.com" | Field accepts input |
| 4 | Enter password: "password123" | Field shows dots (hidden) |
| 5 | Enter confirmation: "password123" | Field shows dots (hidden) |
| 6 | Click "Register" button | Redirect to dashboard |
| 7 | Check page | Shows "Welcome, Test User!" |
| 8 | Check navbar | Shows user name and logout button |

**Test Case 2: Validation Errors**

| Field | Input | Expected Error |
|-------|-------|----------------|
| Name | (empty) | "The name field is required." |
| Name | "AB" (too short) | Accepted (no min length) |
| Email | (empty) | "The email field is required." |
| Email | "notanemail" | "The email must be a valid email address." |
| Email | "test@example.com" (duplicate) | "The email has already been taken." |
| Password | "abc" | "The password must be at least 8 characters." |
| Password | "password123" + confirmation: "different" | "The password confirmation does not match." |

**Test Case 3: Duplicate Email**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Register user: test@example.com | Success |
| 2 | Try registering again with same email | Error: "The email has already been taken." |
| 3 | Check database | Only one user exists |

**Test Case 4: Auto-Login After Registration**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Complete registration | Redirect to dashboard |
| 2 | Check session | user_id, user_name, user_email set |
| 3 | Visit `/login` | Redirect to dashboard (already logged in) |

---

### ✅ Login Flow

**Test Case 1: Successful Login**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Visit `/login` | Login form displayed |
| 2 | Enter email: "test@example.com" | Field accepts input |
| 3 | Enter password: "password123" | Field shows dots |
| 4 | Click "Login" button | Redirect to dashboard |
| 5 | Check dashboard | Shows "Welcome back, Test User!" |

**Test Case 2: Invalid Credentials**

| Scenario | Email | Password | Expected Error |
|----------|-------|----------|----------------|
| User not found | "nonexistent@example.com" | "anything" | "These credentials do not match our records." |
| Wrong password | "test@example.com" | "wrongpassword" | "The provided password is incorrect." |
| Empty email | "" | "password123" | "The email field is required." |
| Invalid email format | "notanemail" | "password123" | "The email must be a valid email address." |

**Test Case 3: Remember Input on Error**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Enter email: "test@example.com" | - |
| 2 | Enter wrong password | - |
| 3 | Submit form | Shows error |
| 4 | Check email field | Still contains "test@example.com" |
| 5 | Check password field | Empty (for security) |

---

### ✅ Dashboard Access

**Test Case 1: Authenticated Access**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Login as user | Success |
| 2 | Visit `/dashboard` | Dashboard displayed |
| 3 | Check user info card | Shows correct name, email, role |
| 4 | Check session info | Shows session ID, IP |

**Test Case 2: Unauthenticated Access**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Logout completely | - |
| 2 | Clear cookies | - |
| 3 | Visit `/dashboard` directly | Redirect to `/login` |
| 4 | Check error message | "Please login to access this page." |

**Test Case 3: Guest Accessing Dashboard**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Open incognito/private window | - |
| 2 | Visit `/dashboard` | Redirect to `/login` |
| 3 | No session exists | Middleware blocks access |

---

### ✅ Logout Flow

**Test Case 1: Successful Logout**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Login as user | Dashboard shown |
| 2 | Click "Logout" button | Redirect to `/login` |
| 3 | Check message | "You have been logged out successfully." |
| 4 | Try visiting `/dashboard` | Redirect to `/login` |
| 5 | Check session | No user_id in session |

**Test Case 2: Logout Security**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Login as user | - |
| 2 | Get session ID | Note the ID |
| 3 | Logout | Session destroyed |
| 4 | Try using old session ID | Invalid, cannot access protected routes |

---

### ✅ Middleware Testing

**Test Case 1: Auth Middleware**

| Route | Logged In | Expected Result |
|-------|-----------|----------------|
| `/dashboard` | No | Redirect to `/login` |
| `/dashboard` | Yes | Show dashboard |
| `/logout` | No | Redirect to `/login` |
| `/logout` | Yes | Process logout |

**Test Case 2: Guest Middleware**

| Route | Logged In | Expected Result |
|-------|-----------|----------------|
| `/login` | No | Show login form |
| `/login` | Yes | Redirect to `/dashboard` |
| `/register` | No | Show register form |
| `/register` | Yes | Redirect to `/dashboard` |

---

### ✅ Security Testing

**Test Case 1: CSRF Protection**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | View login form source | Contains `<input name="_token">` |
| 2 | Submit form without @csrf | 419 error (CSRF token mismatch) |
| 3 | Submit with valid token | Success |

**Test Case 2: Password Hashing**

| Step | Action | Expected Result |
|------|--------|----------------|
| 1 | Register with password: "password123" | Success |
| 2 | Check database (phpMyAdmin/Tinker) | Password is hashed ($2y$...) |
| 3 | Verify plain text not stored | No "password123" in database |

**Test Case 3: SQL Injection Prevention**

| Step | Input | Expected Result |
|------|-------|----------------|
| 1 | Email: `admin' OR '1'='1` | Treated as string, not SQL |
| 2 | Submit login | User not found (safe) |
| 3 | No SQL error | Laravel Query Builder escaped input |

**Test Case 4: XSS Prevention**

| Step | Input | Expected Result |
|------|-------|----------------|
| 1 | Name: `<script>alert('XSS')</script>` | Registered |
| 2 | View dashboard | Shows escaped text, no alert |
| 3 | Check HTML source | `&lt;script&gt;...` (escaped) |

---

## Common Errors & Solutions

### Error 1: Route Not Found (404)

**Symptoms:**
```
404 | NOT FOUND
```

**Causes & Solutions:**

**A. Route not defined:**
```powershell
php artisan route:list
# Check if route exists
```

**B. URL typo:**
```
http://127.0.0.1:8000/loginn  ❌
http://127.0.0.1:8000/login   ✅
```

**C. Clear route cache:**
```powershell
php artisan route:clear
```

---

### Error 2: CSRF Token Mismatch (419)

**Symptoms:**
```
419 | PAGE EXPIRED
```

**Causes & Solutions:**

**A. Missing @csrf:**
```blade
<!-- ❌ WRONG -->
<form method="POST">
    <!-- no @csrf -->
</form>

<!-- ✅ CORRECT -->
<form method="POST">
    @csrf
</form>
```

**B. Session expired:**
- Reload page
- Clear cookies
- Login again

**C. Session driver misconfigured:**
```env
# Check .env
SESSION_DRIVER=file
```

---

### Error 3: View Not Found

**Symptoms:**
```
View [auth.manual.login] not found.
```

**Solutions:**

**A. Check file exists:**
```powershell
Test-Path "resources\views\auth\manual\login.blade.php"
# Should return: True
```

**B. Check file name:**
```
login.blade.php  ✅ (must have .blade.php)
login.php        ❌
```

**C. Clear view cache:**
```powershell
php artisan view:clear
```

---

### Error 4: Class Not Found

**Symptoms:**
```
Class "App\Http\Controllers\Auth\ManualAuthController" not found
```

**Solutions:**

**A. Import controller:**
```php
// At top of routes/web.php
use App\Http\Controllers\Auth\ManualAuthController;
```

**B. Check file exists:**
```powershell
Test-Path "app\Http\Controllers\Auth\ManualAuthController.php"
```

**C. Dump autoload:**
```powershell
composer dump-autoload
```

---

### Error 5: Database Connection Failed

**Symptoms:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Solutions:**

**A. Check .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_auth_demo
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

**B. Create database:**
```sql
CREATE DATABASE laravel_auth_demo;
```

**C. Test connection:**
```powershell
mysql -u root -p laravel_auth_demo
```

---

### Error 6: Validation Not Working

**Symptoms:**
- Form submits with empty fields
- No error messages shown

**Solutions:**

**A. Check validation rules:**
```php
$request->validate([
    'email' => 'required|email',  // Must be present
]);
```

**B. Display errors in view:**
```blade
@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
```

---

### Error 7: Redirect Loop

**Symptoms:**
```
ERR_TOO_MANY_REDIRECTS
```

**Causes & Solutions:**

**A. Wrong middleware on routes:**
```php
// ❌ WRONG - Auth middleware on login
Route::get('/login', [...])->middleware('manual.auth');

// ✅ CORRECT - Guest middleware on login
Route::get('/login', [...])->middleware('manual.guest');
```

**B. Middleware redirecting to itself:**
- Check middleware logic
- Ensure proper redirect routes

---

## Debugging Workflow

### Step 1: Identify the Problem

**Questions to ask:**
1. What were you trying to do?
2. What did you expect to happen?
3. What actually happened?
4. Any error messages?

### Step 2: Check Logs

```powershell
# View Laravel logs
Get-Content storage\logs\laravel.log -Tail 50

# Watch logs in real-time
Get-Content storage\logs\laravel.log -Wait
```

### Step 3: Use dd() for Debugging

```php
// In controller
public function login(Request $request)
{
    dd($request->all()); // Shows all input and stops
    
    // Or dump and continue
    dump($request->email);
    dump($request->password);
}
```

### Step 4: Check Database

```powershell
php artisan tinker
```

```php
// Check if user exists
App\Models\User::where('email', 'test@example.com')->first();

// Check all users
App\Models\User::all();

// Check session
session()->all();
```

### Step 5: Verify Routes

```powershell
php artisan route:list
```

### Step 6: Use Laravel Debugbar

Already installed! Shows queries, session, errors at bottom of page.

---

## Security Checklist

### ✅ Before Production

**1. Environment:**
```env
APP_ENV=production
APP_DEBUG=false
```

**2. HTTPS:**
- Use SSL certificate
- Force HTTPS in production

**3. Database:**
- Use strong passwords
- Restrict database access
- Regular backups

**4. Sessions:**
```env
SESSION_DRIVER=database  # More secure than file
SESSION_SECURE_COOKIE=true  # HTTPS only
```

**5. Passwords:**
- ✅ Using bcrypt (secure)
- ✅ Minimum 8 characters
- ✅ Never log passwords

**6. CSRF:**
- ✅ @csrf on all forms
- ✅ Enabled by default

**7. Input Validation:**
- ✅ Validate all user input
- ✅ Sanitize data
- ✅ Use Laravel's validator

**8. SQL Injection:**
- ✅ Using Query Builder/Eloquent
- ✅ Never raw SQL with user input

**9. XSS:**
- ✅ Using {{ }} (auto-escaped)
- ✅ Never use {!! !!} with user input

---

## Production Deployment

### Step 1: Prepare Environment

**Update .env:**
```env
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=your_production_host
DB_PORT=3306
DB_DATABASE=production_database
DB_USERNAME=production_user
DB_PASSWORD=strong_password_here

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### Step 2: Optimize Application

```powershell
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### Step 3: Set File Permissions

**Linux/Mac:**
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

**Windows:**
- Right-click `storage` folder
- Properties → Security
- Grant write permissions to web server user

### Step 4: Database Migration

```powershell
# On production server
php artisan migrate --force
```

**⚠️ WARNING:** Never use `migrate:fresh` in production!

### Step 5: SSL Certificate

**Free SSL with Let's Encrypt:**
```bash
certbot --apache -d yourdomain.com
```

Or use your hosting provider's SSL.

### Step 6: Security Headers

**Add to public/.htaccess (Apache):**
```apache
# Security Headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

---

## Performance Optimization

### 1. Enable OPcache

**php.ini:**
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. Use Database Caching

```powershell
# Create sessions table
php artisan session:table
php artisan migrate

# Create cache table
php artisan cache:table
php artisan migrate
```

**Update .env:**
```env
SESSION_DRIVER=database
CACHE_DRIVER=database
```

### 3. Optimize Queries

**Eager loading:**
```php
// ❌ N+1 problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // Query per user!
}

// ✅ Eager loading
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // Single query!
}
```

### 4. Use CDN for Assets

Already using Bootstrap CDN in Phase 1 ✅

### 5. Enable Gzip Compression

**Apache (.htaccess):**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## Final Testing Script

### Complete Test Run

```powershell
# 1. Fresh start
php artisan migrate:fresh
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Start server
php artisan serve

# 3. Manual tests (in browser)
# - Register new user
# - Login
# - Access dashboard
# - Logout
# - Try accessing dashboard (should redirect)
# - Login again

# 4. Check logs
Get-Content storage\logs\laravel.log -Tail 50

# 5. Verify database
php artisan tinker
```

```php
// In Tinker
App\Models\User::count(); // Should show registered users
App\Models\User::latest()->first(); // Latest user
session()->all(); // Current session
exit
```

---

## Success Criteria

### ✅ Phase 1 Complete When:

- [ ] User can register with name, email, password
- [ ] Email must be unique
- [ ] Passwords are hashed (bcrypt)
- [ ] User auto-logged in after registration
- [ ] User can login with email and password
- [ ] Invalid credentials show error messages
- [ ] Dashboard shows user information
- [ ] Dashboard only accessible when logged in
- [ ] User can logout
- [ ] Session destroyed on logout
- [ ] Login/register pages redirect to dashboard if already logged in
- [ ] CSRF protection working on all forms
- [ ] Input validation working correctly
- [ ] Error messages displayed properly
- [ ] Logging working (check storage/logs/laravel.log)
- [ ] No security vulnerabilities (SQL injection, XSS, etc.)

---

## Next Steps

✅ **Phase 1 Complete!**

You now have:
- ✅ Manual authentication system
- ✅ Session-based login
- ✅ Registration and login flows
- ✅ Password hashing (bcrypt)
- ✅ Middleware protection
- ✅ Bootstrap UI
- ✅ CSRF protection
- ✅ Input validation
- ✅ Error handling
- ✅ Logging system

📝 **Ready for Phase 2:**
[PHASE2_BREEZE_BOOTSTRAP.md](PHASE2_BREEZE_BOOTSTRAP.md)

**Phase 2 will add:**
- Laravel Breeze scaffolding
- Bootstrap 5 integration
- Email verification
- Password reset
- Remember me functionality
- Profile management

---

## Quick Reference

### Testing Commands

```powershell
# Clear everything
php artisan optimize:clear

# Fresh database
php artisan migrate:fresh

# Check routes
php artisan route:list

# View logs
Get-Content storage\logs\laravel.log -Tail 50

# Interactive testing
php artisan tinker
```

### Test URLs

```
http://127.0.0.1:8000/
http://127.0.0.1:8000/register
http://127.0.0.1:8000/login
http://127.0.0.1:8000/dashboard
```

### Debugging Tools

```php
dd($variable)           // Dump and die
dump($variable)        // Dump and continue
logger('message')      // Quick log
Log::info('message')   // Structured log
```

---

**Congratulations! Phase 1 Manual Authentication is complete!** 🎉

You've built a complete authentication system from scratch and learned:
- How sessions work
- Password hashing with bcrypt
- Route protection with middleware
- CSRF protection
- Input validation
- Blade templating
- Bootstrap styling
- Security best practices

**Ready to move on to Phase 2 (Laravel Breeze)?**
