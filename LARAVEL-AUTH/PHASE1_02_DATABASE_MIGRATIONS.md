# Phase 1: Manual Authentication - Part 2: Database & Migrations

## Table of Contents
1. [Understanding Migrations](#understanding-migrations)
2. [Users Table Structure](#users-table-structure)
3. [Creating Migration](#creating-migration)
4. [User Model Setup](#user-model-setup)
5. [Running Migrations](#running-migrations)
6. [Testing Database](#testing-database)

---

## Understanding Migrations

### What Are Migrations?

**Migrations = Version Control for Your Database**

Think of migrations as Git commits for your database schema.

**Benefits:**
- ✅ Track database changes
- ✅ Share schema with team
- ✅ Rollback changes easily
- ✅ Consistent across environments
- ✅ No manual SQL needed

### Migration Lifecycle

```
1. Create Migration
   └─> php artisan make:migration create_users_table

2. Write Schema
   └─> Define columns in up() method

3. Run Migration
   └─> php artisan migrate
   └─> Creates table in database

4. Rollback (if needed)
   └─> php artisan migrate:rollback
   └─> Runs down() method to undo
```

### Migration File Anatomy

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This executes when you run: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Define columns here
        });
    }

    /**
     * Reverse the migrations.
     * This executes when you run: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

**Methods Explained:**
- `up()` - Creates table/columns (forward migration)
- `down()` - Removes table/columns (backward migration)
- `Schema::create()` - Creates new table
- `Blueprint $table` - Table blueprint for defining columns

---

## Users Table Structure

### Required Columns

| Column | Type | Length | Nullable | Default | Purpose |
|--------|------|--------|----------|---------|---------|
| `id` | BIGINT | - | NO | AUTO | Primary key (auto-increment) |
| `name` | VARCHAR | 255 | NO | - | User's full name |
| `email` | VARCHAR | 255 | NO | - | Email (unique, for login) |
| `password` | VARCHAR | 255 | NO | - | Hashed password (bcrypt) |
| `created_at` | TIMESTAMP | - | YES | NULL | Record creation time |
| `updated_at` | TIMESTAMP | - | YES | NULL | Last update time |

### Why These Columns?

**1. id (Primary Key):**
```php
$table->id();
```
- Unique identifier for each user
- Auto-increments (1, 2, 3...)
- Used for relationships
- Indexed automatically (fast lookups)

**2. name:**
```php
$table->string('name');
```
- User's full name
- VARCHAR(255) in MySQL
- Required for registration
- Display in UI

**3. email (Unique Index):**
```php
$table->string('email')->unique();
```
- Login credential
- Must be unique (one account per email)
- Used for password resets
- Indexed for fast authentication

**4. password:**
```php
$table->string('password');
```
- Stores hashed password (NOT plain text!)
- bcrypt produces 60-character hash
- VARCHAR(255) for future-proofing
- Never display or log this!

**5. created_at & updated_at (Timestamps):**
```php
$table->timestamps();
```
- Automatically managed by Laravel
- `created_at` - Never changes
- `updated_at` - Updates on every save()
- Useful for auditing

### Optional Columns (Not in Phase 1)

These we'll add in later phases:

```php
// Email verification (Phase 3)
$table->timestamp('email_verified_at')->nullable();

// Remember me token (Phase 2 - Breeze)
$table->rememberToken();

// Profile picture
$table->string('avatar')->nullable();

// Account status
$table->boolean('is_active')->default(true);

// Role (Phase 4 - Authorization)
$table->string('role')->default('user');
```

---

## Creating Migration

### Step 1: Check Existing Migration

Laravel includes a users migration by default:

```powershell
# List migration files
Get-ChildItem database\migrations
```

**You should see:**
```
2014_10_12_000000_create_users_table.php
2014_10_12_100000_create_password_reset_tokens_table.php
2019_08_19_000000_create_failed_jobs_table.php
2019_12_14_000001_create_personal_access_tokens_table.php
```

### Step 2: Examine Default Users Migration

Open file: `database/migrations/2014_10_12_000000_create_users_table.php`

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### Step 3: Simplify for Phase 1

**Current migration includes:**
- ✅ `id` - Keep
- ✅ `name` - Keep
- ✅ `email` - Keep
- ❌ `email_verified_at` - Remove (Phase 3 feature)
- ✅ `password` - Keep
- ❌ `rememberToken` - Remove (Phase 2 feature)
- ✅ `timestamps` - Keep

**Modified migration for Phase 1:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates users table for manual authentication
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key - Auto-incrementing ID
            $table->id();
            
            // User information
            $table->string('name'); // Full name
            $table->string('email')->unique(); // Email (must be unique)
            $table->string('password'); // Hashed password
            
            // Timestamps - created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Drops users table
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

**No changes needed!** Laravel's default is perfect for Phase 1 (we'll just ignore extra columns).

### Understanding Column Methods

```php
// Common column types
$table->id();                          // BIGINT UNSIGNED AUTO_INCREMENT
$table->string('name');                // VARCHAR(255)
$table->string('email', 100);          // VARCHAR(100) - custom length
$table->text('bio');                   // TEXT - long text
$table->integer('age');                // INTEGER
$table->boolean('is_active');          // BOOLEAN (TINYINT(1))
$table->decimal('price', 8, 2);        // DECIMAL(8,2) - for money
$table->date('birth_date');            // DATE
$table->datetime('published_at');      // DATETIME
$table->timestamp('verified_at');      // TIMESTAMP
$table->json('metadata');              // JSON column

// Column modifiers
->nullable();                          // Allow NULL values
->default('value');                    // Default value
->unique();                            // Unique constraint
->unsigned();                          // Unsigned (positive only)
->after('column_name');                // Position after column
->comment('Description');              // Column comment

// Special methods
$table->timestamps();                  // created_at + updated_at
$table->softDeletes();                 // deleted_at (soft delete)
$table->rememberToken();               // remember_token (60 chars)
```

---

## User Model Setup

### Step 1: Understand Models

**Models = PHP Classes that Represent Database Tables**

```
User Model (app/Models/User.php)
    ↕
users table (database)
```

**Model allows you to:**
- Query database: `User::where('email', 'test@example.com')->first()`
- Create records: `User::create(['name' => 'John', ...])`
- Update records: `$user->update(['name' => 'Jane'])`
- Delete records: `$user->delete()`

### Step 2: Examine Default User Model

Open: `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Step 3: Simplify User Model for Phase 1

**For manual authentication, we need a basic Model, not Authenticatable:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Laravel assumes 'users' by default
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * These can be set via User::create() or $user->fill()
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays/JSON.
     * Never expose password in API responses
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Automatically manages created_at and updated_at
     */
    public $timestamps = true;
}
```

**Key Properties Explained:**

**1. $fillable (Mass Assignment Protection):**
```php
protected $fillable = ['name', 'email', 'password'];

// This is now ALLOWED:
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => Hash::make('secret')
]);

// This would be BLOCKED (not in fillable):
User::create([
    'is_admin' => true  // ❌ Not fillable - security protection
]);
```

**2. $hidden (Hide Sensitive Data):**
```php
protected $hidden = ['password'];

// When converting to JSON/array:
$user = User::find(1);
return $user->toArray();

// Output:
[
    'id' => 1,
    'name' => 'John',
    'email' => 'john@example.com'
    // password is hidden!
]
```

**3. $timestamps (Automatic Timestamps):**
```php
public $timestamps = true;

// When you create/update:
$user = User::create([...]);
// created_at is set automatically
// updated_at is set automatically

$user->name = 'New Name';
$user->save();
// updated_at is updated automatically
```

### Step 4: Alternative - Keep Authenticatable

**Actually, let's KEEP the original model!**

Even for manual auth, having `Authenticatable` base class is useful:

```php
class User extends Authenticatable  // Keep this
```

**Why?**
- Provides password hashing methods
- Compatible with Laravel's Auth facade (for later)
- Easier transition to Phase 2 (Breeze)
- No downside to having it

**Final User Model (No changes needed):**

The default `app/Models/User.php` is perfect! Just understand what it does:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

**Understanding `casts()`:**
```php
'password' => 'hashed'

// When you set password:
$user->password = 'plain-text-password';
$user->save();
// Laravel automatically hashes it!

// No need to manually do:
$user->password = Hash::make('password'); // Not needed anymore!
```

---

## Running Migrations

### Step 1: Check Migration Status

```powershell
php artisan migrate:status
```

**Output shows:**
```
Migration name .......................... Ran?
2014_10_12_000000_create_users_table .... Pending
```

### Step 2: Run Migrations

```powershell
php artisan migrate
```

**Expected Output:**
```
   INFO  Running migrations.

  2014_10_12_000000_create_users_table ....................... 45ms DONE
  2014_10_12_100000_create_password_reset_tokens_table ....... 28ms DONE
  2019_08_19_000000_create_failed_jobs_table ................. 31ms DONE
  2019_12_14_000001_create_personal_access_tokens_table ...... 42ms DONE
```

**What Happened:**
1. Created `migrations` table (tracks which migrations ran)
2. Created `users` table with our schema
3. Created other tables (password resets, failed jobs, etc.)

### Step 3: Verify Tables in Database

**Option A: MySQL Command Line**
```powershell
mysql -u root -p laravel_auth_demo
```

```sql
SHOW TABLES;
```

**Expected:**
```
+-------------------------------+
| Tables_in_laravel_auth_demo   |
+-------------------------------+
| failed_jobs                   |
| migrations                    |
| password_reset_tokens         |
| personal_access_tokens        |
| users                         |
+-------------------------------+
```

**Check users table structure:**
```sql
DESCRIBE users;
```

**Expected:**
```
+-------------------+-----------------+------+-----+---------+----------------+
| Field             | Type            | Null | Key | Default | Extra          |
+-------------------+-----------------+------+-----+---------+----------------+
| id                | bigint unsigned | NO   | PRI | NULL    | auto_increment |
| name              | varchar(255)    | NO   |     | NULL    |                |
| email             | varchar(255)    | NO   | UNI | NULL    |                |
| email_verified_at | timestamp       | YES  |     | NULL    |                |
| password          | varchar(255)    | NO   |     | NULL    |                |
| remember_token    | varchar(100)    | YES  |     | NULL    |                |
| created_at        | timestamp       | YES  |     | NULL    |                |
| updated_at        | timestamp       | YES  |     | NULL    |                |
+-------------------+-----------------+------+-----+---------+----------------+
```

**Perfect!** Our users table is ready.

### Step 4: Migration Commands Reference

```powershell
# Run all pending migrations
php artisan migrate

# Check migration status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Rollback and re-run all migrations
php artisan migrate:refresh

# Drop all tables and re-run all migrations
php artisan migrate:fresh

# Run specific migration
php artisan migrate --path=/database/migrations/2014_10_12_000000_create_users_table.php
```

---

## Testing Database

### Step 1: Test User Creation via Tinker

Laravel Tinker = Interactive PHP console

```powershell
php artisan tinker
```

**Create a test user:**
```php
$user = new App\Models\User;
$user->name = 'Test User';
$user->email = 'test@example.com';
$user->password = Hash::make('password123');
$user->save();
```

**Expected Output:**
```
=> true
```

**Verify user exists:**
```php
App\Models\User::all();
```

**Output:**
```
Illuminate\Database\Eloquent\Collection {
  all: [
    App\Models\User {
      id: 1,
      name: "Test User",
      email: "test@example.com",
      password: "$2y$12$...",
      created_at: "2025-12-15 10:30:00",
      updated_at: "2025-12-15 10:30:00",
    },
  ],
}
```

**Exit Tinker:**
```php
exit
```

### Step 2: Test Mass Assignment

```powershell
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => Hash::make('secret123')
]);

$user->id; // Shows created user ID
```

### Step 3: Test Querying

```php
// Find by ID
$user = App\Models\User::find(1);

// Find by email
$user = App\Models\User::where('email', 'test@example.com')->first();

// Get all users
$users = App\Models\User::all();

// Count users
App\Models\User::count();
```

### Step 4: Test Password Hashing

```php
// Create user with plain password
$user = App\Models\User::create([
    'name' => 'Password Test',
    'email' => 'pass@test.com',
    'password' => 'plain-password' // Will be auto-hashed!
]);

// Check password is hashed
$user->password;
// Shows: "$2y$12$..." (bcrypt hash)

// Verify password
Hash::check('plain-password', $user->password);
// Returns: true

Hash::check('wrong-password', $user->password);
// Returns: false
```

### Step 5: Clean Up Test Data

```powershell
# Exit Tinker
exit

# Reset database
php artisan migrate:fresh
```

This drops all tables and re-runs migrations (clean slate).

---

## Common Migration Issues

### Issue 1: Migration Already Ran

**Error:**
```
Migration name already exists.
```

**Solution:**
```powershell
php artisan migrate:fresh
```

### Issue 2: Syntax Error in Migration

**Error:**
```
syntax error, unexpected token "function"
```

**Solution:**
- Check PHP version (need PHP 8.1+)
- Verify migration file syntax
- Look for missing semicolons or commas

### Issue 3: Table Already Exists

**Error:**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solution:**
```powershell
php artisan migrate:fresh
```

Or manually drop tables:
```sql
DROP TABLE users;
php artisan migrate
```

### Issue 4: Column Too Long

**Error:**
```
SQLSTATE[42000]: Syntax error: 1071 Specified key was too long
```

**Solution - Update AppServiceProvider:**

File: `app/Providers/AppServiceProvider.php`

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

This fixes old MySQL version (< 5.7.7) compatibility.

---

## Next Steps

✅ **Completed:**
- Understanding migrations
- Users table created
- User model configured
- Database tested

📝 **Next Document:**
[PHASE1_03_CONTROLLERS.md](PHASE1_03_CONTROLLERS.md)

**You will learn:**
- Controller creation
- Registration logic
- Login logic
- Password hashing
- Session management
- Validation

---

## Quick Reference

### Migration Commands

```powershell
php artisan make:migration create_users_table    # Create migration
php artisan migrate                               # Run migrations
php artisan migrate:status                        # Check status
php artisan migrate:rollback                      # Undo last batch
php artisan migrate:fresh                         # Fresh start
```

### Tinker Commands

```powershell
php artisan tinker                                # Start Tinker

# Inside Tinker:
App\Models\User::all()                           # Get all users
App\Models\User::find(1)                         # Find by ID
App\Models\User::where('email', '...')->first()  # Find by email
App\Models\User::count()                         # Count users
exit                                             # Exit Tinker
```

### Common Column Types

```php
$table->id();                   // Auto-increment ID
$table->string('name');         // VARCHAR(255)
$table->string('email', 100);   // VARCHAR(100)
$table->text('bio');            // TEXT
$table->integer('age');         // INTEGER
$table->boolean('active');      // BOOLEAN
$table->timestamps();           // created_at, updated_at
```

---

**Database Ready!** Proceed to Part 3 for controller implementation.
