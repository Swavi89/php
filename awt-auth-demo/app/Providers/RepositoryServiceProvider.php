<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repository Contracts
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;

// Repository Implementations
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ReviewRepository;

/**
 * Class RepositoryServiceProvider
 * 
 * Service provider for binding repository interfaces to their implementations.
 * This enables dependency injection and promotes loose coupling.
 * 
 * Register this provider in config/app.php providers array.
 * 
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind UserRepository
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Bind ProductRepository
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Bind OrderRepository
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );

        // Bind ReviewRepository
        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
