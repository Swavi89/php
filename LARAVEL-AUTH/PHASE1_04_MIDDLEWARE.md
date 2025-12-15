# Phase 1: Manual Authentication - Part 4: Middleware & Route Protection

## Table of Contents
1. [Understanding Middleware](#understanding-middleware)
2. [Creating Auth Middleware](#creating-auth-middleware)
3. [Creating Guest Middleware](#creating-guest-middleware)
4. [Registering Middleware](#registering-middleware)
5. [Applying Middleware to Routes](#applying-middleware-to-routes)
6. [Testing Middleware](#testing-middleware)

---

## Understanding Middleware

### What Is Middleware?

**Middleware = Security Guards for Your Routes**

```
Browser Request
      ↓
   Middleware 1 (Check if logged in)
      ↓
   Middleware 2 (Check CSRF token)
      ↓
   Controller
      ↓
   Response
      ↓
   Middleware (Process response)
      ↓
   Browser
```

**Real-World Analogy:**
```
You want to enter a nightclub (protected route)
   ↓
Bouncer checks your ID (middleware)
   ↓
If 18+: Enter (continue to controller)
If <18: Rejected (redirect to home)
```

### Common Middleware Use Cases

| Middleware | Purpose | Example |
|------------|---------|---------|
| **Auth** | Verify user is logged in | Protect dashboard |
| **Guest** | Verify user is NOT logged in | Login/Register pages |
| **CSRF** | Prevent cross-site attacks | All POST requests |
| **Throttle** | Rate limiting | Prevent spam |
| **Admin** | Check if user is admin | Admin panel |
| **Log** | Log all requests | Debugging |

### Middleware Flow Diagram

```
┌─────────────────────────────────────────────┐
│           Browser Request                    │
└───────────────┬─────────────────────────────┘
                │
                ▼
        ┌───────────────┐
        │  Web Routes   │
        └───────┬───────┘
                │
                ▼
        ┌───────────────┐
        │   Middleware  │ ← We create this
        └───────┬───────┘
                │
           ┌────┴────┐
           │         │
    Authenticated?   │
           │         │
       Yes │     No  │
           ▼         ▼
     Controller   Redirect
                  to Login
```

### Before vs After Middleware

```php
// Before Middleware (runs before controller)
public function handle($request, Closure $next)
{
    // Check authentication here
    if (!session('user_id')) {
        return redirect('/login');
    }
    
    return $next($request); // Continue to controller
}

// After Middleware (runs after controller)
public function handle($request, Closure $next)
{
    $response = $next($request); // Get response first
    
    // Modify response here
    $response->header('X-Custom-Header', 'Value');
    
    return $response;
}
```

---

## Creating Auth Middleware

### Step 1: Create Middleware File

```powershell
php artisan make:middleware ManualAuthMiddleware
```

**Expected Output:**
```
   INFO  Middleware [app/Http/Middleware/ManualAuthMiddleware.php] created successfully.
```

**File Created:**
`app/Http/Middleware/ManualAuthMiddleware.php`

### Step 2: Understand Default Middleware Structure

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
```

**Parameters Explained:**

**1. $request:**
- Contains all request data (GET, POST, headers, cookies)
- Access via: `$request->input('name')`, `$request->ip()`

**2. $next:**
- A closure (anonymous function)
- Calls next middleware or controller
- Pass request to next layer: `$next($request)`

**3. Return value:**
- Must return a Response
- Either from `$next($request)` or redirect

### Step 3: Implement Authentication Check

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Check if user is authenticated via session
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user_id exists in session
        if (!session()->has('user_id')) {
            // User is NOT authenticated
            return redirect()->route('manual.login')
                ->withErrors(['error' => 'Please login to access this page.']);
        }

        // User IS authenticated - continue to controller
        return $next($request);
    }
}
```

### Understanding the Logic

**Step-by-Step Breakdown:**

```php
// 1. Check session
if (!session()->has('user_id')) {
```
**What it does:**
- Looks for 'user_id' in session data
- Returns `true` if exists, `false` if not
- Session set during login: `session(['user_id' => $user->id])`

```php
// 2. Redirect if not found
return redirect()->route('manual.login')
    ->withErrors(['error' => 'Please login to access this page.']);
```
**What it does:**
- Redirects to login route
- Adds error message to flash session
- Stops execution (controller never runs)

```php
// 3. Continue if authenticated
return $next($request);
```
**What it does:**
- Passes request to next middleware/controller
- User can access protected route

### Enhanced Version with Logging

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ManualAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Check if user is authenticated via session
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user_id exists in session
        if (!session()->has('user_id')) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect to login
            return redirect()->route('manual.login')
                ->withErrors(['error' => 'Please login to access this page.']);
        }

        // User is authenticated - continue
        return $next($request);
    }
}
```

---

## Creating Guest Middleware

### Why Guest Middleware?

**Problem:**
- Logged-in users shouldn't see login/register pages
- They should go directly to dashboard

**Solution:**
- Guest middleware checks if user is NOT logged in
- If logged in → redirect to dashboard
- If not logged in → show login/register

### Step 1: Create Middleware

```powershell
php artisan make:middleware ManualGuestMiddleware
```

**Expected Output:**
```
   INFO  Middleware [app/Http/Middleware/ManualGuestMiddleware.php] created successfully.
```

### Step 2: Implement Guest Check

**File:** `app/Http/Middleware/ManualGuestMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManualGuestMiddleware
{
    /**
     * Handle an incoming request.
     * Redirect authenticated users to dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user IS authenticated
        if (session()->has('user_id')) {
            // User is already logged in
            return redirect()->route('manual.dashboard');
        }

        // User is NOT logged in - continue to login/register
        return $next($request);
    }
}
```

### Understanding Guest Logic

**Scenario 1: User NOT logged in**
```
1. User visits /login
2. session()->has('user_id') → false
3. Continue to login page ✅
```

**Scenario 2: User logged in**
```
1. Logged-in user visits /login
2. session()->has('user_id') → true
3. Redirect to /dashboard ✅
4. Login page never shown
```

**Why This Matters:**
```
Without Guest Middleware:
- Logged-in user visits /login → Sees login form (confusing!)
- User might try to login again (unnecessary)

With Guest Middleware:
- Logged-in user visits /login → Redirected to dashboard
- Better UX
```

---

## Registering Middleware

### Understanding Middleware Registration

Laravel needs to know about your custom middleware before you can use it.

**Three types of middleware:**

1. **Global Middleware** - Runs on EVERY request
2. **Route Middleware** - Assigned to specific routes
3. **Middleware Groups** - Grouped middleware (e.g., 'web', 'api')

**We'll use Route Middleware** (most common)

### Step 1: Open Bootstrap File

**File:** `bootstrap/app.php`

This file is Laravel 11's new application configuration file.

### Step 2: Register Middleware

**Update:** `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware aliases
        $middleware->alias([
            'manual.auth' => \App\Http\Middleware\ManualAuthMiddleware::class,
            'manual.guest' => \App\Http\Middleware\ManualGuestMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Understanding Registration

```php
$middleware->alias([
    'manual.auth' => \App\Http\Middleware\ManualAuthMiddleware::class,
    'manual.guest' => \App\Http\Middleware\ManualGuestMiddleware::class,
]);
```

**What it does:**
- Creates shorthand aliases
- `'manual.auth'` = nickname for our middleware
- Can now use in routes: `->middleware('manual.auth')`

**Without Alias:**
```php
// Would need to use full class name
Route::get('/dashboard', [...])->middleware(\App\Http\Middleware\ManualAuthMiddleware::class);
```

**With Alias:**
```php
// Much cleaner!
Route::get('/dashboard', [...])->middleware('manual.auth');
```

### Alternative: Old Laravel 10 Method (if bootstrap/app.php different)

If your `bootstrap/app.php` looks different, use this method:

**File:** `app/Http/Kernel.php` (might not exist in Laravel 11)

```php
protected $middlewareAliases = [
    'manual.auth' => \App\Http\Middleware\ManualAuthMiddleware::class,
    'manual.guest' => \App\Http\Middleware\ManualGuestMiddleware::class,
];
```

---

## Applying Middleware to Routes

### Understanding Route Protection

**Routes without middleware:**
```php
Route::get('/dashboard', [...]);
// Anyone can access (even logged out users)
```

**Routes with middleware:**
```php
Route::get('/dashboard', [...])->middleware('manual.auth');
// Only logged-in users can access
```

### Method 1: Individual Route Middleware

**File:** `routes/web.php`

```php
use App\Http\Controllers\Auth\ManualAuthController;

// Guest routes (only for non-authenticated users)
Route::middleware('manual.guest')->group(function () {
    Route::get('/register', [ManualAuthController::class, 'showRegister'])
        ->name('manual.register');
    Route::post('/register', [ManualAuthController::class, 'register']);

    Route::get('/login', [ManualAuthController::class, 'showLogin'])
        ->name('manual.login');
    Route::post('/login', [ManualAuthController::class, 'login']);
});

// Protected routes (only for authenticated users)
Route::middleware('manual.auth')->group(function () {
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])
        ->name('manual.dashboard');
    Route::post('/logout', [ManualAuthController::class, 'logout'])
        ->name('manual.logout');
});
```

### Method 2: Middleware Groups (Cleaner)

```php
use App\Http\Controllers\Auth\ManualAuthController;

// Guest-only routes
Route::middleware('manual.guest')->group(function () {
    Route::get('/register', [ManualAuthController::class, 'showRegister'])->name('manual.register');
    Route::post('/register', [ManualAuthController::class, 'register']);
    Route::get('/login', [ManualAuthController::class, 'showLogin'])->name('manual.login');
    Route::post('/login', [ManualAuthController::class, 'login']);
});

// Authenticated-only routes
Route::middleware('manual.auth')->group(function () {
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])->name('manual.dashboard');
    Route::post('/logout', [ManualAuthController::class, 'logout'])->name('manual.logout');
});
```

### Method 3: Prefix with Middleware

```php
// All routes under /account/* require authentication
Route::prefix('account')->middleware('manual.auth')->group(function () {
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])->name('manual.dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('manual.profile');
    Route::post('/logout', [ManualAuthController::class, 'logout'])->name('manual.logout');
});
```

### Understanding Route Groups

```php
Route::middleware('manual.auth')->group(function () {
    // All routes here have middleware applied
});
```

**Benefits:**
- ✅ DRY (Don't Repeat Yourself)
- ✅ Easier to manage
- ✅ Less chance of forgetting middleware
- ✅ Cleaner code

**Equivalent to:**
```php
Route::get('/dashboard', [...])->middleware('manual.auth');
Route::get('/profile', [...])->middleware('manual.auth');
Route::post('/logout', [...])->middleware('manual.auth');
// Repetitive!
```

### Multiple Middleware

**Apply multiple middleware to route:**
```php
Route::middleware(['manual.auth', 'throttle:60,1'])->group(function () {
    // Requires authentication AND rate limiting
    Route::get('/dashboard', [...]);
});
```

**Middleware order matters:**
```php
// First check auth, then throttle
->middleware(['manual.auth', 'throttle'])

// First throttle, then check auth
->middleware(['throttle', 'manual.auth'])
```

---

## Testing Middleware

### Test 1: Guest Middleware

**Scenario:** Logged-in user tries to access login page

```powershell
php artisan tinker
```

```php
// Simulate logged-in user
session(['user_id' => 1, 'user_name' => 'Test User']);

// Try to access login route
// (This we'll test in browser)
exit
```

**In Browser:**
1. Start server: `php artisan serve`
2. First login as a user (after creating views)
3. Then try to visit: http://127.0.0.1:8000/login
4. **Expected:** Redirected to /dashboard

### Test 2: Auth Middleware

**Scenario:** Guest user tries to access dashboard

**In Browser:**
1. Make sure you're logged out
2. Clear cookies (Ctrl+Shift+Delete)
3. Try to visit: http://127.0.0.1:8000/dashboard
4. **Expected:** Redirected to /login with error message

### Test 3: Check Middleware Registration

```powershell
php artisan route:list
```

**Expected Output:**
```
  GET|HEAD   login ............ manual.login › Auth\ManualAuthController@showLogin
                               manual.guest
  POST       login ............ Auth\ManualAuthController@login
                               manual.guest
  GET|HEAD   register ......... manual.register › Auth\ManualAuthController@showRegister
                               manual.guest
  POST       register ......... Auth\ManualAuthController@register
                               manual.guest
  GET|HEAD   dashboard ........ manual.dashboard › Auth\ManualAuthController@dashboard
                               manual.auth
  POST       logout ........... manual.logout › Auth\ManualAuthController@logout
                               manual.auth
```

**Notice:**
- Login/register have `manual.guest` middleware
- Dashboard/logout have `manual.auth` middleware

### Test 4: Manual Testing Checklist

**Create this test plan:**

| Test Case | Steps | Expected Result |
|-----------|-------|-----------------|
| **1. Guest Access Dashboard** | 1. Logout<br>2. Visit /dashboard | Redirected to /login with error |
| **2. Guest Access Login** | 1. Logout<br>2. Visit /login | Login form shown |
| **3. Authenticated Access Dashboard** | 1. Login<br>2. Visit /dashboard | Dashboard shown |
| **4. Authenticated Access Login** | 1. Login<br>2. Visit /login | Redirected to /dashboard |
| **5. Logout** | 1. Login<br>2. Click logout<br>3. Try /dashboard | Redirected to /login |

---

## Common Middleware Issues

### Issue 1: Middleware Not Working

**Symptoms:**
- Can access protected routes without login
- Middleware seems to be ignored

**Solutions:**

**A. Check Registration**
```powershell
# Verify middleware registered
php artisan route:list --path=dashboard
```

**B. Clear Route Cache**
```powershell
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

**C. Check Middleware Applied**
```php
// In routes/web.php
Route::get('/dashboard', [...])->middleware('manual.auth');
//                                ^^^^^^^^^^^^^^^^^^^^^^^^
//                                Must be present!
```

### Issue 2: Redirect Loop

**Symptoms:**
- Browser says "Too many redirects"
- Page keeps redirecting

**Causes:**

**A. Middleware on Wrong Routes**
```php
// ❌ WRONG - Auth middleware on login
Route::get('/login', [...])->middleware('manual.auth');
// Creates loop: login → redirect to login → redirect...

// ✅ CORRECT - Guest middleware on login
Route::get('/login', [...])->middleware('manual.guest');
```

**B. Dashboard Route Has Guest Middleware**
```php
// ❌ WRONG
Route::get('/dashboard', [...])->middleware('manual.guest');

// ✅ CORRECT
Route::get('/dashboard', [...])->middleware('manual.auth');
```

### Issue 3: Session Not Found

**Error:**
```
Session store not set on request
```

**Solution:**

**A. Check Session Config**
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

**B. Create Session Table (if using database)**
```powershell
php artisan session:table
php artisan migrate
```

**C. Check Storage Permissions**
```powershell
icacls storage\framework\sessions /grant Everyone:F
```

### Issue 4: Middleware Class Not Found

**Error:**
```
Class "App\Http\Middleware\ManualAuthMiddleware" not found
```

**Solutions:**

**A. Check Namespace**
```php
// In middleware file
namespace App\Http\Middleware;  // Must match file location
```

**B. Check Class Name**
```php
class ManualAuthMiddleware  // Must match filename
```

**C. Run Composer Autoload**
```powershell
composer dump-autoload
```

---

## Next Steps

✅ **Completed:**
- Middleware concept
- Auth middleware created
- Guest middleware created
- Middleware registered
- Routes protected
- Testing strategies

📝 **Next Document:**
[PHASE1_05_GUARDS_POLICIES.md](PHASE1_05_GUARDS_POLICIES.md)

**You will learn:**
- What are Guards
- What are Policies
- Authorization vs Authentication
- Custom guards
- Policy creation
- Gate definitions

---

## Quick Reference

### Middleware Commands

```powershell
php artisan make:middleware MiddlewareName    # Create middleware
php artisan route:list                        # See all routes + middleware
php artisan route:clear                       # Clear route cache
```

### Middleware Registration

```php
// bootstrap/app.php
$middleware->alias([
    'alias' => \App\Http\Middleware\ClassName::class,
]);
```

### Applying Middleware

```php
// Single route
Route::get('/path', [...])->middleware('alias');

// Multiple routes (group)
Route::middleware('alias')->group(function () {
    Route::get('/path1', [...]);
    Route::get('/path2', [...]);
});

// Multiple middleware
Route::middleware(['alias1', 'alias2'])->group(function () {
    // ...
});
```

### Common Middleware Patterns

```php
// Check authentication
if (!session()->has('user_id')) {
    return redirect()->route('login');
}

// Check if guest
if (session()->has('user_id')) {
    return redirect()->route('dashboard');
}

// Continue to next
return $next($request);
```

---

**Middleware Complete!** Proceed to Part 5 for Guards & Policies.
