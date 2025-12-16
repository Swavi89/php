<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface ProductRepositoryInterface
 * 
 * Repository pattern interface for Product data access operations.
 * Handles all database queries related to products.
 * 
 * @package App\Repositories\Contracts
 */
interface ProductRepositoryInterface
{
    /**
     * Get paginated products with filters and relationships.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a product by ID with relationships.
     *
     * @param int $id
     * @param array $relations
     * @return Product|null
     */
    public function findById(int $id, array $relations = []): ?Product;

    /**
     * Find a product by slug.
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product;

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get products by vendor.
     *
     * @param int $vendorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByVendor(int $vendorId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products by category.
     *
     * @param int $categoryId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Sync product categories.
     *
     * @param int $productId
     * @param array $categoryIds
     * @return void
     */
    public function syncCategories(int $productId, array $categoryIds): void;

    /**
     * Update stock quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $productId, int $quantity): bool;

    /**
     * Decrement stock quantity.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function decrementStock(int $productId, int $quantity): bool;

    /**
     * Get total products count.
     *
     * @return int
     */
    public function count(): int;
}
