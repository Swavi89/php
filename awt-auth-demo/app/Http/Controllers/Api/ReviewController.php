<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ReviewController
 * 
 * Handles review-related HTTP requests.
 * Uses ReviewService for business logic.
 * 
 * @package App\Http\Controllers\Api
 */
class ReviewController extends Controller
{
    /**
     * @var ReviewService
     */
    protected $reviewService;

    /**
     * Create a new ReviewController instance.
     *
     * @param ReviewService $reviewService
     */
    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Get reviews for a product.
     *
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($productId, Request $request)
    {
        $perPage = $request->get('per_page', 15);

        try {
            $reviews = $this->reviewService->getProductReviews($productId, $perPage);

            return response()->json([
                'success' => true,
                'data' => $reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user' => [
                            'id' => $review->user->id,
                            'name' => $review->user->name,
                        ],
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                    ];
                }),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'total' => $reviews->total(),
                    'average_rating' => $this->reviewService->getAverageRating($productId),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created review.
     *
     * @param  Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $productId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        try {
            $review = $this->reviewService->createReview($productId, $request->all(), $user);

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user' => $review->user->name,
                    'created_at' => $review->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === 'Product not found' ? 404 : 400;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Update the specified review.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        try {
            $review = $this->reviewService->updateReview($id, $request->all(), $user);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => $review,
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === 'Review not found' ? 404 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Remove the specified review.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = auth()->user();

        try {
            $this->reviewService->deleteReview($id, $user);

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === 'Review not found' ? 404 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
