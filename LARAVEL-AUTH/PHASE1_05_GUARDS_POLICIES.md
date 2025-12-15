# Phase 1: Manual Authentication - Part 5: Guards & Policies

## Table of Contents
1. [Understanding Authorization](#understanding-authorization)
2. [Guards Concept](#guards-concept)
3. [Policies Concept](#policies-concept)
4. [Gates vs Policies](#gates-vs-policies)
5. [Implementing Basic Authorization](#implementing-basic-authorization)
6. [Creating Policies](#creating-policies)
7. [Using Gates](#using-gates)

---

## Understanding Authorization

### Authentication vs Authorization

**Two Different Concepts:**

```
AUTHENTICATION (Who are you?)
└─> "Prove you are John Doe"
    └─> Login with email + password
        └─> Session created
            └─> User is AUTHENTICATED

AUTHORIZATION (What can you do?)
└─> "Can John Doe delete this post?"
    └─> Check permissions/roles
        └─> If owner OR admin: YES
            └─> If guest: NO
                └─> User is AUTHORIZED (or not)
```

### Real-World Examples

| Action | Authentication | Authorization |
|--------|---------------|---------------|
| **View dashboard** | Must be logged in | Anyone logged in |
| **Edit own profile** | Must be logged in | Must be the profile owner |
| **Delete user** | Must be logged in | Must be admin |
| **View all orders** | Must be logged in | Must be admin or manager |
| **Process refund** | Must be logged in | Must be manager only |

### Phase 1 Scope

**What we'll cover:**
- ✅ Basic role checking (admin vs user)
- ✅ Resource ownership (can edit own profile)
- ✅ Gates for simple checks
- ✅ Policies for model-based authorization

**What's in Phase 4 (RBAC):**
- ❌ Complex role systems (Spatie Permission)
- ❌ Multiple permissions per user
- ❌ Role hierarchies
- ❌ Dynamic permission assignment

---

## Guards Concept

### What Are Guards?

**Guards = Different Ways to Authenticate Users**

Think of guards as different "ID card systems":

```
Office Building Access:
├─ Employee Badge (Session Guard) ← Phase 1
├─ Visitor Pass (Token Guard) ← Phase 5 (API)
├─ Biometric Scan (OAuth Guard) ← Phase 6
└─ Key Card (Custom Guard)
```

### Laravel's Default Guards

**File:** `config/auth.php`

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'token',
        'provider' => 'users',
    ],
],
```

**Guards Explained:**

**1. Web Guard (Session-based):**
```php
'web' => [
    'driver' => 'session',  // Uses PHP sessions
    'provider' => 'users',  // Gets users from 'users' table
]
```
- Traditional web authentication
- Session cookies
- What we're using in Phase 1
- Used by: `Auth::login()`, `Auth::user()`

**2. API Guard (Token-based):**
```php
'api' => [
    'driver' => 'token',    // Uses API tokens
    'provider' => 'users',  // Gets users from 'users' table
]
```
- Stateless authentication
- Bearer tokens
- Phase 5 (API Authentication)

### Using Guards

**Check current guard:**
```php
// Default guard (web)
if (Auth::check()) {
    $user = Auth::user();
}

// Specific guard
if (Auth::guard('web')->check()) {
    $user = Auth::guard('web')->user();
}

// API guard
if (Auth::guard('api')->check()) {
    $user = Auth::guard('api')->user();
}
```

### Phase 1: Manual Session (Without Auth Facade)

**We're NOT using Laravel's Auth facade yet:**

```php
// ❌ NOT using (that's Phase 2 - Breeze)
Auth::login($user);
Auth::user();
Auth::check();

// ✅ Using manual sessions (Phase 1)
session(['user_id' => $user->id]);
$userId = session('user_id');
session()->has('user_id');
```

**Why?**
- Educational: Understand what Auth facade does
- Learn fundamentals before using abstractions
- Phase 2 will introduce Auth facade with Breeze

---

## Policies Concept

### What Are Policies?

**Policies = Permission Rules for Models**

```
Post Model
    ↓
PostPolicy
    ├─ view() → Can user view this post?
    ├─ create() → Can user create posts?
    ├─ update() → Can user update THIS post?
    ├─ delete() → Can user delete THIS post?
    └─ forceDelete() → Can user permanently delete?
```

**Real Example:**
```php
// User wants to update a post
if ($user->can('update', $post)) {
    $post->update([...]);
} else {
    abort(403, 'Unauthorized');
}
```

### Policy Methods

**Standard RESTful methods:**

| Method | Purpose | Example |
|--------|---------|---------|
| `viewAny()` | List all resources | Can view posts index? |
| `view()` | View single resource | Can view this post? |
| `create()` | Create new resource | Can create posts? |
| `update()` | Update resource | Can edit this post? |
| `delete()` | Delete resource | Can delete this post? |
| `restore()` | Restore soft-deleted | Can restore this post? |
| `forceDelete()` | Permanent delete | Can permanently delete? |

---

## Gates vs Policies

### Understanding the Difference

**Gates = Simple, General Checks**
```php
// Define once
Gate::define('admin-only', function ($user) {
    return $user->role === 'admin';
});

// Use anywhere
if (Gate::allows('admin-only')) {
    // Do admin stuff
}
```

**Policies = Model-Specific Rules**
```php
// PostPolicy.php
public function update(User $user, Post $post)
{
    return $user->id === $post->user_id;
}

// Use in controller
if ($user->can('update', $post)) {
    // Update post
}
```

### When to Use What

**Use Gates for:**
- ✅ General permissions (admin-only, verified-users)
- ✅ Feature flags (beta-feature, premium-feature)
- ✅ Simple role checks
- ✅ Not tied to specific model

**Use Policies for:**
- ✅ Model-specific permissions (edit post, delete comment)
- ✅ Resource ownership (is this my post?)
- ✅ Complex authorization logic
- ✅ RESTful operations

---

## Implementing Basic Authorization

### Step 1: Add Role to Users Table

**Create migration:**
```powershell
php artisan make:migration add_role_to_users_table
```

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_role_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
```

**Run migration:**
```powershell
php artisan migrate
```

### Step 2: Update User Model

**File:** `app/Models/User.php`

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'role',  // Add this
];
```

**Add helper methods:**
```php
/**
 * Check if user is admin
 */
public function isAdmin(): bool
{
    return $this->role === 'admin';
}

/**
 * Check if user is regular user
 */
public function isUser(): bool
{
    return $this->role === 'user';
}

/**
 * Check if user has specific role
 */
public function hasRole(string $role): bool
{
    return $this->role === $role;
}
```

### Step 3: Update Registration (Optional)

**Default all new users to 'user' role:**

Registration already sets default via migration, but you can be explicit:

```php
// In ManualAuthController@register
$user = User::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'password' => Hash::make($validated['password']),
    'role' => 'user', // Explicit default
]);
```

### Step 4: Create Admin User (Testing)

```powershell
php artisan tinker
```

```php
// Create admin user
$admin = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);

// Verify
$admin->isAdmin();  // true
$admin->isUser();   // false

exit
```

---

## Creating Policies

### Step 1: Create Profile Policy

**Scenario:** Users can only edit their own profile

```powershell
php artisan make:policy ProfilePolicy --model=User
```

**Expected Output:**
```
   INFO  Policy [app/Policies/ProfilePolicy.php] created successfully.
```

### Step 2: Implement Policy Methods

**File:** `app/Policies/ProfilePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;

class ProfilePolicy
{
    /**
     * Determine if the user can view any profiles.
     * Only admins can view all profiles
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the profile.
     * Users can view their own profile, admins can view any
     */
    public function view(User $user, User $profile): bool
    {
        return $user->id === $profile->id || $user->isAdmin();
    }

    /**
     * Determine if the user can update the profile.
     * Users can only update their own profile
     */
    public function update(User $user, User $profile): bool
    {
        return $user->id === $profile->id;
    }

    /**
     * Determine if the user can delete the profile.
     * Only admins can delete profiles
     */
    public function delete(User $user, User $profile): bool
    {
        return $user->isAdmin() && $user->id !== $profile->id;
        // Admin can delete others, but not themselves
    }
}
```

### Step 3: Register Policy

**File:** `app/Providers/AuthServiceProvider.php`

**Laravel 11 Note:** This file might not exist. Create it if needed:

```powershell
php artisan make:provider AuthServiceProvider
```

**Then update:**

```php
<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => ProfilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
```

**Register in bootstrap/app.php:**

```php
->withProviders([
    \App\Providers\AuthServiceProvider::class,
])
```

### Step 4: Use Policy in Controller

**Create ProfileController:**

```powershell
php artisan make:controller ProfileController
```

**File:** `app/Http/Controllers/ProfileController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show($id)
    {
        $profile = User::findOrFail($id);
        $currentUser = User::find(session('user_id'));

        // Check policy
        if (!$currentUser || !$currentUser->can('view', $profile)) {
            abort(403, 'Unauthorized to view this profile');
        }

        return view('profile.show', compact('profile'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $profile = User::findOrFail($id);
        $currentUser = User::find(session('user_id'));

        // Check policy
        if (!$currentUser || !$currentUser->can('update', $profile)) {
            abort(403, 'Unauthorized to edit this profile');
        }

        return view('profile.edit', compact('profile'));
    }

    /**
     * Update profile
     */
    public function update(Request $request, $id)
    {
        $profile = User::findOrFail($id);
        $currentUser = User::find(session('user_id'));

        // Check policy
        if (!$currentUser || !$currentUser->can('update', $profile)) {
            abort(403, 'Unauthorized to update this profile');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $profile->id,
        ]);

        $profile->update($validated);

        return redirect()->route('profile.show', $profile->id)
            ->with('success', 'Profile updated successfully');
    }
}
```

---

## Using Gates

### Step 1: Define Gates

**File:** `app/Providers/AuthServiceProvider.php`

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Admin-only gate
    Gate::define('admin-access', function (User $user) {
        return $user->isAdmin();
    });

    // Verified users gate (if email verification added)
    Gate::define('verified-user', function (User $user) {
        return $user->email_verified_at !== null;
    });

    // Premium feature gate (example)
    Gate::define('premium-feature', function (User $user) {
        return $user->subscription === 'premium';
    });
}
```

### Step 2: Use Gates in Controllers

```php
use Illuminate\Support\Facades\Gate;

public function adminPanel()
{
    // Check gate
    if (!Gate::allows('admin-access')) {
        abort(403, 'Admin access required');
    }

    return view('admin.panel');
}

// Or use authorize helper
public function adminPanel()
{
    $this->authorize('admin-access');
    
    return view('admin.panel');
}
```

### Step 3: Use Gates in Middleware

**Create admin middleware:**

```powershell
php artisan make:middleware AdminMiddleware
```

**File:** `app/Http/Middleware/AdminMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Gate::allows('admin-access')) {
            abort(403, 'Admin access required');
        }

        return $next($request);
    }
}
```

**Register:**

```php
// bootstrap/app.php
$middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
]);
```

**Use in routes:**

```php
Route::middleware(['manual.auth', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/settings', [AdminController::class, 'settings']);
});
```

### Step 4: Use Gates in Blade

```blade
@can('admin-access')
    <a href="/admin">Admin Panel</a>
@endcan

@cannot('admin-access')
    <p>You don't have admin access.</p>
@endcannot

@canany(['edit-post', 'delete-post'])
    <button>Manage Post</button>
@endcanany
```

---

## Practical Examples

### Example 1: Dashboard with Role-Based Content

**Controller:**
```php
public function dashboard()
{
    $user = User::find(session('user_id'));

    if (!$user) {
        return redirect()->route('manual.login');
    }

    // Get data based on role
    if ($user->isAdmin()) {
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];
        return view('auth.manual.admin-dashboard', compact('user', 'stats'));
    }

    return view('auth.manual.dashboard', compact('user'));
}
```

**Blade:**
```blade
<h1>Dashboard</h1>

@if($user->isAdmin())
    <div class="admin-section">
        <h2>Admin Stats</h2>
        <p>Total Users: {{ $stats['total_users'] }}</p>
        <p>New Today: {{ $stats['new_users_today'] }}</p>
    </div>
@endif

<div class="user-section">
    <h2>Welcome, {{ $user->name }}</h2>
    <p>Role: {{ ucfirst($user->role) }}</p>
</div>
```

### Example 2: Delete Button with Authorization

```blade
<!-- Only show delete button if authorized -->
@can('delete', $profile)
    <form method="POST" action="{{ route('profile.destroy', $profile->id) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete Profile</button>
    </form>
@endcan
```

### Example 3: Conditional Navigation

```blade
<nav>
    <a href="{{ route('manual.dashboard') }}">Dashboard</a>
    
    @if($user->isAdmin())
        <a href="{{ route('admin.users') }}">Manage Users</a>
        <a href="{{ route('admin.settings') }}">Settings</a>
    @endif
    
    @can('admin-access')
        <a href="{{ route('admin.reports') }}">Reports</a>
    @endcan
</nav>
```

---

## Next Steps

✅ **Completed:**
- Authorization concepts
- Guards understanding
- Policies creation
- Gates implementation
- Role-based access
- Resource ownership

📝 **Next Document:**
[PHASE1_06_ROUTES.md](PHASE1_06_ROUTES.md)

**You will learn:**
- Route definition
- Route naming
- Route groups
- Route parameters
- Resource routes
- Complete route file

---

## Quick Reference

### Policy Methods

```php
// In Policy class
public function update(User $user, Model $model): bool
{
    return $user->id === $model->user_id;
}

// In Controller
if ($user->can('update', $model)) { }

// In Blade
@can('update', $model)
    <!-- Content -->
@endcan
```

### Gates

```php
// Define
Gate::define('name', function (User $user) {
    return $user->isAdmin();
});

// Check in controller
if (Gate::allows('name')) { }
if (Gate::denies('name')) { }
$this->authorize('name');

// Check in Blade
@can('name')
    <!-- Content -->
@endcan
```

### User Model Helpers

```php
$user->isAdmin()
$user->isUser()
$user->hasRole('admin')
```

---

**Authorization Complete!** Proceed to Part 6 for route definitions.
