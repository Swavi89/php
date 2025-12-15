# Phase 2: Laravel Breeze with Bootstrap - Part 3: Bootstrap Integration

## Table of Contents
1. [Removing Tailwind CSS](#removing-tailwind-css)
2. [Installing Bootstrap 5](#installing-bootstrap-5)
3. [Configuring Vite](#configuring-vite)
4. [Setting Up Bootstrap SASS](#setting-up-bootstrap-sass)
5. [Testing Bootstrap Installation](#testing-bootstrap-installation)
6. [Adding Bootstrap Icons](#adding-bootstrap-icons)

---

## Removing Tailwind CSS

### Understanding the Change

**Why remove Tailwind?**
```
Breeze Default:
├─ Uses Tailwind CSS utility classes
├─ Example: class="flex items-center justify-between"
└─ Great, but different from Bootstrap

Bootstrap Alternative:
├─ Uses Bootstrap component classes
├─ Example: class="d-flex align-items-center justify-content-between"
└─ More traditional, widely used
```

### Step 1: Remove Tailwind Dependencies

```powershell
# Remove Tailwind packages
npm uninstall tailwindcss @tailwindcss/forms postcss autoprefixer
```

**Expected output:**
```
removed 5 packages, and audited 121 packages in 3s

21 packages are looking for funding
  run `npm fund` for details

found 0 vulnerabilities
```

### Step 2: Delete Tailwind Configuration

```powershell
# Remove Tailwind config file
Remove-Item tailwind.config.js -ErrorAction SilentlyContinue

# Remove PostCSS config (we'll recreate it)
Remove-Item postcss.config.js -ErrorAction SilentlyContinue
```

### Step 3: Clean CSS File

**File:** `resources/css/app.css`

**Old content (with Tailwind):**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**Replace with:**
```css
/* Bootstrap will be imported here */
```

**Using PowerShell:**
```powershell
Set-Content -Path "resources\css\app.css" -Value "/* Bootstrap will be imported here */"
```

---

## Installing Bootstrap 5

### Step 1: Install Bootstrap Package

```powershell
npm install bootstrap@5.3.3
```

**Expected output:**
```
added 2 packages, and audited 123 packages in 4s

21 packages are looking for funding
  run `npm fund` for details

found 0 vulnerabilities
```

**What was installed:**
- `bootstrap` - Bootstrap CSS and JavaScript
- `@popperjs/core` - Required for Bootstrap dropdowns, tooltips

### Step 2: Install SASS

Bootstrap uses SASS for customization.

```powershell
npm install sass --save-dev
```

**Expected output:**
```
added 3 packages, and audited 126 packages in 5s

found 0 vulnerabilities
```

### Step 3: Verify Installation

```powershell
# Check package.json
Get-Content package.json | Select-String "bootstrap|sass"
```

**Expected output:**
```
    "bootstrap": "^5.3.3",
    "sass": "^1.71.0"
```

---

## Configuring Vite

### Step 1: Update Vite Configuration

**File:** `vite.config.js`

**Replace entire content:**
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

**Explanation:**
- `input` - Files to process (CSS and JS)
- `refresh` - Auto-refresh browser on changes

### Step 2: Update JavaScript Entry

**File:** `resources/js/app.js`

**Old content:**
```js
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

**Replace with:**
```js
import './bootstrap';

// Import Bootstrap
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
```

**Explanation:**
- Import Bootstrap JavaScript
- Make it globally available
- Remove Alpine.js (not needed)

### Step 3: Update Bootstrap.js

**File:** `resources/js/bootstrap.js`

This file handles Axios configuration (keep as is):

```js
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

---

## Setting Up Bootstrap SASS

### Step 1: Rename CSS to SCSS

```powershell
# Rename app.css to app.scss
Rename-Item resources\css\app.css app.scss
```

### Step 2: Import Bootstrap

**File:** `resources/css/app.scss`

```scss
// Custom variables (optional customization)
// $primary: #your-color;

// Import Bootstrap
@import 'bootstrap/scss/bootstrap';

// Custom styles
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.navbar-brand {
    font-weight: bold;
}

// Alert animations
.alert {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

// Card hover effects
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

// Button improvements
.btn {
    transition: all 0.2s;
}

// Form focus styles
.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

// Custom spacing
.section-padding {
    padding: 3rem 0;
}

// Utility classes
.min-vh-100 {
    min-height: 100vh;
}
```

### Step 3: Update Vite Config for SCSS

**File:** `vite.config.js`

**Update input to use .scss:**
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.scss',  // Changed from .css
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

### Step 4: Bootstrap Customization (Optional)

**For custom colors, create:** `resources/css/_variables.scss`

```scss
// Brand colors
$primary: #0d6efd;
$secondary: #6c757d;
$success: #198754;
$danger: #dc3545;
$warning: #ffc107;
$info: #0dcaf0;

// Fonts
$font-family-sans-serif: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;

// Spacing
$spacer: 1rem;

// Border radius
$border-radius: 0.375rem;

// Navbar
$navbar-dark-color: rgba(255, 255, 255, 0.9);
$navbar-dark-hover-color: rgba(255, 255, 255, 1);
```

**Then in app.scss:**
```scss
// Import custom variables first
@import 'variables';

// Import Bootstrap
@import 'bootstrap/scss/bootstrap';

// Your custom styles...
```

---

## Testing Bootstrap Installation

### Step 1: Rebuild Assets

**Stop previous npm run dev (Ctrl+C), then:**

```powershell
npm run dev
```

**Expected output:**
```
> dev
> vite

  VITE v5.4.21  ready in 345 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help

  LARAVEL v11.47.0  plugin v1.2.0

  ➜  APP_URL: http://localhost
```

**Look for:**
```
✓ built in 234ms
```

### Step 2: Create Test View

**File:** `resources/views/test-bootstrap.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Test</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-primary">Bootstrap 5 Test</h1>
        
        <!-- Alert -->
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle"></i> Bootstrap is working!
        </div>
        
        <!-- Buttons -->
        <div class="mb-3">
            <button class="btn btn-primary">Primary</button>
            <button class="btn btn-secondary">Secondary</button>
            <button class="btn btn-success">Success</button>
            <button class="btn btn-danger">Danger</button>
        </div>
        
        <!-- Card -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Card Header
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Card Title</h5>
                        <p class="card-text">This is a Bootstrap card component.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form -->
        <div class="mt-4">
            <form>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" placeholder="name@example.com">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        
        <!-- Dropdown -->
        <div class="dropdown mt-4">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Dropdown button
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Action</a></li>
                <li><a class="dropdown-item" href="#">Another action</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Something else</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
```

### Step 3: Add Test Route

**File:** `routes/web.php`

**Add at the top:**
```php
Route::get('/test-bootstrap', function () {
    return view('test-bootstrap');
});
```

### Step 4: Visit Test Page

**Visit:** http://127.0.0.1:8000/test-bootstrap

**Check:**
- ✅ Bootstrap styles loaded (buttons, cards, forms)
- ✅ Colors correct (blue primary, etc.)
- ✅ Dropdown works (click to open)
- ✅ Forms styled properly
- ✅ Responsive (resize browser)

### Step 5: Browser Console Check

**Open Developer Tools (F12)**

**Console tab should show:**
```
No errors
```

**Network tab:**
- `app.scss` loaded (compiled to CSS)
- `app.js` loaded
- No 404 errors

### Step 6: Test JavaScript

**Open browser console and type:**
```javascript
bootstrap
```

**Expected output:**
```javascript
{Alert: ƒ, Button: ƒ, Carousel: ƒ, Collapse: ƒ, Dropdown: ƒ, ...}
```

**Test dropdown:**
```javascript
// Should work when clicking dropdown button
```

---

## Adding Bootstrap Icons

### Step 1: Install Bootstrap Icons

```powershell
npm install bootstrap-icons
```

**Expected output:**
```
added 1 package, and audited 127 packages in 3s

found 0 vulnerabilities
```

### Step 2: Import Icons in SCSS

**File:** `resources/css/app.scss`

**Add at top:**
```scss
// Import Bootstrap Icons
@import 'bootstrap-icons/font/bootstrap-icons';

// Custom variables (optional)
// $primary: #your-color;

// Import Bootstrap
@import 'bootstrap/scss/bootstrap';

// Rest of your custom styles...
```

### Step 3: Test Icons

**Update test view:**

```blade
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i> Bootstrap Icons working!
</div>

<div class="mb-3">
    <button class="btn btn-primary">
        <i class="bi bi-save"></i> Save
    </button>
    <button class="btn btn-secondary">
        <i class="bi bi-pencil"></i> Edit
    </button>
    <button class="btn btn-danger">
        <i class="bi bi-trash"></i> Delete
    </button>
</div>
```

### Step 4: Rebuild and Test

```powershell
# Rebuild (npm run dev should auto-rebuild)
# Visit: http://127.0.0.1:8000/test-bootstrap
```

**Check:**
- ✅ Icons display next to text
- ✅ Icons scale properly
- ✅ Icons in different colors

### Common Bootstrap Icons

```html
<!-- Actions -->
<i class="bi bi-save"></i>        <!-- Save -->
<i class="bi bi-pencil"></i>      <!-- Edit -->
<i class="bi bi-trash"></i>       <!-- Delete -->
<i class="bi bi-plus"></i>        <!-- Add -->
<i class="bi bi-x"></i>           <!-- Close -->

<!-- Navigation -->
<i class="bi bi-house"></i>       <!-- Home -->
<i class="bi bi-gear"></i>        <!-- Settings -->
<i class="bi bi-person"></i>      <!-- User -->
<i class="bi bi-box-arrow-right"></i> <!-- Logout -->

<!-- Status -->
<i class="bi bi-check-circle"></i>      <!-- Success -->
<i class="bi bi-exclamation-triangle"></i> <!-- Warning -->
<i class="bi bi-x-circle"></i>          <!-- Error -->
<i class="bi bi-info-circle"></i>       <!-- Info -->

<!-- Forms -->
<i class="bi bi-envelope"></i>    <!-- Email -->
<i class="bi bi-lock"></i>        <!-- Password -->
<i class="bi bi-eye"></i>         <!-- Show -->
<i class="bi bi-eye-slash"></i>   <!-- Hide -->
```

---

## Troubleshooting

### Issue 1: Styles Not Loading

**Symptoms:**
- Page has no styling
- Plain HTML appearance

**Solutions:**

**A. Check Vite is running:**
```powershell
# Should see:
# VITE v5.4.21 ready
npm run dev
```

**B. Check Blade template includes Vite:**
```blade
@vite(['resources/css/app.scss', 'resources/js/app.js'])
```

**C. Clear cache:**
```powershell
php artisan config:clear
php artisan cache:clear
```

**D. Rebuild assets:**
```powershell
npm run build
```

### Issue 2: SASS Compilation Error

**Error:**
```
Error: Can't find stylesheet to import.
@import 'bootstrap/scss/bootstrap';
```

**Solution:**

```powershell
# Reinstall Bootstrap
npm install bootstrap

# Check node_modules exists
Test-Path node_modules\bootstrap

# Rebuild
npm run dev
```

### Issue 3: JavaScript Not Working

**Symptoms:**
- Dropdowns don't work
- No console errors but no functionality

**Solutions:**

**A. Check bootstrap imported in app.js:**
```js
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
```

**B. Check browser console:**
```javascript
// Should return object
bootstrap
```

**C. Rebuild JavaScript:**
```powershell
npm run dev
```

### Issue 4: Icons Not Showing

**Symptoms:**
- Square boxes instead of icons
- Missing icon fonts

**Solutions:**

**A. Check import in app.scss:**
```scss
@import 'bootstrap-icons/font/bootstrap-icons';
```

**B. Reinstall icons:**
```powershell
npm install bootstrap-icons
npm run dev
```

**C. Check font files copied:**
```powershell
# Should exist after build:
Test-Path public\build\assets\*.woff
```

### Issue 5: Build Errors

**Error:**
```
[vite] Internal server error: Expected identifier but found "("
```

**Solution:**

**Check syntax in files:**
```scss
// WRONG
@import bootstrap/scss/bootstrap;

// CORRECT
@import 'bootstrap/scss/bootstrap';
```

**Clear and rebuild:**
```powershell
Remove-Item -Recurse node_modules
npm install
npm run dev
```

---

## Production Build

### Building for Production

**When ready to deploy:**

```powershell
npm run build
```

**Expected output:**
```
> build
> vite build

vite v5.4.21 building for production...
✓ 125 modules transformed.
public/build/manifest.json        0.45 kB │ gzip: 0.20 kB
public/build/assets/app-abc123.css    185.32 kB │ gzip: 25.14 kB
public/build/assets/app-xyz789.js     45.78 kB │ gzip: 15.62 kB
✓ built in 2.34s
```

**What happens:**
- CSS minified
- JavaScript minified
- Files fingerprinted (cache busting)
- Placed in `public/build/`

**Update .env for production:**
```env
APP_ENV=production
APP_DEBUG=false
```

**Blade automatically uses built assets:**
```blade
{{-- Development: loads from Vite dev server --}}
{{-- Production: loads from public/build/ --}}
@vite(['resources/css/app.scss', 'resources/js/app.js'])
```

---

## Next Steps

✅ **Completed:**
- Tailwind CSS removed
- Bootstrap 5 installed
- Vite configured
- SASS setup complete
- Bootstrap Icons added
- Testing successful

📝 **Next Document:**
[PHASE2_04_CONVERTING_VIEWS.md](PHASE2_04_CONVERTING_VIEWS.md)

**You will learn:**
- Converting Breeze views to Bootstrap
- Creating guest layout
- Creating app layout
- Converting login view
- Converting registration view
- Navigation component

---

## Quick Reference

### Installation Commands

```powershell
# Remove Tailwind
npm uninstall tailwindcss @tailwindcss/forms postcss autoprefixer

# Install Bootstrap
npm install bootstrap@5.3.3 sass --save-dev

# Install Icons
npm install bootstrap-icons

# Development
npm run dev

# Production
npm run build
```

### File Structure

```
resources/
├── css/
│   ├── app.scss              # Main SCSS file
│   └── _variables.scss       # Custom variables (optional)
├── js/
│   ├── app.js               # Main JavaScript
│   └── bootstrap.js         # Axios config
└── views/
    └── test-bootstrap.blade.php
```

### SCSS Import Order

```scss
// 1. Custom variables (optional)
@import 'variables';

// 2. Bootstrap Icons
@import 'bootstrap-icons/font/bootstrap-icons';

// 3. Bootstrap
@import 'bootstrap/scss/bootstrap';

// 4. Custom styles
.your-custom-class { }
```

### Vite Blade Directive

```blade
{{-- Always use this in layout head --}}
@vite(['resources/css/app.scss', 'resources/js/app.js'])
```

---

**Bootstrap integration complete!** Ready to convert Breeze views to Bootstrap.
