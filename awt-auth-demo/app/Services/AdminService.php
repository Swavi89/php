<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;

/**
 * Class AdminService
 * 
 * Service class for handling admin-specific business logic.
 * Contains statistics and administrative operations.
 * 
 * @package App\Services
 */
class AdminService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * AdminService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param ProductRepositoryInterface $productRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get admin dashboard statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_users' => $this->userRepository->countByRole(),
            'total_customers' => $this->userRepository->countByRole('customer'),
            'total_vendors' => $this->userRepository->countByRole('vendor'),
            'total_admins' => $this->userRepository->countByRole('admin'),
            'total_products' => $this->productRepository->count(),
            'total_orders' => $this->orderRepository->count(),
        ];
    }
}
