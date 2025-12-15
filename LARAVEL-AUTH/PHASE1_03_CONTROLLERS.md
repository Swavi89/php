# Phase 1: Manual Authentication - Part 3: Controllers & Authentication Logic

## Table of Contents
1. [Understanding Controllers](#understanding-controllers)
2. [Creating Auth Controller](#creating-auth-controller)
3. [Registration Logic](#registration-logic)
4. [Login Logic](#login-logic)
5. [Logout Logic](#logout-logic)
6. [Dashboard Logic](#dashboard-logic)
7. [Complete Controller Code](#complete-controller-code)
8. [Testing Controller](#testing-controller)

---

## Understanding Controllers

### What Are Controllers?

**Controllers = Traffic Directors for Your Application**

```
Browser Request → Route → Controller → Model/View → Response
```

**Controller Responsibilities:**
- Receive HTTP requests
- Validate input data
- Interact with models (database)
- Return views or redirects
- Handle business logic

### MVC Pattern

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────┐
│   Route     │ → routes/web.php
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Controller  │ → app/Http/Controllers/
└──────┬──────┘
       │
   ┌───┴────┐
   ▼        ▼
┌──────┐ ┌──────┐
│Model │ │ View │ → app/Models/, resources/views/
└──────┘ └──────┘
   │        │
   └────┬───┘
        ▼
   ┌─────────┐
   │Response │
   └─────────┘
```

### Controller Best Practices

✅ **Do:**
- Keep controllers thin (minimal logic)
- Use descriptive method names
- Validate all input
- Return appropriate responses
- Handle errors gracefully

❌ **Don't:**
- Put business logic in controllers
- Query database directly (use models)
- Hardcode values
- Skip validation
- Ignore error handling

---

## Creating Auth Controller

### Step 1: Create Controller Directory

```powershell
# Create Auth subdirectory
New-Item -ItemType Directory -Path "app\Http\Controllers\Auth" -Force
```

**Expected Output:**
```
    Directory: D:\auth-app\app\Http\Controllers

Mode                 LastWriteTime         Length Name
----                 -------------         ------ ----
d----          12/15/2025  10:00 AM                Auth
```

### Step 2: Create Controller File

```powershell
php artisan make:controller Auth/ManualAuthController
```

**Expected Output:**
```
   INFO  Controller [app/Http/Controllers/Auth/ManualAuthController.php] created successfully.
```

### Step 3: Understand Controller Structure

Open: `app/Http/Controllers/Auth/ManualAuthController.php`

**Default generated code:**
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManualAuthController extends Controller
{
    //
}
```

**Structure Explained:**
- `namespace` - Organizes classes (Auth subdirectory)
- `use` - Imports classes we need
- `extends Controller` - Inherits base controller features
- Empty class body - We'll add methods here

---

## Registration Logic

### Understanding Registration Flow

```
1. User visits /register
   ↓
2. showRegister() → Display registration form
   ↓
3. User fills form (name, email, password, password_confirmation)
   ↓
4. Form submits POST to /register
   ↓
5. register() method executes
   ↓
6. Validate input
   ↓
7. Check if email already exists
   ↓
8. Hash password
   ↓
9. Create user in database
   ↓
10. Create session (auto-login)
   ↓
11. Redirect to dashboard
```

### Step 1: Show Registration Form Method

```php
/**
 * Show registration form
 * 
 * @return \Illuminate\View\View
 */
public function showRegister()
{
    return view('auth.manual.register');
}
```

**Explanation:**
- Method name: `showRegister()` (descriptive)
- Returns a view: `resources/views/auth/manual/register.blade.php`
- No logic needed - just display form
- Simple and clean

### Step 2: Handle Registration Method

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Handle registration form submission
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function register(Request $request)
{
    // Step 1: Validate input
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    // Step 2: Create user
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    // Step 3: Log registration
    Log::info('User registered successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'ip' => $request->ip(),
    ]);

    // Step 4: Create session (auto-login)
    session([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
    ]);

    // Step 5: Redirect to dashboard
    return redirect()->route('manual.dashboard')
        ->with('success', 'Registration successful! Welcome, ' . $user->name);
}
```

### Understanding Validation Rules

```php
$request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
]);
```

**Rule-by-Rule Breakdown:**

**1. name validation:**
```php
'name' => 'required|string|max:255'
```
- `required` - Field must be present and not empty
- `string` - Must be string type
- `max:255` - Maximum 255 characters

**2. email validation:**
```php
'email' => 'required|email|unique:users,email'
```
- `required` - Must be present
- `email` - Must be valid email format (xxx@yyy.zzz)
- `unique:users,email` - Must not exist in `users` table, `email` column

**3. password validation:**
```php
'password' => 'required|min:8|confirmed'
```
- `required` - Must be present
- `min:8` - Minimum 8 characters
- `confirmed` - Must match `password_confirmation` field

**Validation Messages (Auto-Generated):**
```
"The name field is required."
"The email must be a valid email address."
"The email has already been taken."
"The password must be at least 8 characters."
"The password confirmation does not match."
```

### Understanding Hash::make()

```php
Hash::make($validated['password'])
```

**What It Does:**
- Takes plain text password: `"password123"`
- Uses bcrypt algorithm
- Adds random salt
- Produces hash: `"$2y$12$abcd...xyz"` (60 characters)

**Why?**
- ✅ Never store plain text passwords!
- ✅ Each hash is unique (even same password)
- ✅ One-way (can't reverse)
- ✅ Industry standard security

**Example:**
```php
Hash::make('password123')
// "$2y$12$LKl3pQ7R..."

Hash::make('password123')  // Same input
// "$2y$12$9Xp2mN8B..."  // Different output!
```

### Understanding Session Creation

```php
session([
    'user_id' => $user->id,
    'user_name' => $user->name,
    'user_email' => $user->email,
]);
```

**What It Does:**
- Creates PHP session
- Stores user data in session
- Session ID stored in cookie
- Persists across requests

**How It Works:**
```
1. User registers → Session created
2. Session ID: abc123xyz
3. Cookie sent to browser: laravel_session=abc123xyz
4. Browser sends cookie with every request
5. Server retrieves session data
6. We know user is logged in
```

**Session Data Stored:**
```php
$_SESSION = [
    'user_id' => 1,
    'user_name' => 'John Doe',
    'user_email' => 'john@example.com'
]
```

---

## Login Logic

### Understanding Login Flow

```
1. User visits /login
   ↓
2. showLogin() → Display login form
   ↓
3. User enters email + password
   ↓
4. Form submits POST to /login
   ↓
5. login() method executes
   ↓
6. Validate input
   ↓
7. Find user by email
   ↓
8. Verify password with Hash::check()
   ↓
9. If valid: Create session
   ↓
10. Redirect to dashboard
   ↓
11. If invalid: Redirect back with error
```

### Step 1: Show Login Form Method

```php
/**
 * Show login form
 * 
 * @return \Illuminate\View\View
 */
public function showLogin()
{
    return view('auth.manual.login');
}
```

### Step 2: Handle Login Method

```php
/**
 * Handle login form submission
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function login(Request $request)
{
    // Step 1: Validate input
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Step 2: Find user by email
    $user = User::where('email', $validated['email'])->first();

    // Step 3: Check if user exists
    if (!$user) {
        Log::warning('Login failed - User not found', [
            'email' => $validated['email'],
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    // Step 4: Verify password
    if (!Hash::check($validated['password'], $user->password)) {
        Log::warning('Login failed - Invalid password', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'password' => 'The provided password is incorrect.',
        ])->withInput($request->only('email'));
    }

    // Step 5: Create session
    session([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_email' => $user->email,
    ]);

    // Step 6: Log successful login
    Log::info('User logged in successfully', [
        'user_id' => $user->id,
        'email' => $user->email,
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    // Step 7: Redirect to dashboard
    return redirect()->route('manual.dashboard')
        ->with('success', 'Welcome back, ' . $user->name . '!');
}
```

### Understanding Hash::check()

```php
Hash::check($plainPassword, $hashedPassword)
```

**How It Works:**
```php
// During registration:
$hash = Hash::make('password123');
// Stored in database: "$2y$12$abcd...xyz"

// During login:
$isValid = Hash::check('password123', $hash);
// Returns: true

$isValid = Hash::check('wrong-password', $hash);
// Returns: false
```

**Security Features:**
- Constant-time comparison (prevents timing attacks)
- Automatically handles salt
- Works with any bcrypt hash

### Understanding Error Handling

**1. Redirect back with errors:**
```php
return back()->withErrors([
    'email' => 'These credentials do not match our records.',
]);
```

**What It Does:**
- Redirects to previous page (login form)
- Stores error in flash session
- Error available via `@error` or `$errors` in Blade

**2. Keep old input:**
```php
->withInput($request->only('email'))
```

**Why?**
- User doesn't have to retype email
- Only keeps email (NOT password for security)

**3. Display in Blade:**
```blade
@error('email')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<input type="email" name="email" value="{{ old('email') }}">
```

### Understanding Session Regeneration

**Security Best Practice:**
```php
// After successful login, regenerate session ID
session()->regenerate();
```

**Why?**
- Prevents session fixation attacks
- Attacker can't predict session ID
- Creates new session ID after authentication

**Add to login method (enhanced version):**
```php
// Step 5: Create session and regenerate ID
session([
    'user_id' => $user->id,
    'user_name' => $user->name,
    'user_email' => $user->email,
]);
session()->regenerate(); // Security best practice
```

---

## Logout Logic

### Understanding Logout Flow

```
1. User clicks logout button
   ↓
2. POST /logout
   ↓
3. logout() method executes
   ↓
4. Destroy session
   ↓
5. Log the event
   ↓
6. Redirect to login page
```

### Logout Method

```php
/**
 * Handle logout
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function logout(Request $request)
{
    // Step 1: Get user info before destroying session
    $userId = session('user_id');
    $userEmail = session('user_email');

    // Step 2: Log logout event
    if ($userId) {
        Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $userEmail,
            'ip' => $request->ip(),
        ]);
    }

    // Step 3: Destroy session
    session()->flush();
    session()->regenerate();

    // Step 4: Redirect to login
    return redirect()->route('manual.login')
        ->with('success', 'You have been logged out successfully.');
}
```

### Understanding Session Methods

**1. session()->flush():**
```php
session()->flush();
```
- Removes ALL session data
- User is now logged out
- Session file still exists

**2. session()->regenerate():**
```php
session()->regenerate();
```
- Creates new session ID
- Old session ID invalidated
- Prevents session fixation

**3. session()->forget():**
```php
session()->forget('user_id');
```
- Removes specific key
- Other session data remains

**Complete Logout Flow:**
```php
// Before logout:
$_SESSION = [
    'user_id' => 1,
    'user_name' => 'John',
    'user_email' => 'john@example.com'
]

// After flush:
$_SESSION = []  // Empty

// After regenerate:
// New session ID generated
// Old ID no longer valid
```

---

## Dashboard Logic

### Dashboard Method

```php
/**
 * Show dashboard (protected route)
 * 
 * @return \Illuminate\View\View
 */
public function dashboard()
{
    // Get user from session
    $user = User::find(session('user_id'));

    // If user not found (session corrupted), logout
    if (!$user) {
        session()->flush();
        return redirect()->route('manual.login')
            ->withErrors(['error' => 'Session expired. Please login again.']);
    }

    // Pass user to view
    return view('auth.manual.dashboard', compact('user'));
}
```

### Understanding compact()

```php
return view('auth.manual.dashboard', compact('user'));
```

**Is equivalent to:**
```php
return view('auth.manual.dashboard', ['user' => $user]);
```

**Usage in Blade:**
```blade
<h1>Welcome, {{ $user->name }}!</h1>
<p>Email: {{ $user->email }}</p>
```

---

## Complete Controller Code

**File:** `app/Http/Controllers/Auth/ManualAuthController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ManualAuthController extends Controller
{
    /**
     * Show registration form
     * 
     * @return \Illuminate\View\View
     */
    public function showRegister()
    {
        return view('auth.manual.register');
    }

    /**
     * Handle registration form submission
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Log registration
        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        // Create session (auto-login)
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);
        session()->regenerate();

        // Redirect to dashboard
        return redirect()->route('manual.dashboard')
            ->with('success', 'Registration successful! Welcome, ' . $user->name);
    }

    /**
     * Show login form
     * 
     * @return \Illuminate\View\View
     */
    public function showLogin()
    {
        return view('auth.manual.login');
    }

    /**
     * Handle login form submission
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $validated['email'])->first();

        // Check if user exists
        if (!$user) {
            Log::warning('Login failed - User not found', [
                'email' => $validated['email'],
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->withInput($request->only('email'));
        }

        // Verify password
        if (!Hash::check($validated['password'], $user->password)) {
            Log::warning('Login failed - Invalid password', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'password' => 'The provided password is incorrect.',
            ])->withInput($request->only('email'));
        }

        // Create session
        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);
        session()->regenerate();

        // Log successful login
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Redirect to dashboard
        return redirect()->route('manual.dashboard')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle logout
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Get user info before destroying session
        $userId = session('user_id');
        $userEmail = session('user_email');

        // Log logout event
        if ($userId) {
            Log::info('User logged out', [
                'user_id' => $userId,
                'email' => $userEmail,
                'ip' => $request->ip(),
            ]);
        }

        // Destroy session
        session()->flush();
        session()->regenerate();

        // Redirect to login
        return redirect()->route('manual.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show dashboard (protected route)
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        // Get user from session
        $user = User::find(session('user_id'));

        // If user not found (session corrupted), logout
        if (!$user) {
            session()->flush();
            return redirect()->route('manual.login')
                ->withErrors(['error' => 'Session expired. Please login again.']);
        }

        // Pass user to view
        return view('auth.manual.dashboard', compact('user'));
    }
}
```

---

## Testing Controller

### Step 1: Test Registration via Tinker

```powershell
php artisan tinker
```

```php
// Simulate registration request
$request = new Illuminate\Http\Request([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
]);

// Test validation
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
]);

// Should pass validation
print_r($validated);
```

### Step 2: Test Password Hashing

```php
// Hash a password
$hash = Hash::make('password123');
echo $hash;
// Output: $2y$12$abcd...xyz

// Verify password
Hash::check('password123', $hash);
// Returns: true

Hash::check('wrong-password', $hash);
// Returns: false

exit
```

### Step 3: Create Test Routes (Temporary)

Add to `routes/web.php`:

```php
use App\Http\Controllers\Auth\ManualAuthController;

Route::get('/test-register', [ManualAuthController::class, 'showRegister']);
```

### Step 4: Test via Browser (After Views Created)

```powershell
php artisan serve
```

Visit: http://127.0.0.1:8000/test-register

**You should see:**
- Error: View not found (expected - we haven't created views yet)
- This confirms controller is working

---

## Common Controller Issues

### Issue 1: Class Not Found

**Error:**
```
Class "App\Models\User" not found
```

**Solution:**
Add `use` statement at top:
```php
use App\Models\User;
```

### Issue 2: Hash Facade Not Found

**Error:**
```
Class "Hash" not found
```

**Solution:**
```php
use Illuminate\Support\Facades\Hash;
```

### Issue 3: Validation Fails Silently

**Error:**
No error shown, just redirects back

**Solution:**
Validation errors auto-redirect. Check:
```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### Issue 4: Session Not Working

**Error:**
Session data not persisting

**Solutions:**
1. Check session driver in `.env`:
   ```env
   SESSION_DRIVER=file
   ```

2. Clear session:
   ```powershell
   php artisan session:table
   php artisan migrate
   ```

3. Check storage permissions:
   ```powershell
   icacls storage /grant Everyone:F
   ```

---

## Next Steps

✅ **Completed:**
- Controller created
- Registration logic
- Login logic
- Logout logic
- Dashboard logic
- Password hashing
- Session management
- Input validation
- Logging

📝 **Next Document:**
[PHASE1_04_MIDDLEWARE.md](PHASE1_04_MIDDLEWARE.md)

**You will learn:**
- What is middleware
- Creating custom middleware
- Protecting routes
- Guest-only routes
- Middleware registration
- Route groups

---

## Quick Reference

### Controller Methods Summary

```php
// Show forms
showRegister()  → Display registration form
showLogin()     → Display login form

// Handle forms
register()      → Process registration
login()         → Process login
logout()        → Process logout
dashboard()     → Show protected page
```

### Key Functions

```php
// Validation
$request->validate([...])

// Hashing
Hash::make($password)
Hash::check($plain, $hash)

// Session
session(['key' => 'value'])
session('key')
session()->flush()
session()->regenerate()

// Redirects
return redirect()->route('name')
return back()
return redirect('/path')

// Flash messages
->with('success', 'Message')
->withErrors(['field' => 'Error'])
->withInput()
```

### Logging Levels

```php
Log::debug()     // Development only
Log::info()      // General information
Log::warning()   // Potential issues
Log::error()     // Errors occurred
```

---

**Controller Complete!** Proceed to Part 4 for middleware implementation.
