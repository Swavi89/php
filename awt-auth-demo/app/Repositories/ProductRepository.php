<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class ProductRepository
 * 
 * Implementation of ProductRepositoryInterface.
 * Handles all database operations related to products.
 * 
 * @package App\Repositories
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var Product
     */
    protected $model;

    /**
     * ProductRepository constructor.
     *
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated products with filters and relationships.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['vendor', 'categories', 'reviews']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'published'); // Default to published
        }

        // Filter by vendor
        if (isset($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Filter by category
        if (isset($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        // Search by name or description
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        // Price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find a product by ID with relationships.
     *
     * @param int $id
     * @param array $relations
     * @return Product|null
     */
    public function findById(int $id, array $relations = []): ?Product
    {
        $query = $this->model->query();

        if (!empty($relations)) {
            $query->with($relations);
        } else {
            $query->with(['vendor', 'categories', 'reviews.user']);
        }

        return $query->find($id);
    }

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->model->where('slug', $slug)
            ->with(['vendor', 'categories', 'reviews.user'])
            ->first();
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);
        return $product->fresh(['vendor', 'categories']);
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $product = $this->findById($id);
        return $product ? $product->delete() : false;
    }

    /**
     * Get products by vendor.
     *
     * @param int $vendorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('vendor_id', $vendorId)
            ->with(['categories', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->with(['vendor', 'categories', 'reviews'])
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Sync product categories.
     *
     * @param int $productId
     * @param array $categoryIds
     * @return void
     */
    public function syncCategories(int $productId, array $categoryIds): void
    {
        $product = $this->findById($productId);
        if ($product) {
            $product->categories()->sync($categoryIds);
        }
    }

    /**
     * Update stock quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);
        if ($product) {
            $product->stock_quantity = $quantity;
            return $product->save();
        }
        return false;
    }

    /**
     * Decrement stock quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function decrementStock(int $productId, int $quantity): bool
    {
        $product = $this->findById($productId);
        if ($product && $product->stock_quantity >= $quantity) {
            $product->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Get total products count.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }
}
