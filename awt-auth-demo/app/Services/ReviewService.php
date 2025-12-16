<?php

namespace App\Services;

use App\Models\Review;
use App\Models\User;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class ReviewService
 * 
 * Service class for handling review business logic.
 * Contains all business rules for product reviews.
 * 
 * @package App\Services
 */
class ReviewService
{
    /**
     * @var ReviewRepositoryInterface
     */
    protected $reviewRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ReviewService constructor.
     *
     * @param ReviewRepositoryInterface $reviewRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ReviewRepositoryInterface $reviewRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Get reviews for a product.
     *
     * @param int $productId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductReviews(int $productId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->reviewRepository->getByProduct($productId, $perPage);
    }

    /**
     * Create a new review.
     *
     * @param int $productId
     * @param array $data
     * @param User $user
     * @return Review
     * @throws \Exception
     */
    public function createReview(int $productId, array $data, User $user): Review
    {
        // Check if product exists
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        // Check if user already reviewed this product
        if ($this->reviewRepository->userHasReviewed($user->id, $productId)) {
            throw new \Exception('You have already reviewed this product');
        }

        // Validate rating
        if (!isset($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            throw new \Exception('Rating must be between 1 and 5');
        }

        // Create review
        $review = $this->reviewRepository->create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return $this->reviewRepository->findById($review->id);
    }

    /**
     * Update a review.
     *
     * @param int $id
     * @param array $data
     * @param User $user
     * @return Review
     * @throws \Exception
     */
    public function updateReview(int $id, array $data, User $user): Review
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            throw new \Exception('Review not found');
        }

        // Check authorization
        if (!$this->canModifyReview($review, $user)) {
            throw new \Exception('Unauthorized to modify this review');
        }

        // Validate rating if provided
        if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
            throw new \Exception('Rating must be between 1 and 5');
        }

        return $this->reviewRepository->update($id, $data);
    }

    /**
     * Delete a review.
     *
     * @param int $id
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteReview(int $id, User $user): bool
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            throw new \Exception('Review not found');
        }

        // Check authorization
        if (!$this->canModifyReview($review, $user)) {
            throw new \Exception('Unauthorized to delete this review');
        }

        return $this->reviewRepository->delete($id);
    }

    /**
     * Check if user can modify review.
     *
     * @param Review $review
     * @param User $user
     * @return bool
     */
    protected function canModifyReview(Review $review, User $user): bool
    {
        // Admin can modify any review
        if ($user->role === 'admin') {
            return true;
        }

        // User can only modify their own reviews
        return $review->user_id === $user->id;
    }

    /**
     * Get average rating for a product.
     *
     * @param int $productId
     * @return float
     */
    public function getAverageRating(int $productId): float
    {
        return $this->reviewRepository->getAverageRating($productId);
    }
}
