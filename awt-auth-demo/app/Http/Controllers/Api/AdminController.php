<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Class AdminController
 * 
 * Handles admin-related HTTP requests.
 * Uses AdminService for business logic.
 * 
 * @package App\Http\Controllers\Api
 */
class AdminController extends Controller
{
    /**
     * @var AdminService
     */
    protected $adminService;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * Create a new AdminController instance.
     *
     * @param AdminService $adminService
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        AdminService $adminService,
        UserRepositoryInterface $userRepository
    ) {
        $this->adminService = $adminService;
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users (admin only).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers(Request $request)
    {
        try {
            $filters = [
                'role' => $request->get('role'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
            ];

            $users = $this->userRepository->getAll($filters);

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get admin statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->adminService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
