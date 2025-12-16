<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface ReviewRepositoryInterface
 * 
 * Repository pattern interface for Review data access operations.
 * 
 * @package App\Repositories\Contracts
 */
interface ReviewRepositoryInterface
{
    /**
     * Get paginated reviews for a product.
     *
     * @param int $productId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByProduct(int $productId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a review by ID.
     *
     * @param int $id
     * @return Review|null
     */
    public function findById(int $id): ?Review;

    /**
     * Create a new review.
     *
     * @param array $data
     * @return Review
     */
    public function create(array $data): Review;

    /**
     * Update an existing review.
     *
     * @param int $id
     * @param array $data
     * @return Review
     */
    public function update(int $id, array $data): Review;

    /**
     * Delete a review.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Check if user already reviewed a product.
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function userHasReviewed(int $userId, int $productId): bool;

    /**
     * Get average rating for a product.
     *
     * @param int $productId
     * @return float
     */
    public function getAverageRating(int $productId): float;

    /**
     * Get reviews count for a product.
     *
     * @param int $productId
     * @return int
     */
    public function getReviewsCount(int $productId): int;
}
