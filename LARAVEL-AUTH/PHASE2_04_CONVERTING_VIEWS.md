# Phase 2: Laravel Breeze with Bootstrap - Part 4: Converting Views (Layouts)

## Table of Contents
1. [Understanding Breeze Layouts](#understanding-breeze-layouts)
2. [Converting Guest Layout](#converting-guest-layout)
3. [Converting App Layout](#converting-app-layout)
4. [Creating Navigation Component](#creating-navigation-component)
5. [Testing Layouts](#testing-layouts)

---

## Understanding Breeze Layouts

### Layout Architecture

```
Breeze Layout System:
├── layouts/
│   ├── guest.blade.php        # For unauthenticated users (login, register)
│   ├── app.blade.php          # For authenticated users (dashboard, profile)
│   └── navigation.blade.php   # Navigation menu (header)
└── Usage:
    ├── Auth views extend guest.blade.php
    └── Dashboard/Profile extend app.blade.php
```

### Tailwind vs Bootstrap Classes

**Common conversions:**

| Element | Tailwind (Old) | Bootstrap (New) |
|---------|---------------|-----------------|
| Container | `max-w-7xl mx-auto` | `container` |
| Flex row | `flex items-center` | `d-flex align-items-center` |
| Flex column | `flex flex-col` | `d-flex flex-column` |
| Spacing | `mt-4 mb-6` | `mt-4 mb-5` |
| Text center | `text-center` | `text-center` |
| Background | `bg-gray-100` | `bg-light` |
| Text color | `text-gray-900` | `text-dark` |
| Button | `px-4 py-2 bg-blue-500` | `btn btn-primary` |
| Card | `bg-white shadow rounded` | `card` |
| Grid | `grid grid-cols-2` | `row`, `col-md-6` |

---

## Converting Guest Layout

### Original Guest Layout (Tailwind)

**File:** `resources/views/layouts/guest.blade.php`

**Current content:**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
```

### Bootstrap Converted Guest Layout

**Replace entire content:**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Authentication')</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .app-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .app-logo i {
            font-size: 2.5rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <!-- Logo -->
                <div class="text-center mb-4">
                    <a href="/" class="text-decoration-none">
                        <div class="app-logo">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4 class="text-white fw-bold">{{ config('app.name', 'Laravel') }}</h4>
                    </a>
                </div>

                <!-- Card Content -->
                <div class="auth-card p-4">
                    {{ $slot }}
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-3">
                    <p class="text-white-50 small mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

**Key changes:**
- ✅ Bootstrap grid system (`container`, `row`, `col`)
- ✅ Custom gradient background
- ✅ Beautiful card styling with backdrop blur
- ✅ Centered logo with icon
- ✅ Responsive design (col-md-5 col-lg-4)
- ✅ Modern glassmorphism effect
- ✅ Footer with copyright

---

## Converting App Layout

### Original App Layout (Tailwind)

**File:** `resources/views/layouts/app.blade.php`

**Current content:**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
```

### Bootstrap Converted App Layout

**Replace entire content:**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Navigation -->
    @include('layouts.navigation')

    <!-- Page Header -->
    @isset($header)
        <header class="bg-white shadow-sm border-bottom">
            <div class="container py-3">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <!-- Session Status Messages -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Page Content -->
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy</a>
                    <a href="#" class="text-muted text-decoration-none me-3">Terms</a>
                    <a href="#" class="text-muted text-decoration-none">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
```

**Key changes:**
- ✅ Bootstrap container for content
- ✅ Session flash messages with icons
- ✅ Dismissible alerts
- ✅ Professional footer
- ✅ Stack sections for additional CSS/JS
- ✅ Dynamic page titles with @yield

---

## Creating Navigation Component

### Original Navigation (Tailwind)

**File:** `resources/views/layouts/navigation.blade.php`

Very long file with Alpine.js dropdowns and hamburger menu.

### Bootstrap Converted Navigation

**Replace entire content:**

```blade
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-shield-lock me-2"></i>
            <span class="fw-bold">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Side -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-house me-1"></i> Dashboard
                    </a>
                </li>
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav ms-auto">
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       id="userDropdown" 
                       data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                             style="width: 32px; height: 32px; font-size: 14px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <div class="small text-muted">{{ Auth::user()->email }}</div>
                            </h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i> Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

**Key features:**
- ✅ Bootstrap navbar with dark theme
- ✅ Responsive hamburger menu
- ✅ User avatar (first letter of name)
- ✅ Dropdown menu with profile/logout
- ✅ Active link highlighting
- ✅ Icons for all menu items
- ✅ Email shown in dropdown header
- ✅ Logout form in dropdown

### Enhanced Navigation (Optional)

**With notification badge and search:**

```blade
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="bi bi-shield-lock me-2"></i>
            <span class="fw-bold">{{ config('app.name', 'Laravel') }}</span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Side Navigation -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-house me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-grid me-1"></i> Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-people me-1"></i> Team
                    </a>
                </li>
            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav ms-auto">
                <!-- Search -->
                <li class="nav-item me-2">
                    <form class="d-flex" role="search">
                        <input class="form-control form-control-sm" type="search" placeholder="Search...">
                    </form>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">New message from John</a></li>
                        <li><a class="dropdown-item" href="#">Your profile was updated</a></li>
                        <li><a class="dropdown-item" href="#">New comment on your post</a></li>
                    </ul>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       id="userDropdown" 
                       data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                             style="width: 32px; height: 32px; font-size: 14px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <h6 class="dropdown-header">
                                <div class="fw-bold">{{ Auth::user()->name }}</div>
                                <div class="small text-muted">{{ Auth::user()->email }}</div>
                            </h6>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i> Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-question-circle me-2"></i> Help
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

---

## Testing Layouts

### Test 1: Guest Layout

**Visit any auth page:**
- http://127.0.0.1:8000/login
- http://127.0.0.1:8000/register

**Check:**
- ✅ Gradient background displays
- ✅ Centered card with glassmorphism
- ✅ Logo with icon visible
- ✅ App name displays
- ✅ Footer shows copyright
- ✅ Responsive on mobile (resize browser)

### Test 2: App Layout

**Login and visit:**
- http://127.0.0.1:8000/dashboard

**Check:**
- ✅ Navigation bar displays
- ✅ User name in dropdown
- ✅ Avatar with first letter
- ✅ Dropdown works on click
- ✅ Logout button visible
- ✅ Footer displays
- ✅ Content container centered

### Test 3: Navigation Functionality

**Desktop:**
1. Click user dropdown
2. Should open smoothly
3. Click outside, should close
4. Hover over menu items

**Mobile (resize browser to < 768px):**
1. Hamburger icon visible
2. Click hamburger
3. Menu slides down
4. All links visible
5. User dropdown still works

### Test 4: Session Messages

**Test flash messages:**

**Add to any route:**
```php
Route::get('/test-message', function () {
    return redirect()->route('dashboard')
        ->with('status', 'This is a success message!');
});
```

**Visit:** http://127.0.0.1:8000/test-message

**Check:**
- ✅ Green success alert displays
- ✅ Check icon visible
- ✅ Close button works
- ✅ Alert dismisses on click

### Test 5: Multiple Users

**Create another user:**

```powershell
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'password' => Hash::make('password123'),
]);
exit
```

**Logout, login as Jane:**
- Avatar shows "J"
- Name shows "Jane Smith"
- Email in dropdown correct

---

## Quick Reference

### Layout Usage

**Guest pages (login, register):**
```blade
<x-guest-layout>
    {{-- Your form here --}}
</x-guest-layout>
```

**Authenticated pages (dashboard):**
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-dark">Dashboard</h2>
    </x-slot>

    {{-- Your content here --}}
</x-app-layout>
```

### Flash Messages

**Controller:**
```php
return redirect()->route('dashboard')
    ->with('status', 'Success message!');
    
return redirect()->route('dashboard')
    ->with('error', 'Error message!');
```

### Active Link Highlighting

```blade
<a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
   href="{{ route('dashboard') }}">
    Dashboard
</a>
```

---

## Next Steps

✅ **Completed:**
- Guest layout converted to Bootstrap
- App layout converted to Bootstrap
- Navigation component created
- Layouts tested successfully

📝 **Next Document:**
[PHASE2_05_CONVERTING_AUTH_VIEWS.md](PHASE2_05_CONVERTING_AUTH_VIEWS.md)

**You will learn:**
- Converting login view
- Converting registration view
- Converting forgot password view
- Converting reset password view
- Converting email verification views
- Form validation errors styling

---

**Layouts complete!** Ready to convert authentication views.
