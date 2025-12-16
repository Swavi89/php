<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Class ProductService
 * 
 * Service class for handling product business logic.
 * Contains all business rules and operations related to products.
 * 
 * @package App\Services
 */
class ProductService
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get paginated products list.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get a single product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function getProductById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @param User $user
     * @return Product
     * @throws \Exception
     */
    public function createProduct(array $data, User $user): Product
    {
        // Validate user role
        if (!in_array($user->role, ['vendor', 'admin'])) {
            throw new \Exception('Only vendors and admins can create products');
        }

        // Set vendor ID
        $data['vendor_id'] = $user->id;

        // Generate slug from name
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        // Set default status if not provided
        $data['status'] = $data['status'] ?? 'draft';

        // Create product
        $product = $this->productRepository->create($data);

        // Sync categories if provided
        if (isset($data['categories']) && is_array($data['categories'])) {
            $this->productRepository->syncCategories($product->id, $data['categories']);
        }

        return $this->productRepository->findById($product->id);
    }

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @param User $user
     * @return Product
     * @throws \Exception
     */
    public function updateProduct(int $id, array $data, User $user): Product
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        // Check authorization
        if (!$this->canModifyProduct($product, $user)) {
            throw new \Exception('Unauthorized to modify this product');
        }

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
        }

        // Update product
        $product = $this->productRepository->update($id, $data);

        // Sync categories if provided
        if (isset($data['categories']) && is_array($data['categories'])) {
            $this->productRepository->syncCategories($id, $data['categories']);
        }

        return $this->productRepository->findById($id);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteProduct(int $id, User $user): bool
    {
        $product = $this->productRepository->findById($id);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        // Check authorization
        if (!$this->canModifyProduct($product, $user)) {
            throw new \Exception('Unauthorized to delete this product');
        }

        return $this->productRepository->delete($id);
    }

    /**
     * Get vendor's products.
     *
     * @param int $vendorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getVendorProducts(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getByVendor($vendorId, $perPage);
    }

    /**
     * Update product stock.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        return $this->productRepository->updateStock($productId, $quantity);
    }

    /**
     * Decrease product stock (when order is placed).
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        if ($product->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        return $this->productRepository->decrementStock($productId, $quantity);
    }

    /**
     * Check if user can modify product.
     *
     * @param Product $product
     * @param User $user
     * @return bool
     */
    protected function canModifyProduct(Product $product, User $user): bool
    {
        // Admin can modify any product
        if ($user->role === 'admin') {
            return true;
        }

        // Vendor can only modify their own products
        if ($user->role === 'vendor' && $product->vendor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Generate a unique slug from name.
     *
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $exists = Product::where('slug', $slug)
                ->when($excludeId, function ($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId);
                })
                ->exists();

            if (!$exists) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
