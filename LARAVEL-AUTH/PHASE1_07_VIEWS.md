# Phase 1: Manual Authentication - Part 7: Bootstrap Views & Blade Templates

## Table of Contents
1. [Understanding Blade](#understanding-blade)
2. [Bootstrap Setup](#bootstrap-setup)
3. [Layout Template](#layout-template)
4. [Registration View](#registration-view)
5. [Login View](#login-view)
6. [Dashboard View](#dashboard-view)
7. [Error Handling](#error-handling)
8. [CSRF Protection](#csrf-protection)

---

## Understanding Blade

### What Is Blade?

**Blade = Laravel's Templating Engine**

```
PHP Code (Complex)              Blade Syntax (Simple)
─────────────────────          ──────────────────────
<?php echo $name; ?>           {{ $name }}
<?php if ($user): ?>           @if($user)
    ...                            ...
<?php endif; ?>                @endif
```

**Benefits:**
- ✅ Cleaner syntax
- ✅ Template inheritance
- ✅ Auto-escaping (security)
- ✅ Components and slots
- ✅ Directives (@if, @foreach, etc.)

### Blade Basics

**Output variables:**
```blade
{{ $variable }}              <!-- Escaped (safe) -->
{!! $variable !!}            <!-- Unescaped (dangerous!) -->
{{ $variable ?? 'default' }} <!-- With default -->
```

**Control structures:**
```blade
@if($condition)
    <!-- Content -->
@elseif($other)
    <!-- Content -->
@else
    <!-- Content -->
@endif

@foreach($items as $item)
    <p>{{ $item }}</p>
@endforeach

@for($i = 0; $i < 10; $i++)
    <p>{{ $i }}</p>
@endfor

@while($condition)
    <!-- Content -->
@endwhile
```

**Include files:**
```blade
@include('partials.header')
@include('partials.sidebar', ['user' => $user])
```

**Template inheritance:**
```blade
<!-- layout.blade.php -->
<html>
    <head>@yield('title')</head>
    <body>@yield('content')</body>
</html>

<!-- page.blade.php -->
@extends('layouts.app')
@section('title', 'Page Title')
@section('content')
    <p>Content here</p>
@endsection
```

---

## Bootstrap Setup

### Step 1: Add Bootstrap via CDN

**Why CDN?**
- ✅ No installation needed
- ✅ Fast setup for Phase 1
- ✅ Cached by browsers
- ✅ Easy to understand

**Note:** Phase 2 will use npm/Vite for assets

### Bootstrap 5.3 CDN Links

```html
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- JavaScript Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

### Bootstrap Grid System

```html
<div class="container">          <!-- Fixed width container -->
    <div class="row">             <!-- Row -->
        <div class="col-md-6">    <!-- 6 columns (50% on medium+) -->
            Content
        </div>
        <div class="col-md-6">    <!-- 6 columns (50% on medium+) -->
            Content
        </div>
    </div>
</div>
```

**Grid breakpoints:**
- `col-` - Extra small (< 576px)
- `col-sm-` - Small (≥ 576px)
- `col-md-` - Medium (≥ 768px)
- `col-lg-` - Large (≥ 992px)
- `col-xl-` - Extra large (≥ 1200px)

---

## Layout Template

### Step 1: Create Layouts Directory

```powershell
New-Item -ItemType Directory -Path "resources\views\layouts" -Force
```

### Step 2: Create Master Layout

**File:** `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel Auth Demo')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 50px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: #667eea;
            font-weight: 700;
            font-size: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation (if user is logged in) -->
    @if(session()->has('user_id'))
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ route('manual.dashboard') }}">Laravel Auth</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manual.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">{{ session('user_name') }}</span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('manual.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link" style="display: inline; cursor: pointer;">
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    @endif

    <!-- Main Content -->
    <div class="container">
        @yield('content')
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
```

### Understanding the Layout

**1. Meta Tags:**
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```
- Provides CSRF token for JavaScript AJAX requests
- Security feature

**2. Dynamic Title:**
```blade
<title>@yield('title', 'Laravel Auth Demo')</title>
```
- `@yield('title')` - Placeholder for page title
- `'Laravel Auth Demo'` - Default if no title provided

**3. Conditional Navigation:**
```blade
@if(session()->has('user_id'))
    <nav>...</nav>
@endif
```
- Only shows navbar if user is logged in
- Checks for 'user_id' in session

**4. Content Section:**
```blade
@yield('content')
```
- Main placeholder for page content
- Child views will fill this section

**5. Stacks:**
```blade
@stack('styles')
@stack('scripts')
```
- Allows child views to add custom CSS/JS
- More on this later

---

## Registration View

### Step 1: Create Auth Directory

```powershell
New-Item -ItemType Directory -Path "resources\views\auth\manual" -Force
```

### Step 2: Create Registration View

**File:** `resources/views/auth/manual/register.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Register - Laravel Auth Demo')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <h1>Create Account</h1>
                <p class="text-muted">Join us today! It's free and easy.</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Oops!</strong> Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('manual.register') }}">
                @csrf

                <!-- Name Field -->
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input 
                        type="text" 
                        class="form-control @error('name') is-invalid @enderror" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        placeholder="John Doe"
                        required
                        autofocus
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="john@example.com"
                        required
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Minimum 8 characters"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Must be at least 8 characters long.</small>
                </div>

                <!-- Password Confirmation Field -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        placeholder="Re-type your password"
                        required
                    >
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Register
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-muted">
                        Already have an account? 
                        <a href="{{ route('manual.login') }}" class="text-decoration-none">
                            Login here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

### Understanding Registration Form

**1. Form Declaration:**
```blade
<form method="POST" action="{{ route('manual.register') }}">
```
- `method="POST"` - HTTP method
- `action="{{ route('manual.register') }}"` - Submits to register route

**2. CSRF Token:**
```blade
@csrf
```
- **CRITICAL!** Laravel requires this on all POST/PUT/DELETE
- Prevents Cross-Site Request Forgery attacks
- Generates: `<input type="hidden" name="_token" value="...">`

**3. Old Input:**
```blade
value="{{ old('name') }}"
```
- Repopulates field after validation error
- User doesn't lose their input

**4. Error Display:**
```blade
@error('name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```
- Shows error message for specific field
- Bootstrap's `.invalid-feedback` class

**5. Conditional Classes:**
```blade
class="form-control @error('name') is-invalid @enderror"
```
- Adds `is-invalid` class if error exists
- Bootstrap shows red border

---

## Login View

**File:** `resources/views/auth/manual/login.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Login - Laravel Auth Demo')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p class="text-muted">Sign in to continue to your account</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Login Failed!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('manual.login') }}">
                @csrf

                <!-- Email Field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="Enter your email"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me Checkbox (Optional) -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Login
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-muted">
                        Don't have an account? 
                        <a href="{{ route('manual.register') }}" class="text-decoration-none">
                            Register here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

## Dashboard View

**File:** `resources/views/auth/manual/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Dashboard - Laravel Auth Demo')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-md-10">
        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Welcome Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <h1 class="display-4 mb-3">Welcome, {{ $user->name }}! 🎉</h1>
                <p class="lead text-muted">You have successfully logged in to your account.</p>
            </div>
        </div>

        <!-- User Info Card -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">User ID:</td>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Name:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Role:</td>
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Member Since:</td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Session Info Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Session Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Session ID:</td>
                                    <td><code>{{ session()->getId() }}</code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Logged In As:</td>
                                    <td>{{ session('user_name') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ session('user_email') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">IP Address:</td>
                                    <td>{{ request()->ip() }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">User Agent:</td>
                                    <td class="text-truncate" style="max-width: 200px;" title="{{ request()->userAgent() }}">
                                        {{ Str::limit(request()->userAgent(), 30) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="#" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-circle"></i> Edit Profile
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="#" class="btn btn-outline-info w-100">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </div>
                    <div class="col-md-4">
                        <form method="POST" action="{{ route('manual.logout') }}" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase Info -->
        <div class="alert alert-info mt-4" role="alert">
            <h5 class="alert-heading">Phase 1: Manual Authentication</h5>
            <p class="mb-0">
                This is a manual session-based authentication system built from scratch without using Laravel Breeze or Jetstream. 
                You're seeing this dashboard because you successfully authenticated using custom controllers and middleware.
            </p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endpush
```

### Understanding Dashboard Features

**1. User Object:**
```blade
{{ $user->name }}
{{ $user->email }}
```
- Passed from controller: `compact('user')`
- Accessed via object properties

**2. Date Formatting:**
```blade
{{ $user->created_at->format('M d, Y') }}
```
- Laravel's Carbon date helper
- Formats: "Dec 15, 2025"

**3. Conditional Badge:**
```blade
<span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'info' }}">
```
- Ternary operator in Blade
- Admin = red badge, User = blue badge

**4. String Helper:**
```blade
{{ Str::limit(request()->userAgent(), 30) }}
```
- Laravel's Str helper
- Limits string to 30 characters

**5. Push Directive:**
```blade
@push('styles')
    <link rel="...">
@endpush
```
- Adds content to `@stack('styles')` in layout
- Useful for page-specific assets

---

## Error Handling

### Global Error Display

**Add to all forms:**

```blade
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following:
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

### Field-Specific Errors

```blade
<!-- Input with error -->
<input 
    type="text" 
    class="form-control @error('name') is-invalid @enderror" 
    name="name"
>

<!-- Error message -->
@error('name')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
```

### Success Messages

```blade
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

---

## CSRF Protection

### What Is CSRF?

**Cross-Site Request Forgery Attack:**

```
1. User logs into yoursite.com
2. User visits evilsite.com
3. evilsite.com has hidden form:
   <form action="yoursite.com/delete-account" method="POST">
4. Form auto-submits
5. Without CSRF protection: Account deleted!
```

### How Laravel Prevents This

**1. Add @csrf to every form:**
```blade
<form method="POST" action="/login">
    @csrf  <!-- CRITICAL! -->
    <!-- form fields -->
</form>
```

**2. Laravel generates:**
```html
<input type="hidden" name="_token" value="random-token-here">
```

**3. On submit, Laravel checks:**
- Token in form matches token in session
- If match: Process request ✅
- If no match: 419 error ❌

### CSRF in Different Methods

**POST Form:**
```blade
<form method="POST" action="{{ route('manual.login') }}">
    @csrf
    <!-- fields -->
</form>
```

**DELETE/PUT Method Spoofing:**
```blade
<form method="POST" action="{{ route('user.destroy', $user->id) }}">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
```

**AJAX Requests:**
```javascript
fetch('/api/data', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});
```

---

## Next Steps

✅ **Completed:**
- Blade templating
- Bootstrap integration
- Layout creation
- Registration view
- Login view
- Dashboard view
- Error handling
- CSRF protection

📝 **Next Document:**
[PHASE1_08_TESTING.md](PHASE1_08_TESTING.md)

**You will learn:**
- Manual testing guide
- Testing checklist
- Common errors
- Debugging tips
- Production deployment
- Security best practices

---

## Quick Reference

### Blade Directives

```blade
{{ $variable }}               <!-- Echo (escaped) -->
{!! $html !!}                <!-- Echo (unescaped) -->
@if($condition) ... @endif   <!-- If statement -->
@foreach($items as $item)    <!-- Loop -->
@csrf                        <!-- CSRF token -->
@error('field')              <!-- Field error -->
@extends('layout')           <!-- Extend layout -->
@section('name')             <!-- Define section -->
@yield('name')               <!-- Show section -->
@include('partial')          <!-- Include file -->
@push('stack')               <!-- Push to stack -->
@stack('stack')              <!-- Show stack -->
```

### Bootstrap Classes

```html
<!-- Layout -->
.container, .row, .col-md-6

<!-- Forms -->
.form-control, .form-label, .is-invalid, .invalid-feedback

<!-- Buttons -->
.btn, .btn-primary, .btn-lg, .d-grid

<!-- Alerts -->
.alert, .alert-success, .alert-danger, .alert-dismissible

<!-- Cards -->
.card, .card-header, .card-body

<!-- Utilities -->
.mb-3, .mt-4, .text-center, .shadow-sm
```

---

**Views Complete!** Proceed to Part 8 for testing and deployment.
