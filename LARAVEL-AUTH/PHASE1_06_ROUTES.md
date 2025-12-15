# Phase 1: Manual Authentication - Part 6: Routes Definition

## Table of Contents
1. [Understanding Routes](#understanding-routes)
2. [Route Basics](#route-basics)
3. [Route Methods](#route-methods)
4. [Route Naming](#route-naming)
5. [Route Groups](#route-groups)
6. [Complete Routes File](#complete-routes-file)
7. [Testing Routes](#testing-routes)

---

## Understanding Routes

### What Are Routes?

**Routes = URL Mapping to Controllers**

```
User visits URL: http://127.0.0.1:8000/login
         ↓
Route matches: Route::get('/login', ...)
         ↓
Calls controller: ManualAuthController@showLogin
         ↓
Returns view: auth.manual.login
         ↓
Browser shows: Login form
```

**Route Components:**
```php
Route::METHOD('/URI', [Controller::class, 'method'])->name('route.name');
  │      │       │            │                │              │
  │      │       │            │                │              └─ Route name (for links)
  │      │       │            │                └─ Controller method
  │      │       │            └─ Controller class
  │      │       └─ URI path (what user types)
  │      └─ HTTP method (GET, POST, etc.)
  └─ Route facade
```

### Route Flow Diagram

```
┌──────────────────────────────────────────┐
│  Browser: GET http://site.com/login     │
└────────────────┬─────────────────────────┘
                 │
                 ▼
         ┌───────────────┐
         │  routes/web.php │
         └───────┬─────────┘
                 │
                 ▼
    Route::get('/login', [...])
                 │
                 ▼
    ┌────────────────────────┐
    │ Middleware Check       │
    │ (manual.guest)         │
    └────────┬───────────────┘
             │
        Authorized?
             │
        ┌────┴────┐
        │         │
       Yes       No
        │         │
        ▼         ▼
    Controller  Redirect
        │
        ▼
    Return View
```

---

## Route Basics

### HTTP Methods

**Common HTTP methods:**

| Method | Purpose | Example Use |
|--------|---------|-------------|
| **GET** | Retrieve data | Show login form, display page |
| **POST** | Submit data | Process login, create user |
| **PUT/PATCH** | Update data | Update profile |
| **DELETE** | Delete data | Delete account |

**In Laravel:**
```php
Route::get('/path', [...]);      // GET request
Route::post('/path', [...]);     // POST request
Route::put('/path', [...]);      // PUT request
Route::patch('/path', [...]);    // PATCH request
Route::delete('/path', [...]);   // DELETE request
```

### Basic Route Syntax

**Simple closure route:**
```php
Route::get('/', function () {
    return view('welcome');
});
```

**Controller route:**
```php
use App\Http\Controllers\Auth\ManualAuthController;

Route::get('/login', [ManualAuthController::class, 'showLogin']);
```

**With route name:**
```php
Route::get('/login', [ManualAuthController::class, 'showLogin'])
    ->name('manual.login');
```

### Route Parameters

**Basic parameter:**
```php
Route::get('/user/{id}', function ($id) {
    return "User ID: " . $id;
});

// URL: /user/123
// Output: User ID: 123
```

**Optional parameter:**
```php
Route::get('/posts/{id?}', function ($id = null) {
    return $id ? "Post $id" : "All posts";
});

// URL: /posts → All posts
// URL: /posts/5 → Post 5
```

**Multiple parameters:**
```php
Route::get('/post/{id}/comment/{commentId}', function ($id, $commentId) {
    return "Post $id, Comment $commentId";
});

// URL: /post/10/comment/25
```

**Parameter constraints:**
```php
// Only numbers
Route::get('/user/{id}', [...])->where('id', '[0-9]+');

// Only letters
Route::get('/username/{name}', [...])->where('name', '[a-zA-Z]+');

// Multiple constraints
Route::get('/post/{id}/{slug}', [...])
    ->where(['id' => '[0-9]+', 'slug' => '[a-z-]+']);
```

---

## Route Methods

### Match Multiple Methods

```php
// GET and POST
Route::match(['get', 'post'], '/form', [...]);

// All methods
Route::any('/any-method', [...]);
```

### Redirect Routes

```php
// Simple redirect
Route::redirect('/old-path', '/new-path');

// With status code
Route::redirect('/old-path', '/new-path', 301);

// Permanent redirect (301)
Route::permanentRedirect('/old-path', '/new-path');
```

### View Routes (No Controller)

```php
// Directly return a view
Route::view('/about', 'pages.about');

// With data
Route::view('/about', 'pages.about', ['title' => 'About Us']);
```

---

## Route Naming

### Why Name Routes?

**Without names:**
```blade
<!-- Hard-coded URLs - BAD -->
<a href="/auth/manual/login">Login</a>
<form action="/auth/manual/register" method="POST">
```

**Problems:**
- ❌ If you change URL, update everywhere
- ❌ Easy to make typos
- ❌ Hard to maintain

**With names:**
```blade
<!-- Named routes - GOOD -->
<a href="{{ route('manual.login') }}">Login</a>
<form action="{{ route('manual.register') }}" method="POST">
```

**Benefits:**
- ✅ Change URL in one place
- ✅ Laravel generates correct URL
- ✅ Type-safe (Laravel checks if route exists)
- ✅ Easier to maintain

### Naming Convention

**Best practices:**

```php
// Format: resource.action
Route::get('/login', [...])->name('manual.login');        // Show login
Route::post('/login', [...])->name('manual.login.store'); // Process login
Route::get('/register', [...])->name('manual.register');  // Show register

// Or simpler (same name for GET and POST):
Route::get('/login', [...])->name('manual.login');
Route::post('/login', [...])->name('manual.login'); // Same name OK for different methods
```

**Common patterns:**

| Action | Route Name |
|--------|------------|
| Show list | `posts.index` |
| Show single | `posts.show` |
| Show create form | `posts.create` |
| Store new | `posts.store` |
| Show edit form | `posts.edit` |
| Update | `posts.update` |
| Delete | `posts.destroy` |

### Using Named Routes

**In Blade templates:**
```blade
<!-- Simple link -->
<a href="{{ route('manual.login') }}">Login</a>

<!-- With parameters -->
<a href="{{ route('profile.show', $user->id) }}">View Profile</a>
<a href="{{ route('profile.show', ['id' => 5]) }}">User 5</a>

<!-- With query string -->
<a href="{{ route('posts.index', ['sort' => 'date']) }}">Posts</a>
<!-- Generates: /posts?sort=date -->
```

**In controllers:**
```php
// Redirect to named route
return redirect()->route('manual.dashboard');

// With parameters
return redirect()->route('profile.show', $user->id);

// With flash message
return redirect()->route('manual.login')
    ->with('success', 'Logged out successfully');
```

---

## Route Groups

### Why Use Groups?

**Without groups (repetitive):**
```php
Route::get('/admin/users', [...])->middleware('admin');
Route::get('/admin/settings', [...])->middleware('admin');
Route::get('/admin/reports', [...])->middleware('admin');
// Same middleware repeated!
```

**With groups (DRY):**
```php
Route::middleware('admin')->group(function () {
    Route::get('/admin/users', [...]);
    Route::get('/admin/settings', [...]);
    Route::get('/admin/reports', [...]);
});
// Middleware applied once!
```

### Middleware Groups

```php
Route::middleware('manual.auth')->group(function () {
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])
        ->name('manual.dashboard');
    Route::post('/logout', [ManualAuthController::class, 'logout'])
        ->name('manual.logout');
});
```

### Prefix Groups

```php
// All routes get /admin prefix
Route::prefix('admin')->group(function () {
    Route::get('/users', [...]);      // /admin/users
    Route::get('/settings', [...]);   // /admin/settings
    Route::get('/reports', [...]);    // /admin/reports
});
```

### Name Prefix Groups

```php
// All route names get 'admin.' prefix
Route::name('admin.')->group(function () {
    Route::get('/users', [...])->name('users');       // admin.users
    Route::get('/settings', [...])->name('settings'); // admin.settings
});
```

### Combined Groups

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['manual.auth', 'admin'])
    ->group(function () {
        Route::get('/users', [AdminController::class, 'users'])
            ->name('users'); // Route name: admin.users, URL: /admin/users
        Route::get('/settings', [AdminController::class, 'settings'])
            ->name('settings'); // Route name: admin.settings, URL: /admin/settings
    });
```

### Nested Groups

```php
Route::middleware('manual.auth')->group(function () {
    // Regular user routes
    Route::get('/dashboard', [...]);
    
    // Admin-only routes (nested group)
    Route::middleware('admin')->group(function () {
        Route::get('/admin/panel', [...]);
    });
});
```

---

## Complete Routes File

### Full routes/web.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ManualAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============================================================================
// GUEST ROUTES (Only for non-authenticated users)
// ============================================================================

Route::middleware('manual.guest')->group(function () {
    
    // Registration routes
    Route::get('/register', [ManualAuthController::class, 'showRegister'])
        ->name('manual.register');
    Route::post('/register', [ManualAuthController::class, 'register']);

    // Login routes
    Route::get('/login', [ManualAuthController::class, 'showLogin'])
        ->name('manual.login');
    Route::post('/login', [ManualAuthController::class, 'login']);
});

// ============================================================================
// AUTHENTICATED ROUTES (Only for logged-in users)
// ============================================================================

Route::middleware('manual.auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])
        ->name('manual.dashboard');

    // Logout
    Route::post('/logout', [ManualAuthController::class, 'logout'])
        ->name('manual.logout');
});
```

### With Comments and Organization

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ManualAuthController;

/**
 * ============================================================================
 * PHASE 1: MANUAL AUTHENTICATION ROUTES
 * ============================================================================
 * 
 * This file contains all routes for manual session-based authentication.
 * 
 * Route Groups:
 * - Public: Accessible to everyone
 * - Guest: Only for non-authenticated users (login, register)
 * - Auth: Only for authenticated users (dashboard, logout)
 * 
 * Middleware:
 * - manual.guest: Redirects authenticated users to dashboard
 * - manual.auth: Redirects guests to login
 */

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

/**
 * Home/Welcome Page
 * Accessible to everyone (guests and authenticated users)
 */
Route::get('/', function () {
    return view('welcome');
})->name('home');

// ============================================================================
// GUEST ROUTES (Guest Middleware)
// ============================================================================

Route::middleware('manual.guest')->group(function () {
    
    /**
     * Registration Routes
     * GET  /register - Show registration form
     * POST /register - Process registration
     */
    Route::get('/register', [ManualAuthController::class, 'showRegister'])
        ->name('manual.register');
    Route::post('/register', [ManualAuthController::class, 'register']);

    /**
     * Login Routes
     * GET  /login - Show login form
     * POST /login - Process login
     */
    Route::get('/login', [ManualAuthController::class, 'showLogin'])
        ->name('manual.login');
    Route::post('/login', [ManualAuthController::class, 'login']);
});

// ============================================================================
// AUTHENTICATED ROUTES (Auth Middleware)
// ============================================================================

Route::middleware('manual.auth')->group(function () {
    
    /**
     * Dashboard
     * Main page after successful login
     */
    Route::get('/dashboard', [ManualAuthController::class, 'dashboard'])
        ->name('manual.dashboard');

    /**
     * Logout
     * POST only (prevents CSRF attacks)
     */
    Route::post('/logout', [ManualAuthController::class, 'logout'])
        ->name('manual.logout');
});
```

### Alternative: Resource-Style Organization

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ManualAuthController;

// Home
Route::view('/', 'welcome')->name('home');

// Authentication Routes
Route::controller(ManualAuthController::class)->group(function () {
    
    // Guest-only routes
    Route::middleware('manual.guest')->group(function () {
        Route::get('/register', 'showRegister')->name('manual.register');
        Route::post('/register', 'register');
        Route::get('/login', 'showLogin')->name('manual.login');
        Route::post('/login', 'login');
    });

    // Auth-only routes
    Route::middleware('manual.auth')->group(function () {
        Route::get('/dashboard', 'dashboard')->name('manual.dashboard');
        Route::post('/logout', 'logout')->name('manual.logout');
    });
});
```

---

## Testing Routes

### Step 1: List All Routes

```powershell
php artisan route:list
```

**Expected Output:**
```
  GET|HEAD   / .................... home › Closure
  GET|HEAD   register ............ manual.register › Auth\ManualAuthController@showRegister
                                  manual.guest
  POST       register ............ Auth\ManualAuthController@register
                                  manual.guest
  GET|HEAD   login ............... manual.login › Auth\ManualAuthController@showLogin
                                  manual.guest
  POST       login ............... Auth\ManualAuthController@login
                                  manual.guest
  GET|HEAD   dashboard ........... manual.dashboard › Auth\ManualAuthController@dashboard
                                  manual.auth
  POST       logout .............. manual.logout › Auth\ManualAuthController@logout
                                  manual.auth
```

### Step 2: Filter Routes

```powershell
# Show only manual auth routes
php artisan route:list --path=login

# Show only GET routes
php artisan route:list --method=GET

# Show only routes with name
php artisan route:list --name=manual

# Show specific route
php artisan route:list --path=dashboard
```

### Step 3: Test Route Generation

```powershell
php artisan tinker
```

```php
// Test named routes
route('manual.login');
// Returns: "http://localhost/login"

route('manual.dashboard');
// Returns: "http://localhost/dashboard"

// Test with parameters (if you have them)
route('profile.show', 5);
// Returns: "http://localhost/profile/5"

exit
```

### Step 4: Test in Browser (After Views Created)

```powershell
php artisan serve
```

**Test each route:**

| URL | Expected Result |
|-----|-----------------|
| http://127.0.0.1:8000/ | Welcome page |
| http://127.0.0.1:8000/login | Login form (if guest) |
| http://127.0.0.1:8000/register | Register form (if guest) |
| http://127.0.0.1:8000/dashboard | Dashboard (if authenticated) |
| http://127.0.0.1:8000/logout | 405 Method Not Allowed (POST only) |

---

## Common Route Issues

### Issue 1: Route Not Found (404)

**Error:**
```
404 | NOT FOUND
```

**Solutions:**

**A. Check route exists:**
```powershell
php artisan route:list
```

**B. Clear route cache:**
```powershell
php artisan route:clear
```

**C. Check spelling:**
```php
// Wrong
Route::get('/loginn', [...]);

// Correct
Route::get('/login', [...]);
```

### Issue 2: Method Not Allowed (405)

**Error:**
```
405 | METHOD NOT ALLOWED
```

**Cause:**
Using wrong HTTP method

**Example:**
```php
// Route expects POST
Route::post('/logout', [...]);

// But you're using GET
<a href="/logout">Logout</a> ❌

// Fix: Use POST
<form method="POST" action="/logout">
    @csrf
    <button>Logout</button>
</form> ✅
```

### Issue 3: Named Route Not Found

**Error:**
```
Route [manual.login] not defined
```

**Solutions:**

**A. Check route name:**
```powershell
php artisan route:list --name=manual
```

**B. Add name to route:**
```php
Route::get('/login', [...])->name('manual.login');
//                           ^^^^^^^^^^^^^^^^^^^^ Add this
```

**C. Clear cache:**
```powershell
php artisan route:clear
php artisan config:clear
```

### Issue 4: Controller Not Found

**Error:**
```
Target class [ManualAuthController] does not exist
```

**Solutions:**

**A. Import controller:**
```php
use App\Http\Controllers\Auth\ManualAuthController;
```

**B. Use full namespace:**
```php
Route::get('/login', [\App\Http\Controllers\Auth\ManualAuthController::class, 'showLogin']);
```

**C. Verify controller exists:**
```powershell
ls app\Http\Controllers\Auth\ManualAuthController.php
```

### Issue 5: Middleware Not Applied

**Symptom:**
Can access protected routes without login

**Solutions:**

**A. Check middleware registered:**
```powershell
php artisan route:list --path=dashboard
# Should show: manual.auth
```

**B. Verify middleware applied:**
```php
Route::middleware('manual.auth')->group(function () {
//   ^^^^^^^^^^^^^^^^^^^^^^^^^^^ Must be here
    Route::get('/dashboard', [...]);
});
```

**C. Clear caches:**
```powershell
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

---

## Next Steps

✅ **Completed:**
- Route basics
- HTTP methods
- Route naming
- Route groups
- Complete route file
- Route testing

📝 **Next Document:**
[PHASE1_07_VIEWS.md](PHASE1_07_VIEWS.md)

**You will learn:**
- Blade templating
- Bootstrap integration
- Layout creation
- Form design
- CSRF protection
- Error display
- Complete view files

---

## Quick Reference

### Route Definition

```php
// Basic
Route::get('/path', [Controller::class, 'method']);

// With name
Route::get('/path', [...])->name('route.name');

// With middleware
Route::get('/path', [...])->middleware('auth');

// Group
Route::middleware('auth')->group(function () {
    Route::get('/path1', [...]);
    Route::get('/path2', [...]);
});
```

### Route Usage

```php
// In Blade
{{ route('route.name') }}
{{ route('route.name', $id) }}

// In Controller
return redirect()->route('route.name');
return redirect()->route('route.name', $id);
```

### Testing Commands

```powershell
php artisan route:list                    # All routes
php artisan route:list --path=login       # Filter by path
php artisan route:list --name=manual      # Filter by name
php artisan route:clear                   # Clear cache
```

---

**Routes Complete!** Proceed to Part 7 for Bootstrap view creation.
