# Phase 2: Laravel Breeze with Bootstrap - Part 6: Profile Management

## Table of Contents
1. [Understanding Profile Management](#understanding-profile-management)
2. [Converting Profile Edit Page](#converting-profile-edit-page)
3. [Update Profile Information Form](#update-profile-information-form)
4. [Update Password Form](#update-password-form)
5. [Delete Account Form](#delete-account-form)
6. [Profile Controller Customization](#profile-controller-customization)
7. [Testing Profile Features](#testing-profile-features)

---

## Understanding Profile Management

### Profile Features Overview

```
Breeze Profile Management:
├── Update Profile Information
│   ├── Change name
│   ├── Change email
│   └── Email verification status
├── Update Password
│   ├── Current password verification
│   ├── New password
│   └── Password confirmation
└── Delete Account
    ├── Password confirmation required
    ├── Irreversible action warning
    └── Complete data deletion
```

### File Structure

```
resources/views/profile/
├── edit.blade.php                              # Main profile page
└── partials/
    ├── update-profile-information-form.blade.php  # Name/email update
    ├── update-password-form.blade.php             # Password change
    └── delete-user-form.blade.php                 # Account deletion
```

---

## Converting Profile Edit Page

### Original Profile Edit (Tailwind)

**File:** `resources/views/profile/edit.blade.php`

### Bootstrap Converted Profile Edit

**Replace entire content:**

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0 fw-bold text-dark">
            <i class="bi bi-person-circle me-2"></i>Profile Settings
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="row g-4">
            <!-- Update Profile Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person me-2"></i>Profile Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Account Stats (Optional) -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>Account Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Member since:</strong><br>
                                <small class="text-muted">
                                    {{ Auth::user()->created_at->format('F d, Y') }}
                                </small>
                            </li>
                            <li class="mb-2">
                                <strong>Last updated:</strong><br>
                                <small class="text-muted">
                                    {{ Auth::user()->updated_at->diffForHumans() }}
                                </small>
                            </li>
                            <li class="mb-2">
                                <strong>Email status:</strong><br>
                                @if (Auth::user()->hasVerifiedEmail())
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Unverified
                                    </span>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Password -->
        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-lock me-2"></i>Update Password
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
                        </h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

**Key features:**
- ✅ Three separate cards for each section
- ✅ Account details sidebar
- ✅ Icons for each section
- ✅ Danger zone styling for delete
- ✅ Responsive layout
- ✅ Member since and last updated info

---

## Update Profile Information Form

### Original Form (Tailwind)

**File:** `resources/views/profile/partials/update-profile-information-form.blade.php`

### Bootstrap Converted Form

**Replace entire content:**

```blade
<section>
    <header class="mb-4">
        <h6 class="fw-bold">Profile Information</h6>
        <p class="text-muted small mb-0">
            Update your account's profile information and email address.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}" 
                       required 
                       autofocus 
                       autocomplete="name">
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-envelope"></i>
                </span>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $user->email) }}" 
                       required 
                       autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-2 mb-0" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Your email address is unverified.
                    
                    <button form="send-verification" class="btn btn-link p-0 ms-2 text-decoration-none">
                        Click here to re-send the verification email.
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-2 mb-0" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        A new verification link has been sent to your email address.
                    </div>
                @endif
            @endif
        </div>

        <!-- Save Button -->
        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small" id="saved-message">
                    <i class="bi bi-check-circle me-1"></i>Saved successfully!
                </span>
            @endif
        </div>
    </form>
</section>

@if (session('status') === 'profile-updated')
    @push('scripts')
    <script>
        setTimeout(() => {
            const message = document.getElementById('saved-message');
            if (message) {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }
        }, 3000);
    </script>
    @endpush
@endif
```

**Key features:**
- ✅ Name and email fields with icons
- ✅ Email verification status alert
- ✅ Resend verification button
- ✅ Success message that fades
- ✅ Validation error handling
- ✅ Auto-fade success message after 3 seconds

---

## Update Password Form

### Original Form (Tailwind)

**File:** `resources/views/profile/partials/update-password-form.blade.php`

### Bootstrap Converted Form

**Replace entire content:**

```blade
<section>
    <header class="mb-4">
        <h6 class="fw-bold">Update Password</h6>
        <p class="text-muted small mb-0">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">
                Current Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" 
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                       id="update_password_current_password" 
                       name="current_password" 
                       autocomplete="current-password"
                       placeholder="Enter current password">
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="update_password_password" class="form-label">
                New Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-key"></i>
                </span>
                <input type="password" 
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                       id="update_password_password" 
                       name="password" 
                       autocomplete="new-password"
                       placeholder="Enter new password">
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="form-text">
                <small>Minimum 8 characters, mix of letters and numbers recommended</small>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">
                Confirm New Password
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-key-fill"></i>
                </span>
                <input type="password" 
                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                       id="update_password_password_confirmation" 
                       name="password_confirmation" 
                       autocomplete="new-password"
                       placeholder="Re-enter new password">
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <!-- Save Button -->
        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-check me-2"></i>Update Password
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small" id="password-saved-message">
                    <i class="bi bi-check-circle me-1"></i>Password updated successfully!
                </span>
            @endif
        </div>
    </form>
</section>

@if (session('status') === 'password-updated')
    @push('scripts')
    <script>
        setTimeout(() => {
            const message = document.getElementById('password-saved-message');
            if (message) {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }
        }, 3000);
    </script>
    @endpush
@endif
```

**Key features:**
- ✅ Three password fields (current, new, confirm)
- ✅ Different icons for each field
- ✅ Password strength hint
- ✅ Validation with error bag 'updatePassword'
- ✅ Success message that fades
- ✅ Secure autocomplete attributes

---

## Delete Account Form

### Original Form (Tailwind)

**File:** `resources/views/profile/partials/delete-user-form.blade.php`

### Bootstrap Converted Form

**Replace entire content:**

```blade
<section>
    <header class="mb-4">
        <h6 class="fw-bold text-danger">Delete Account</h6>
        <p class="text-muted small mb-0">
            Once your account is deleted, all of its resources and data will be permanently deleted. 
            Before deleting your account, please download any data or information that you wish to retain.
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        <i class="bi bi-trash me-2"></i>Delete Account
    </button>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmUserDeletionModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-octagon me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>

                    <p>
                        Are you sure you want to delete your account? Once your account is deleted, 
                        all of its resources and data will be permanently deleted.
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" id="delete-user-form">
                        @csrf
                        @method('delete')

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Please enter your password to confirm:
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password"
                                       required>
                                @error('password', 'userDeletion')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" form="delete-user-form" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Delete Account Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

@error('password', 'userDeletion')
    @push('scripts')
    <script>
        // Reopen modal if validation failed
        var deleteModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
        deleteModal.show();
    </script>
    @endpush
@enderror
```

**Key features:**
- ✅ Warning message about data loss
- ✅ Bootstrap modal for confirmation
- ✅ Password required to delete
- ✅ Danger styling (red theme)
- ✅ Modal reopens if validation fails
- ✅ Clear cancel option

---

## Profile Controller Customization

### Understanding ProfileController

**File:** `app/Http/Controllers/ProfileController.php`

**Current methods:**
```php
public function edit(Request $request): View
{
    return view('profile.edit', [
        'user' => $request->user(),
    ]);
}

public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $request->user()->fill($request->validated());

    if ($request->user()->isDirty('email')) {
        $request->user()->email_verified_at = null;
    }

    $request->user()->save();

    return Redirect::route('profile.edit')->with('status', 'profile-updated');
}

public function destroy(Request $request): RedirectResponse
{
    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'current_password'],
    ]);

    $user = $request->user();

    Auth::logout();

    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return Redirect::to('/');
}
```

### Adding Avatar Upload (Optional Enhancement)

**Step 1: Add migration for avatar column**

```powershell
php artisan make:migration add_avatar_to_users_table
```

**Migration file:**
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('avatar')->nullable()->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('avatar');
    });
}
```

**Run migration:**
```powershell
php artisan migrate
```

**Step 2: Update User model**

**File:** `app/Models/User.php`

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'avatar', // Add this
];
```

**Step 3: Add avatar upload form**

**Create:** `resources/views/profile/partials/update-avatar-form.blade.php`

```blade
<section>
    <header class="mb-4">
        <h6 class="fw-bold">Profile Picture</h6>
        <p class="text-muted small mb-0">
            Upload a profile picture to personalize your account.
        </p>
    </header>

    <div class="d-flex align-items-center mb-3">
        @if (Auth::user()->avatar)
            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                 alt="Avatar" 
                 class="rounded-circle me-3" 
                 style="width: 80px; height: 80px; object-fit: cover;">
        @else
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                 style="width: 80px; height: 80px; font-size: 2rem;">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="mb-1 fw-bold">{{ Auth::user()->name }}</p>
            <p class="text-muted small mb-0">{{ Auth::user()->email }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="avatar" class="form-label">Choose Image</label>
            <input type="file" 
                   class="form-control @error('avatar') is-invalid @enderror" 
                   id="avatar" 
                   name="avatar" 
                   accept="image/*">
            @error('avatar')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
            <div class="form-text">
                <small>JPG, PNG or GIF. Max size 2MB.</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-upload me-2"></i>Upload
            </button>
            
            @if (Auth::user()->avatar)
                <form method="post" action="{{ route('profile.avatar.delete') }}" class="d-inline">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash me-2"></i>Remove
                    </button>
                </form>
            @endif
        </div>
    </form>
</section>
```

**Step 4: Add avatar routes**

**File:** `routes/web.php`

```php
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Avatar routes
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
});
```

**Step 5: Add controller methods**

**File:** `app/Http/Controllers/ProfileController.php`

```php
public function updateAvatar(Request $request): RedirectResponse
{
    $request->validate([
        'avatar' => ['required', 'image', 'max:2048'], // 2MB max
    ]);

    // Delete old avatar
    if ($request->user()->avatar) {
        Storage::disk('public')->delete($request->user()->avatar);
    }

    // Store new avatar
    $path = $request->file('avatar')->store('avatars', 'public');
    
    $request->user()->update([
        'avatar' => $path,
    ]);

    return Redirect::route('profile.edit')->with('status', 'avatar-updated');
}

public function deleteAvatar(Request $request): RedirectResponse
{
    if ($request->user()->avatar) {
        Storage::disk('public')->delete($request->user()->avatar);
        
        $request->user()->update([
            'avatar' => null,
        ]);
    }

    return Redirect::route('profile.edit')->with('status', 'avatar-deleted');
}
```

**Step 6: Create storage link**

```powershell
php artisan storage:link
```

---

## Testing Profile Features

### Test 1: Update Profile Information

**Login and visit:** http://127.0.0.1:8000/profile

**Test name update:**
1. Change name to "Updated Name"
2. Click "Save Changes"
3. Should show success message
4. Page reloads with new name
5. Success message fades after 3 seconds

**Test email update:**
1. Change email to new address
2. Click "Save Changes"
3. Should show unverified warning
4. Can resend verification
5. Email verification status resets

### Test 2: Update Password

**Test validation:**
1. Leave all fields empty, submit
2. Should show "current password required"
3. Enter wrong current password
4. Should show "current password incorrect"
5. Enter new password without confirmation
6. Should show "confirmation doesn't match"

**Test successful update:**
1. Enter correct current password
2. Enter new password: newpassword123
3. Confirm new password: newpassword123
4. Click "Update Password"
5. Should show success message
6. Logout and login with new password

### Test 3: Delete Account

**Test modal:**
1. Click "Delete Account" button
2. Modal opens with warning
3. Click "Cancel"
4. Modal closes, nothing happens

**Test validation:**
1. Open modal again
2. Leave password empty, submit
3. Should show validation error
4. Modal stays open with error

**Test successful deletion:**
1. Create test user
2. Login as test user
3. Go to profile
4. Click "Delete Account"
5. Enter password
6. Click "Delete Account Permanently"
7. Should logout
8. Redirect to home
9. Cannot login with deleted account

### Test 4: Avatar Upload (If Implemented)

**Test upload:**
1. Click "Choose Image"
2. Select image file
3. Click "Upload"
4. Avatar displays
5. Old default letter avatar replaced

**Test remove:**
1. Click "Remove" button
2. Avatar deleted
3. Default letter avatar shows again

**Test validation:**
1. Try uploading PDF
2. Should show "must be image"
3. Try large file (>2MB)
4. Should show "file too large"

### Test 5: Responsive Design

**Desktop:**
- Profile form in main column
- Account details in sidebar
- All cards visible

**Mobile:**
- Cards stack vertically
- Sidebar moves below
- Forms full width

---

## Next Steps

✅ **Completed:**
- Profile edit page converted
- Update profile information form
- Update password form
- Delete account form
- Optional avatar upload
- All features tested

📝 **Next Document:**
[PHASE2_07_TESTING_VALIDATION.md](PHASE2_07_TESTING_VALIDATION.md)

**You will learn:**
- Complete testing checklist
- Validation testing
- Security testing
- Cross-browser testing
- Mobile testing
- Performance testing

---

## Quick Reference

### Profile Routes

```php
GET     /profile          # Show profile page
PATCH   /profile          # Update profile info
PUT     /password         # Update password
DELETE  /profile          # Delete account
```

### Session Status Values

```php
'profile-updated'     // Profile info updated
'password-updated'    // Password changed
'avatar-updated'      // Avatar uploaded
'avatar-deleted'      // Avatar removed
```

### Validation Error Bags

```php
@error('field', 'updatePassword')    // Password update errors
@error('field', 'userDeletion')      // Account deletion errors
@error('field')                      // Profile update errors
```

### Testing User Creation

```powershell
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password123'),
]);
```

---

**Profile management complete!** Ready for comprehensive testing.
