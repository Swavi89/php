<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class OrderService
 * 
 * Service class for handling order business logic.
 * Contains all business rules for order processing.
 * 
 * @package App\Services
 */
class OrderService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductService
     */
    protected $productService;

    /**
     * OrderService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductService $productService
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        ProductService $productService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
    }

    /**
     * Get paginated orders.
     *
     * @param array $filters
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrders(array $filters, User $user, int $perPage = 15): LengthAwarePaginator
    {
        // Customers can only see their own orders
        if ($user->role === 'customer') {
            $filters['user_id'] = $user->id;
        }

        return $this->orderRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get a single order by ID.
     *
     * @param int $id
     * @param User $user
     * @return Order|null
     * @throws \Exception
     */
    public function getOrderById(int $id, User $user): ?Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            return null;
        }

        // Check authorization
        if (!$this->canViewOrder($order, $user)) {
            throw new \Exception('Unauthorized to view this order');
        }

        return $order;
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @param User $user
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $data, User $user): Order
    {
        // Validate items
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new \Exception('Order must contain at least one item');
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $orderItems = [];

            // Process each item
            foreach ($data['items'] as $item) {
                $product = $this->productRepository->findById($item['product_id']);

                if (!$product) {
                    throw new \Exception("Product with ID {$item['product_id']} not found");
                }

                if ($product->status !== 'published') {
                    throw new \Exception("Product {$product->name} is not available");
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $itemTotal,
                ];

                // Decrease stock
                $this->productService->decreaseStock($product->id, $item['quantity']);
            }

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return $this->orderRepository->findById($order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @param User $user
     * @return Order
     * @throws \Exception
     */
    public function updateOrderStatus(int $id, string $status, User $user): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        // Check authorization
        if (!$this->canModifyOrder($order, $user)) {
            throw new \Exception('Unauthorized to modify this order');
        }

        // Validate status transition
        $this->validateStatusTransition($order->status, $status);

        $this->orderRepository->updateStatus($id, $status);

        return $this->orderRepository->findById($id);
    }

    /**
     * Cancel an order.
     *
     * @param int $id
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function cancelOrder(int $id, User $user): bool
    {
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new \Exception('Order not found');
        }

        // Check authorization
        if (!$this->canModifyOrder($order, $user)) {
            throw new \Exception('Unauthorized to cancel this order');
        }

        // Can only cancel pending or processing orders
        if (!in_array($order->status, ['pending', 'processing'])) {
            throw new \Exception('Cannot cancel order with status: ' . $order->status);
        }

        DB::beginTransaction();

        try {
            // Restore stock for each item
            foreach ($order->items as $item) {
                $product = $this->productRepository->findById($item->product_id);
                if ($product) {
                    $this->productRepository->updateStock(
                        $product->id,
                        $product->stock_quantity + $item->quantity
                    );
                }
            }

            // Update order status
            $this->orderRepository->updateStatus($id, 'cancelled');

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if user can view order.
     *
     * @param Order $order
     * @param User $user
     * @return bool
     */
    protected function canViewOrder(Order $order, User $user): bool
    {
        // Admin can view all orders
        if ($user->role === 'admin') {
            return true;
        }

        // Vendor can view orders containing their products
        if ($user->role === 'vendor') {
            foreach ($order->items as $item) {
                if ($item->product->vendor_id === $user->id) {
                    return true;
                }
            }
        }

        // Customer can view their own orders
        if ($order->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can modify order.
     *
     * @param Order $order
     * @param User $user
     * @return bool
     */
    protected function canModifyOrder(Order $order, User $user): bool
    {
        // Admin can modify all orders
        if ($user->role === 'admin') {
            return true;
        }

        // Vendor can modify orders containing their products
        if ($user->role === 'vendor') {
            foreach ($order->items as $item) {
                if ($item->product->vendor_id === $user->id) {
                    return true;
                }
            }
        }

        // Customer can cancel their own pending orders
        if ($user->role === 'customer' && $order->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Validate status transition.
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return void
     * @throws \Exception
     */
    protected function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            throw new \Exception('Invalid current status');
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new \Exception("Cannot change status from {$currentStatus} to {$newStatus}");
        }
    }

    /**
     * Generate a unique order number.
     *
     * @return string
     */
    protected function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(12));
    }
}
