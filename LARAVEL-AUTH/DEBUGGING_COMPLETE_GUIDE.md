# Complete Debugging & Logging Guide for Laravel

## Table of Contents
1. [Development Debugging Techniques](#development-debugging)
2. [Xdebug Setup (Complete Guide)](#xdebug-setup)
3. [Production Logging](#production-logging)
4. [Best Practices](#best-practices)
5. [Troubleshooting](#troubleshooting)

---

## Development Debugging

### 1. Quick Debug Functions

#### A. `dd()` - Dump and Die
**When to use:** Quick variable inspection, stops execution

```php
// In controller
public function login(Request $request)
{
    dd($request->all()); // Shows all request data and stops
    
    // Multiple variables
    dd($request->email, $request->password, auth()->user());
    
    // With labels
    dd([
        'Request Data' => $request->all(),
        'Session' => session()->all(),
        'User' => auth()->user()
    ]);
}
```

**Output:** HTML formatted dump with syntax highlighting

#### B. `dump()` - Dump and Continue
**When to use:** Need to see multiple points without stopping

```php
public function login(Request $request)
{
    dump('Starting login process'); // Step 1
    
    $user = User::where('email', $request->email)->first();
    dump('User found:', $user); // Step 2
    
    if ($user) {
        dump('Verifying password'); // Step 3
        $valid = Hash::check($request->password, $user->password);
        dump('Password valid:', $valid); // Step 4
    }
    
    return view('dashboard');
}
```

#### C. `ddd()` - Dump, Die, and Debug
**When to use:** Enhanced dd() with additional debug info

```php
ddd($variable); // Shows variable + debug trace
```

### 2. Laravel Debugbar

#### Installation (Already Done)
```bash
composer require barryvdh/laravel-debugbar --dev
```

#### Using Debugbar

**Automatic Features:**
- Timeline - Execution time per operation
- Messages - Custom debug messages
- Exceptions - All exceptions thrown
- Database - All queries executed
- Views - Templates rendered
- Route - Current route info
- Queries - SQL with bindings
- Session - Session data
- Request - Request data

**Add Custom Messages:**
```php
use Illuminate\Support\Facades\Log;
use Barryvdh\Debugbar\Facades\Debugbar;

// Add messages to debugbar
Debugbar::info('User login attempt');
Debugbar::warning('Slow query detected');
Debugbar::error('Validation failed');

// Add to specific collectors
Debugbar::addMessage('Custom message', 'messages');

// Measure execution time
Debugbar::startMeasure('process', 'Processing data');
// ... your code ...
Debugbar::stopMeasure('process');
```

**Access in Browser:**
1. Run application: `php artisan serve`
2. Visit any page: http://127.0.0.1:8000
3. Look at bottom toolbar (orange bar)
4. Click tabs to inspect different aspects

### 3. Logging

#### Log Levels (Lowest to Highest Severity)
```php
use Illuminate\Support\Facades\Log;

Log::debug('Detailed debug info');      // Development only
Log::info('Informational message');     // General info
Log::notice('Normal but significant');  // Worth noting
Log::warning('Warning message');        // Potential issue
Log::error('Error occurred');           // Runtime error
Log::critical('Critical condition');    // Critical issue
Log::alert('Action required');          // Immediate action needed
Log::emergency('System unusable');      // System down
```

#### Logging with Context
```php
// Simple message
Log::info('User logged in');

// With context array
Log::info('User logged in', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent()
]);

// Exception logging
try {
    // code
} catch (\Exception $e) {
    Log::error('Login failed', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

#### View Logs
```powershell
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50

# Watch logs in real-time
Get-Content storage\logs\laravel.log -Wait -Tail 20

# Search logs
Select-String -Path "storage\logs\laravel.log" -Pattern "error"
```

---

## Xdebug Setup

### Step 1: Get Your PHP Configuration

Run this command and copy the **ENTIRE output**:

```powershell
php -i
```

**OR** save to file:

```powershell
php -i > php_info.txt
```

### Step 2: Identify Your PHP Build

Look for these specific lines in the output:

```
PHP Version => 8.3.26

System => Windows NT PC 10.0 build 19045 (Windows 10) AMD64
Architecture => x64
Thread Safety => disabled
Compiler => Visual C++ 2019

Zend Extension Build => API420230831,NTS,VS16
PHP Extension Build => API20230831,NTS,VS16
```

**Key Information Needed:**
1. **PHP Version:** 8.3.26
2. **Thread Safety:** disabled (NTS) or enabled (TS)
3. **Architecture:** x64 or x86
4. **Compiler:** VS16 (Visual C++ 2019)
5. **Zend Extension Build:** API420230831,NTS,VS16

### Step 3: Download Correct Xdebug DLL

#### Option A: Use Xdebug Wizard (Recommended)

1. Go to: https://xdebug.org/wizard
2. Paste your complete `php -i` output
3. Click "Analyse my phpinfo() output"
4. Download the recommended DLL file

#### Option B: Manual Download

Based on your build from Step 2:

**For this project (PHP 8.3, NTS, x64, VS16):**
- Visit: https://xdebug.org/download
- Download: `xdebug-3.3.2-8.3-vs16-nts-x86_64.dll`

**Naming convention explained:**
- `3.3.2` - Xdebug version
- `8.3` - PHP version
- `vs16` - Visual Studio 2019 (VC16)
- `nts` - Non Thread Safe
- `x86_64` - 64-bit

**Important:** Must match ALL parameters!

### Step 4: Install Xdebug DLL

1. **Rename the downloaded file:**
   ```
   xdebug-3.3.2-8.3-vs16-nts-x86_64.dll → php_xdebug.dll
   ```

2. **Move to PHP extensions folder:**
   ```
   C:\php-8.3.26\ext\php_xdebug.dll
   ```

3. **Verify file exists:**
   ```powershell
   Test-Path "C:\php-8.3.26\ext\php_xdebug.dll"
   # Should return: True
   ```

### Step 5: Configure php.ini

1. **Find your php.ini file:**
   ```powershell
   php --ini
   ```
   
   Output shows:
   ```
   Configuration File (php.ini) Path: C:\php-8.3.26
   Loaded Configuration File:         C:\php-8.3.26\php.ini
   ```

2. **Open php.ini in VS Code:**
   ```powershell
   code "C:\php-8.3.26\php.ini"
   ```

3. **Add Xdebug configuration at the END of php.ini:**

   ```ini
   [Xdebug]
   zend_extension="C:\php-8.3.26\ext\php_xdebug.dll"
   xdebug.mode=debug
   xdebug.start_with_request=yes
   xdebug.client_host=127.0.0.1
   xdebug.client_port=9003
   xdebug.log="C:\php-8.3.26\xdebug.log"
   xdebug.log_level=7
   ```

   **Configuration Explained:**
   - `zend_extension` - Path to Xdebug DLL (absolute path)
   - `xdebug.mode=debug` - Enable debugging mode
   - `xdebug.start_with_request=yes` - Start debugging on every request
   - `xdebug.client_host` - Where to connect (VS Code)
   - `xdebug.client_port` - Port for debugging (9003 is default)
   - `xdebug.log` - Log file for troubleshooting
   - `xdebug.log_level=7` - Verbose logging

4. **Save php.ini**

### Step 6: Verify Xdebug Installation

```powershell
# Check if Xdebug is loaded
php -v
```

**Expected output:**
```
PHP 8.3.26 (cli) (built: Nov 20 2024 19:47:33) (NTS Visual C++ 2019 x64)
Copyright (c) The PHP Group
Zend Engine v4.3.26, Copyright (c) Zend Technologies
    with Xdebug v3.3.2, Copyright (c) 2002-2024, by Derick Rethans
```

**Check Xdebug modules:**
```powershell
php -m | findstr -i xdebug
```

**Expected output:**
```
xdebug
Xdebug
```

**Check detailed Xdebug settings:**
```powershell
php -i | findstr -i xdebug
```

### Step 7: Install VS Code Extension

1. Open VS Code
2. Press `Ctrl+Shift+X` (Extensions)
3. Search: "PHP Debug"
4. Install: "PHP Debug" by Xdebug
5. Reload VS Code if prompted

### Step 8: Configure VS Code Launch Settings

**File:** `.vscode/launch.json` (Already created)

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "d:\\auth-app": "${workspaceFolder}"
            }
        },
        {
            "name": "Launch currently open script",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "port": 0,
            "runtimeArgs": [
                "-dxdebug.mode=debug",
                "-dxdebug.start_with_request=yes"
            ],
            "env": {
                "XDEBUG_MODE": "debug,develop",
                "XDEBUG_CONFIG": "client_port=9003"
            }
        }
    ]
}
```

**Path Mapping Explained:**
- Left side: Path Xdebug sees (Windows path)
- Right side: Path VS Code uses (workspace)
- Must match your actual paths!

### Step 9: Using Xdebug Debugging

#### A. Start Debugging Session

**Method 1: Using F5**
1. Open your project in VS Code
2. Press `F5`
3. Select "Listen for Xdebug"
4. Status bar shows: "Xdebug: Listening on port 9003"

**Method 2: Debug Panel**
1. Click "Run and Debug" icon (left sidebar)
2. Select "Listen for Xdebug" from dropdown
3. Click green play button

#### B. Set Breakpoints

**Simple Breakpoint:**
1. Open file: `app/Http/Controllers/Auth/ManualAuthController.php`
2. Click in the left gutter (line number area) at line 111
3. Red dot appears = breakpoint set

**Conditional Breakpoint:**
1. Right-click in gutter
2. Select "Add Conditional Breakpoint"
3. Enter condition: `$request->email == 'test@example.com'`

**Logpoint (doesn't stop execution):**
1. Right-click in gutter
2. Select "Add Logpoint"
3. Enter message: `Login attempt for {$request->email}`

#### C. Trigger Breakpoint

1. **Start Laravel server** (separate terminal):
   ```powershell
   php artisan serve
   ```

2. **Ensure Xdebug listener is running** (F5 in VS Code)

3. **Make request to your application:**
   - Visit http://127.0.0.1:8000/login
   - Fill form and submit
   - VS Code will pause at breakpoint

#### D. Debugging Controls

**When paused at breakpoint:**

| Shortcut | Action | Description |
|----------|--------|-------------|
| **F5** | Continue | Resume execution until next breakpoint |
| **F10** | Step Over | Execute current line, don't enter functions |
| **F11** | Step Into | Enter into function calls |
| **Shift+F11** | Step Out | Exit current function |
| **Ctrl+Shift+F5** | Restart | Restart debugging session |
| **Shift+F5** | Stop | Stop debugging |

**Debug Panels:**

1. **Variables**
   - Shows all variables in current scope
   - Expand arrays and objects
   - Hover over variables in code to see values

2. **Watch**
   - Add custom expressions to watch
   - Example: `$user->email`, `count($items)`

3. **Call Stack**
   - Shows function call hierarchy
   - Click to jump to different stack frames

4. **Breakpoints**
   - List all breakpoints
   - Enable/disable breakpoints
   - Remove breakpoints

#### E. Debug Console

**Evaluate expressions while debugging:**
```php
// In Debug Console, type:
$request->all()
$user->email
Hash::check('password', $user->password)
session()->all()
```

### Step 10: Debug Real-World Example

**Scenario: Debug Login Process**

1. **Set breakpoints:**
   ```
   Line 111: Start of login method
   Line 127: After finding user
   Line 132: Before password check
   Line 146: Before session creation
   ```

2. **Start debugging:**
   - Press F5 → "Listen for Xdebug"

3. **Trigger request:**
   - Visit http://127.0.0.1:8000/login
   - Enter email: test@example.com
   - Enter password: password123
   - Click "Login"

4. **VS Code pauses at line 111:**
   - **Variables panel** shows `$request` object
   - Expand `$request` → `parameters` → `request`
   - See: `email`, `password` values

5. **Press F10** (Step Over) multiple times:
   - Line 112-119: Validation
   - Line 127: `$user` variable appears
   - Hover over `$user` to see details

6. **Press F10** to reach line 132:
   - Check `$user` existence
   - In Debug Console type: `$user->email`
   - Shows: "test@example.com"

7. **Press F11** (Step Into) at password check:
   - Enters `verifyPassword()` method
   - See internal Hash::check() call

8. **Press F5** (Continue):
   - Execution resumes
   - Browser redirects to dashboard

### Step 11: Advanced Xdebug Features

#### A. Profiling (Performance Analysis)

Add to php.ini:
```ini
xdebug.mode=debug,profile
xdebug.output_dir="C:\php-8.3.26\xdebug_profiles"
xdebug.profiler_output_name=cachegrind.out.%t
```

View profiles with tools like:
- QCacheGrind
- KCacheGrind
- Webgrind

#### B. Code Coverage

```ini
xdebug.mode=debug,coverage
```

Use with PHPUnit:
```powershell
php artisan test --coverage
```

#### C. Tracing

```ini
xdebug.mode=debug,trace
xdebug.trace_output_dir="C:\php-8.3.26\xdebug_traces"
```

Generates execution traces for analysis.

---

## Production Logging

### Configuration for Production

#### 1. Update .env

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=daily
LOG_LEVEL=error
```

**Explanation:**
- `APP_DEBUG=false` - Don't show errors to users
- `LOG_CHANNEL=daily` - New log file each day
- `LOG_LEVEL=error` - Only log errors and above

#### 2. Configure Log Channels

**File:** `config/logging.php`

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'error'),
        'days' => 14, // Keep 14 days of logs
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

### Production Logging Best Practices

#### 1. Structured Logging

```php
// Good - Structured with context
Log::error('Payment failed', [
    'user_id' => $user->id,
    'order_id' => $order->id,
    'amount' => $amount,
    'gateway' => 'stripe',
    'error_code' => $e->getCode(),
    'timestamp' => now()
]);

// Bad - Unstructured message
Log::error('Payment failed for user ' . $user->id);
```

#### 2. Security Logging

```php
// Log security events
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'timestamp' => now()
]);

Log::alert('Multiple failed login attempts', [
    'email' => $email,
    'attempts' => $attempts,
    'ip' => $ip,
    'timeframe' => '5 minutes'
]);
```

#### 3. Performance Logging

```php
// Log slow queries
DB::listen(function ($query) {
    if ($query->time > 1000) { // > 1 second
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time . 'ms'
        ]);
    }
});

// Log slow requests
$start = microtime(true);
// ... code execution ...
$duration = (microtime(true) - $start) * 1000;

if ($duration > 2000) { // > 2 seconds
    Log::warning('Slow request', [
        'url' => request()->fullUrl(),
        'method' => request()->method(),
        'duration' => $duration . 'ms'
    ]);
}
```

#### 4. Never Log Sensitive Data

```php
// NEVER do this
Log::info('User login', [
    'password' => $request->password, // ❌ NEVER!
    'credit_card' => $payment->card,  // ❌ NEVER!
    'api_key' => $config->key,        // ❌ NEVER!
]);

// Instead
Log::info('User login', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'has_password' => !empty($request->password) // ✅ Good
]);
```

### Log Monitoring

#### 1. Real-time Monitoring

**PowerShell (Windows):**
```powershell
# Watch logs
Get-Content storage\logs\laravel-*.log -Wait -Tail 50

# Filter errors only
Get-Content storage\logs\laravel-*.log -Wait | Select-String "ERROR"
```

#### 2. Log Rotation Script

**Create:** `scripts/rotate_logs.ps1`

```powershell
# Rotate old logs
$logPath = "storage\logs"
$archivePath = "storage\logs\archive"
$daysToKeep = 14

# Create archive directory
New-Item -ItemType Directory -Force -Path $archivePath

# Move old logs
Get-ChildItem $logPath -Filter "laravel-*.log" | 
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$daysToKeep) } |
    Move-Item -Destination $archivePath

# Compress archived logs
Compress-Archive -Path "$archivePath\*" -DestinationPath "$archivePath\logs-$(Get-Date -Format 'yyyy-MM').zip" -Update
```

#### 3. Log Analysis

**Search for errors:**
```powershell
# Find all errors
Select-String -Path "storage\logs\*.log" -Pattern "\[error\]"

# Count errors by type
Select-String -Path "storage\logs\*.log" -Pattern "\[error\]" | 
    Group-Object Line | 
    Sort-Object Count -Descending
```

### Third-Party Services

#### 1. Sentry (Error Tracking)

```bash
composer require sentry/sentry-laravel
```

**.env:**
```env
SENTRY_LARAVEL_DSN=https://your-key@sentry.io/your-project
```

#### 2. Papertrail (Cloud Logging)

**config/logging.php:**
```php
'papertrail' => [
    'driver' => 'monolog',
    'level' => 'debug',
    'handler' => SyslogUdpHandler::class,
    'handler_with' => [
        'host' => env('PAPERTRAIL_URL'),
        'port' => env('PAPERTRAIL_PORT'),
    ],
],
```

#### 3. LogRocket (Session Replay)

```bash
npm install --save logrocket
```

Track user sessions with errors.

---

## Best Practices

### Development

1. **Use appropriate tools for the job:**
   - Quick checks → `dd()`, `dump()`
   - Visual debugging → Laravel Debugbar
   - Step-by-step → Xdebug
   - Logging → `Log` facade

2. **Remove debug code before commit:**
   ```php
   // ❌ Don't commit
   dd($request);
   var_dump($data);
   
   // ✅ Commit (proper logging)
   Log::debug('Request received', $request->all());
   ```

3. **Use meaningful breakpoints:**
   ```php
   // ❌ Too many
   // Breakpoint on every line
   
   // ✅ Strategic
   // Breakpoint at: validation, database query, business logic
   ```

### Production

1. **Log levels hierarchy:**
   ```
   DEBUG → INFO → NOTICE → WARNING → ERROR → CRITICAL → ALERT → EMERGENCY
   ```
   
   Production: Only ERROR and above

2. **Include context always:**
   ```php
   Log::error('Database query failed', [
       'query' => $query,
       'user_id' => auth()->id(),
       'route' => request()->route()->getName(),
       'timestamp' => now()
   ]);
   ```

3. **Monitor logs actively:**
   - Set up alerts for CRITICAL/EMERGENCY
   - Daily review of ERRORs
   - Weekly analysis of WARNINGs

4. **Rotate logs regularly:**
   - Daily rotation (Laravel default)
   - Keep 14-30 days
   - Archive older logs

5. **Secure log files:**
   ```bash
   # Linux permissions
   chmod 640 storage/logs/*.log
   
   # Windows: Restrict access to administrators only
   ```

---

## Troubleshooting

### Xdebug Not Working

#### Problem: Xdebug not showing in php -v

**Solution:**
```powershell
# Check if DLL exists
Test-Path "C:\php-8.3.26\ext\php_xdebug.dll"

# Check php.ini path
php --ini

# Verify extension line
Select-String -Path "C:\php-8.3.26\php.ini" -Pattern "xdebug"

# Check for syntax errors in php.ini
php -c "C:\php-8.3.26\php.ini" -v
```

#### Problem: Breakpoints not hitting

**Checklist:**
1. ✅ Xdebug listener running (F5 in VS Code)
2. ✅ Server running (`php artisan serve`)
3. ✅ Path mapping correct in launch.json
4. ✅ Port 9003 not blocked by firewall
5. ✅ xdebug.start_with_request=yes in php.ini

**Test:**
```php
// Add to controller
xdebug_break(); // Force break
```

#### Problem: Firewall blocking

```powershell
# Allow port 9003
New-NetFirewallRule -DisplayName "Xdebug" -Direction Inbound -LocalPort 9003 -Protocol TCP -Action Allow
```

### Laravel Debugbar Not Showing

**Solutions:**
```powershell
# Clear config
php artisan config:clear

# Publish config
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"

# Check .env
APP_DEBUG=true
DEBUGBAR_ENABLED=true
```

### Logs Not Writing

**Solutions:**
```powershell
# Check permissions
icacls storage\logs

# Create logs directory
New-Item -ItemType Directory -Force -Path storage\logs

# Give write permissions
icacls storage\logs /grant Everyone:F

# Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## Quick Reference

### Debug Commands

```powershell
# Development
dd($var)                          # Dump and die
dump($var)                        # Dump and continue
logger('message', ['context'])    # Quick log

# Xdebug
php -v                            # Check if loaded
php -m | findstr xdebug          # Check module
php -i | findstr xdebug          # Detailed info

# Logs
Get-Content storage\logs\laravel.log -Tail 50
Get-Content storage\logs\laravel.log -Wait
Select-String -Path "storage\logs\*.log" -Pattern "error"

# Laravel
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### VS Code Shortcuts

| Shortcut | Action |
|----------|--------|
| **F5** | Start/Continue debugging |
| **F9** | Toggle breakpoint |
| **F10** | Step over |
| **F11** | Step into |
| **Shift+F11** | Step out |
| **Ctrl+Shift+F5** | Restart debugging |
| **Shift+F5** | Stop debugging |

### Log Levels

```php
Log::emergency()  // System unusable
Log::alert()      // Action required immediately
Log::critical()   // Critical conditions
Log::error()      // Runtime errors
Log::warning()    // Warning messages
Log::notice()     // Normal but significant
Log::info()       // Informational
Log::debug()      // Debug-level messages
```

---

## Summary

✅ **Development:**
- Use `dd()`/`dump()` for quick checks
- Laravel Debugbar for visual debugging
- Xdebug for step-by-step debugging
- Log::debug() for development logging

✅ **Production:**
- Log::error() and above only
- Structured logging with context
- Daily log rotation
- Monitor logs actively
- Use third-party services for alerts

✅ **Tools:**
- VS Code + PHP Debug extension
- Xdebug 3.3.x
- Laravel Debugbar
- PowerShell for log monitoring

---

**Complete debugging setup achieved!** You now have professional-grade debugging and logging for both development and production environments.
