<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthService
 * 
 * Service class for handling authentication business logic.
 * This layer sits between controllers and repositories,
 * containing all business logic for authentication operations.
 * 
 * @package App\Services
 */
class AuthService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * AuthService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function register(array $data): array
    {
        // Check if email already exists
        if ($this->userRepository->emailExists($data['email'])) {
            throw new \Exception('Email already registered');
        }

        // Hash the password
        $data['password'] = Hash::make($data['password']);
        
        // Set default role if not provided
        $data['role'] = $data['role'] ?? 'customer';
        
        // Set default status
        $data['status'] = 'active';

        // Create user
        $user = $this->userRepository->create($data);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Authenticate user and generate token.
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials): array
    {
        // Attempt to find user by email
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        // Verify password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('Invalid credentials');
        }

        // Check if user is active
        if ($user->status !== 'active') {
            throw new \Exception('Account is ' . $user->status);
        }

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Get authenticated user details.
     *
     * @return User
     */
    public function me(): User
    {
        return JWTAuth::user();
    }

    /**
     * Refresh JWT token.
     *
     * @return array
     */
    public function refresh(): array
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Logout user (invalidate token).
     *
     * @return void
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Verify user's role.
     *
     * @param User $user
     * @param array $allowedRoles
     * @return bool
     */
    public function hasRole(User $user, array $allowedRoles): bool
    {
        return in_array($user->role, $allowedRoles);
    }

    /**
     * Check if user is active.
     *
     * @param User $user
     * @return bool
     */
    public function isActive(User $user): bool
    {
        return $user->status === 'active';
    }
}
