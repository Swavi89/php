<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class ReviewRepository
 * 
 * Implementation of ReviewRepositoryInterface.
 * Handles all database operations related to reviews.
 * 
 * @package App\Repositories
 */
class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var Review
     */
    protected $model;

    /**
     * ReviewRepository constructor.
     *
     * @param Review $model
     */
    public function __construct(Review $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated reviews for a product.
     *
     * @param int $productId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByProduct(int $productId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('product_id', $productId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a review by ID.
     *
     * @param int $id
     * @return Review|null
     */
    public function findById(int $id): ?Review
    {
        return $this->model->with(['user', 'product'])->find($id);
    }

    /**
     * Create a new review.
     *
     * @param array $data
     * @return Review
     */
    public function create(array $data): Review
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing review.
     *
     * @param int $id
     * @param array $data
     * @return Review
     */
    public function update(int $id, array $data): Review
    {
        $review = $this->findById($id);
        $review->update($data);
        return $review->fresh(['user', 'product']);
    }

    /**
     * Delete a review.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $review = $this->findById($id);
        return $review ? $review->delete() : false;
    }

    /**
     * Check if user already reviewed a product.
     *
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function userHasReviewed(int $userId, int $productId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get average rating for a product.
     *
     * @param int $productId
     * @return float
     */
    public function getAverageRating(int $productId): float
    {
        return (float) $this->model->where('product_id', $productId)
            ->avg('rating') ?? 0;
    }

    /**
     * Get reviews count for a product.
     *
     * @param int $productId
     * @return int
     */
    public function getReviewsCount(int $productId): int
    {
        return $this->model->where('product_id', $productId)->count();
    }
}
