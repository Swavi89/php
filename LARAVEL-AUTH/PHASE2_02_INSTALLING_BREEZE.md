# Phase 2: Laravel Breeze with Bootstrap - Part 2: Installing Breeze

## Table of Contents
1. [Preparing Your Laravel Project](#preparing-your-laravel-project)
2. [Installing Breeze Package](#installing-breeze-package)
3. [Scaffolding Breeze](#scaffolding-breeze)
4. [Understanding Generated Files](#understanding-generated-files)
5. [Running Migrations](#running-migrations)
6. [Testing Breeze Installation](#testing-breeze-installation)
7. [Troubleshooting](#troubleshooting)

---

## Preparing Your Laravel Project

### Option A: Fresh Laravel Installation (Recommended)

**Step 1: Create new Laravel project**

```powershell
# Navigate to your projects directory
cd D:\

# Create new Laravel project
composer create-project laravel/laravel breeze-app

# Navigate into project
cd breeze-app
```

**Expected output:**
```
Creating a "laravel/laravel" project at "./breeze-app"
Installing laravel/laravel (v11.x.x)
  - Downloading laravel/laravel (v11.x.x)
  - Installing laravel/laravel (v11.x.x): Extracting archive
Created project in D:\breeze-app
> @php artisan key:generate --ansi
Application key set successfully.
```

**Step 2: Configure database**

**File:** `.env`

```env
APP_NAME="Breeze Auth App"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=breeze_auth_demo
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Step 3: Create database**

```powershell
# Connect to MySQL
mysql -u root -p

# In MySQL console:
CREATE DATABASE breeze_auth_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Step 4: Test database connection**

```powershell
php artisan migrate
```

**Expected output:**
```
   INFO  Preparing database.

  Creating migration table .............................................. 32ms DONE

   INFO  Running migrations.

  2014_10_12_000000_create_users_table .................................. 45ms DONE
  2014_10_12_100000_create_password_reset_tokens_table .................. 28ms DONE
  2019_08_19_000000_create_failed_jobs_table ............................ 35ms DONE
  2019_12_14_000001_create_personal_access_tokens_table ................. 42ms DONE
```

### Option B: Using Existing Project (auth-app)

⚠️ **Warning:** Breeze will overwrite some files!

**Step 1: Backup existing work**

```powershell
cd D:\auth-app

# Create backup branch
git add .
git commit -m "Phase 1 complete - before Breeze installation"
git branch phase1-backup

# Or create full backup
cd ..
Copy-Item -Path "auth-app" -Destination "auth-app-backup" -Recurse
cd auth-app
```

**Step 2: Clean existing auth files (optional)**

If you want fresh Breeze installation:

```powershell
# Remove Phase 1 auth files
Remove-Item app\Http\Controllers\ManualAuthController.php -ErrorAction SilentlyContinue
Remove-Item app\Http\Middleware\ManualAuthMiddleware.php -ErrorAction SilentlyContinue
Remove-Item app\Http\Middleware\ManualGuestMiddleware.php -ErrorAction SilentlyContinue
```

**Step 3: Reset database**

```powershell
php artisan migrate:fresh
```

---

## Installing Breeze Package

### Step 1: Install via Composer

```powershell
composer require laravel/breeze --dev
```

**Explanation:**
- `laravel/breeze` - Package name
- `--dev` - Development dependency (not needed in production)

**Expected output:**
```
Using version ^2.x for laravel/breeze
./composer.json has been updated
Running composer update laravel/breeze
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking laravel/breeze (v2.3.8)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
  - Downloading laravel/breeze (v2.3.8)
  - Installing laravel/breeze (v2.3.8): Extracting archive
Generating optimized autoload files
```

**Verification:**

```powershell
# Check Breeze is installed
composer show laravel/breeze
```

**Output:**
```
name     : laravel/breeze
descrip. : Minimal Laravel authentication scaffolding.
keywords : auth, authentication, laravel
versions : * v2.3.8
type     : library
```

### Step 2: Verify Installation

```powershell
# List available artisan commands
php artisan list | Select-String "breeze"
```

**Expected output:**
```
  breeze:install             Install the Breeze controllers and resources
```

---

## Scaffolding Breeze

### Understanding Breeze Stacks

Breeze offers multiple frontend stacks:

```
Available Stacks:
├─ blade              → Blade + Alpine.js + Tailwind CSS (default)
├─ livewire           → Livewire + Alpine.js + Tailwind CSS
├─ react              → React + Inertia.js + Tailwind CSS
├─ vue                → Vue + Inertia.js + Tailwind CSS
└─ api                → API-only (no views)
```

**We'll use:** `blade` (simplest, uses Blade templates)

### Step 1: Run Breeze Installation

```powershell
php artisan breeze:install blade
```

**Interactive prompts:**

```
┌ Which Breeze stack would you like to install? ──────────────┐
│   Blade with Alpine                                          │
│ › Livewire (Volt Class API) with Alpine                     │
│   Livewire (Volt Functional API) with Alpine                │
│   React with Inertia                                         │
│   Vue with Inertia                                           │
│   API only                                                   │
└──────────────────────────────────────────────────────────────┘
```

**Select:** `Blade with Alpine` (first option, press Enter)

```
┌ Would you like dark mode support? ───────────────────────────┐
│ › No                                                          │
│   Yes                                                         │
└──────────────────────────────────────────────────────────────┘
```

**Select:** `No` (we'll use Bootstrap styling)

```
┌ Which testing framework do you prefer? ──────────────────────┐
│ › Pest                                                        │
│   PHPUnit                                                     │
│   None                                                        │
└──────────────────────────────────────────────────────────────┘
```

**Select:** `Pest` or `PHPUnit` (your preference)

**Installation output:**
```
   INFO  Breeze scaffolding installed successfully.

   INFO  Please execute the "npm install" and "npm run dev" commands to build your assets.
```

### Step 2: Install Node Dependencies

```powershell
npm install
```

**Expected output:**
```
npm WARN deprecated @humanwhocodes/config-array@0.11.14
npm WARN deprecated @humanwhocodes/object-schema@2.0.3

added 125 packages, and audited 126 packages in 15s

23 packages are looking for funding
  run `npm fund` for details

found 0 vulnerabilities
```

**What was installed:**
- Vite (build tool)
- Tailwind CSS (we'll replace with Bootstrap)
- Alpine.js (JavaScript framework)
- PostCSS (CSS processor)
- Autoprefixer (CSS vendor prefixes)

### Step 3: Build Assets

**Development mode:**
```powershell
npm run dev
```

**Expected output:**
```
> dev
> vite

  VITE v5.4.21  ready in 234 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help

  LARAVEL v11.47.0  plugin v1.2.0

  ➜  APP_URL: http://localhost
```

**Leave this running!** Open new terminal for other commands.

---

## Understanding Generated Files

### Controllers Created

**Authentication Controllers:**

**File:** `app/Http/Controllers/Auth/RegisteredUserController.php`
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
```

**Key features:**
- ✅ Form Request validation
- ✅ Password hashing
- ✅ Fires Registered event (for email verification)
- ✅ Auto-login after registration
- ✅ Redirects to dashboard

**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
```php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    
    $request->session()->regenerate();
    
    return redirect()->intended(route('dashboard', absolute: false));
}
```

**Key features:**
- ✅ Uses dedicated LoginRequest
- ✅ Session regeneration (security)
- ✅ Intended redirect (remembers where user wanted to go)
- ✅ Rate limiting

### Form Requests Created

**File:** `app/Http/Requests/Auth/LoginRequest.php`

```php
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();
    
    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());
        
        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }
    
    RateLimiter::clear($this->throttleKey());
}

public function ensureIsNotRateLimited(): void
{
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
        return;
    }
    
    event(new Lockout($this));
    
    $seconds = RateLimiter::availableIn($this->throttleKey());
    
    throw ValidationException::withMessages([
        'email' => trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]),
    ]);
}
```

**Key features:**
- ✅ Rate limiting (5 attempts per minute)
- ✅ Lockout event
- ✅ Remember me functionality
- ✅ Clear rate limit on success

### Views Created

**Directory:** `resources/views/`

```
views/
├── auth/
│   ├── confirm-password.blade.php         # Password confirmation
│   ├── forgot-password.blade.php          # Request password reset
│   ├── login.blade.php                    # Login form
│   ├── register.blade.php                 # Registration form
│   ├── reset-password.blade.php           # Reset password form
│   └── verify-email.blade.php             # Email verification notice
├── layouts/
│   ├── app.blade.php                      # Authenticated layout
│   ├── guest.blade.php                    # Guest layout
│   └── navigation.blade.php               # Navigation menu
├── profile/
│   ├── edit.blade.php                     # Profile edit page
│   └── partials/
│       ├── delete-user-form.blade.php     # Delete account
│       ├── update-password-form.blade.php # Change password
│       └── update-profile-information-form.blade.php  # Update profile
├── dashboard.blade.php                    # Dashboard
└── welcome.blade.php                      # Home page (updated)
```

### Routes Created

**File:** `routes/auth.php` (new file)

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

**File:** `routes/web.php` (updated)

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
```

---

## Running Migrations

### Step 1: Check Migration Files

```powershell
Get-ChildItem database\migrations
```

**Expected files:**
```
2014_10_12_000000_create_users_table.php
2014_10_12_100000_create_password_reset_tokens_table.php
2019_08_19_000000_create_failed_jobs_table.php
2019_12_14_000001_create_personal_access_tokens_table.php
2024_01_01_000000_create_cache_table.php
2024_01_01_000001_create_jobs_table.php
```

### Step 2: Run Migrations

```powershell
php artisan migrate
```

**Expected output:**
```
   INFO  Preparing database.

  Creating migration table ........................................... 28ms DONE

   INFO  Running migrations.

  2014_10_12_000000_create_users_table ............................... 42ms DONE
  2014_10_12_100000_create_password_reset_tokens_table ............... 25ms DONE
  2019_08_19_000000_create_failed_jobs_table ......................... 32ms DONE
  2019_12_14_000001_create_personal_access_tokens_table .............. 38ms DONE
  2024_01_01_000000_create_cache_table ............................... 30ms DONE
  2024_01_01_000001_create_jobs_table ................................ 35ms DONE
```

### Step 3: Verify Tables Created

```powershell
php artisan tinker
```

```php
// List all tables
DB::select('SHOW TABLES');

// Check users table structure
Schema::getColumnListing('users');
// Returns: ["id", "name", "email", "email_verified_at", "password", "remember_token", "created_at", "updated_at"]

exit
```

---

## Testing Breeze Installation

### Step 1: Start Development Server

**Terminal 1 (keep running):**
```powershell
npm run dev
```

**Terminal 2:**
```powershell
php artisan serve
```

**Expected output:**
```
   INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

### Step 2: Test Registration

1. **Visit:** http://127.0.0.1:8000/register

2. **Check page loads:**
   - Registration form visible
   - Tailwind CSS styling (for now)
   - Name, Email, Password fields

3. **Fill form:**
   - Name: Test User
   - Email: test@example.com
   - Password: password123
   - Confirm Password: password123

4. **Submit form**

5. **Expected result:**
   - Redirected to dashboard
   - Logged in automatically
   - Welcome message displayed

### Step 3: Test Logout

1. **Click navigation dropdown**

2. **Click "Log Out"**

3. **Expected result:**
   - Redirected to home page
   - No longer authenticated

### Step 4: Test Login

1. **Visit:** http://127.0.0.1:8000/login

2. **Fill form:**
   - Email: test@example.com
   - Password: password123
   - Check "Remember Me" (optional)

3. **Submit form**

4. **Expected result:**
   - Redirected to dashboard
   - Logged in successfully

### Step 5: Test Database

```powershell
php artisan tinker
```

```php
// Check user created
$user = App\Models\User::first();
$user->name;        // "Test User"
$user->email;       // "test@example.com"
$user->created_at;  // Timestamp

// Check password is hashed
$user->password;    // $2y$12$... (bcrypt hash)

// Test authentication
Auth::attempt(['email' => 'test@example.com', 'password' => 'password123']);
// Returns: true

exit
```

### Step 6: Test Routes

```powershell
# List all routes
php artisan route:list | Select-String "auth|register|login|dashboard"
```

**Expected routes:**
```
GET|HEAD   register ..................... register › Auth\RegisteredUserController@create
POST       register ................................ Auth\RegisteredUserController@store
GET|HEAD   login .......................... login › Auth\AuthenticatedSessionController@create
POST       login .................................. Auth\AuthenticatedSessionController@store
POST       logout .......................... logout › Auth\AuthenticatedSessionController@destroy
GET|HEAD   dashboard ...................... dashboard
GET|HEAD   forgot-password ... password.request › Auth\PasswordResetLinkController@create
POST       forgot-password ........ password.email › Auth\PasswordResetLinkController@store
```

---

## Troubleshooting

### Issue 1: "Class 'Vite' not found"

**Error:**
```
Error: Class "Vite" not found
```

**Solution:**

```powershell
# Clear config cache
php artisan config:clear

# Install dependencies
npm install

# Run dev server
npm run dev
```

### Issue 2: Assets Not Loading

**Error:**
```
Failed to load resource: the server responded with a status of 404
http://localhost:5173/@vite/client
```

**Solution:**

```powershell
# Ensure Vite is running
npm run dev

# Check .env
# APP_URL should match server URL
APP_URL=http://127.0.0.1:8000

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Issue 3: Database Connection Error

**Error:**
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```

**Solution:**

**Check .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=breeze_auth_demo
DB_USERNAME=root
DB_PASSWORD=your_actual_password
```

**Test connection:**
```powershell
mysql -u root -p
# Enter password
SHOW DATABASES;
EXIT;
```

### Issue 4: Migration Error

**Error:**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solution:**

```powershell
# Rollback migrations
php artisan migrate:rollback

# Or fresh migration
php artisan migrate:fresh

# Or drop database and recreate
mysql -u root -p
DROP DATABASE breeze_auth_demo;
CREATE DATABASE breeze_auth_demo;
EXIT;

php artisan migrate
```

### Issue 5: Route Not Found

**Error:**
```
Target class [Auth\RegisteredUserController] does not exist.
```

**Solution:**

**Check routes/web.php includes auth.php:**
```php
require __DIR__.'/auth.php';
```

**Clear route cache:**
```powershell
php artisan route:clear
php artisan optimize:clear
```

### Issue 6: npm install Fails

**Error:**
```
npm ERR! code ENOENT
npm ERR! syscall open
npm ERR! path D:\breeze-app\package.json
```

**Solution:**

```powershell
# Ensure you're in project root
cd D:\breeze-app
Get-Location

# Check package.json exists
Test-Path package.json

# If missing, reinstall Breeze
php artisan breeze:install blade
```

---

## Next Steps

✅ **Completed:**
- Fresh Laravel project created
- Breeze package installed
- Breeze scaffolding complete
- Migrations run successfully
- Basic testing passed

📝 **Next Document:**
[PHASE2_03_BOOTSTRAP_INTEGRATION.md](PHASE2_03_BOOTSTRAP_INTEGRATION.md)

**You will learn:**
- Removing Tailwind CSS
- Installing Bootstrap 5
- Configuring Vite for Bootstrap
- Testing Bootstrap installation
- Understanding asset pipeline

---

## Quick Reference

### Installation Commands

```powershell
# Install Breeze
composer require laravel/breeze --dev

# Scaffold Blade stack
php artisan breeze:install blade

# Install dependencies
npm install

# Run migrations
php artisan migrate

# Development servers
npm run dev          # Terminal 1
php artisan serve    # Terminal 2
```

### Key Files Created

```
Controllers:  app/Http/Controllers/Auth/
Routes:       routes/auth.php
Views:        resources/views/auth/
Layouts:      resources/views/layouts/
Profile:      app/Http/Controllers/ProfileController.php
Requests:     app/Http/Requests/Auth/LoginRequest.php
```

### Testing URLs

```
Home:         http://127.0.0.1:8000
Register:     http://127.0.0.1:8000/register
Login:        http://127.0.0.1:8000/login
Dashboard:    http://127.0.0.1:8000/dashboard
Profile:      http://127.0.0.1:8000/profile
```

---

**Breeze installation complete!** Ready to integrate Bootstrap 5.
