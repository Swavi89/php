<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UserRepositoryInterface
 * 
 * Repository pattern interface for User data access operations.
 * This interface defines the contract for user-related database operations,
 * promoting loose coupling and making the code more testable.
 * 
 * @package App\Repositories\Contracts
 */
interface UserRepositoryInterface
{
    /**
     * Get all users with optional filters.
     *
     * @param array $filters Optional filters (role, status, etc.)
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     * @return User
     */
    public function update(int $id, array $data): User;

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get users by role.
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection;

    /**
     * Get users by status.
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection;

    /**
     * Count users by role.
     *
     * @param string|null $role
     * @return int
     */
    public function countByRole(?string $role = null): int;

    /**
     * Check if email exists.
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool;
}
