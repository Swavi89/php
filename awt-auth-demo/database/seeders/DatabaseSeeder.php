<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Vendor Users
        $vendor1 = User::create([
            'name' => 'Tech Vendor',
            'email' => 'vendor1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $vendor2 = User::create([
            'name' => 'Fashion Vendor',
            'email' => 'vendor2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Customer Users
        $customer1 = User::create([
            'name' => 'John Doe',
            'email' => 'customer1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $customer2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'customer2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $computers = Category::create([
            'name' => 'Computers',
            'slug' => 'computers',
            'parent_id' => $electronics->id,
        ]);

        $phones = Category::create([
            'name' => 'Phones',
            'slug' => 'phones',
            'parent_id' => $electronics->id,
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
        ]);

        $mensClothing = Category::create([
            'name' => "Men's Clothing",
            'slug' => 'mens-clothing',
            'parent_id' => $clothing->id,
        ]);

        $womensClothing = Category::create([
            'name' => "Women's Clothing",
            'slug' => 'womens-clothing',
            'parent_id' => $clothing->id,
        ]);

        // Create Products
        $laptop = Product::create([
            'vendor_id' => $vendor1->id,
            'name' => 'MacBook Pro 16"',
            'slug' => 'macbook-pro-16',
            'description' => 'Powerful laptop with M2 Pro chip, 16GB RAM, 512GB SSD',
            'price' => 2499.99,
            'stock_quantity' => 10,
            'status' => 'published',
        ]);
        $laptop->categories()->attach([$electronics->id, $computers->id]);

        $phone = Product::create([
            'vendor_id' => $vendor1->id,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'description' => 'Latest iPhone with A17 Pro chip, 256GB storage',
            'price' => 1199.99,
            'stock_quantity' => 25,
            'status' => 'published',
        ]);
        $phone->categories()->attach([$electronics->id, $phones->id]);

        $tshirt = Product::create([
            'vendor_id' => $vendor2->id,
            'name' => "Men's Cotton T-Shirt",
            'slug' => 'mens-cotton-tshirt',
            'description' => 'Comfortable 100% cotton t-shirt, available in multiple colors',
            'price' => 29.99,
            'stock_quantity' => 100,
            'status' => 'published',
        ]);
        $tshirt->categories()->attach([$clothing->id, $mensClothing->id]);

        $dress = Product::create([
            'vendor_id' => $vendor2->id,
            'name' => "Women's Summer Dress",
            'slug' => 'womens-summer-dress',
            'description' => 'Elegant summer dress perfect for any occasion',
            'price' => 79.99,
            'stock_quantity' => 50,
            'status' => 'published',
        ]);
        $dress->categories()->attach([$clothing->id, $womensClothing->id]);

        $headphones = Product::create([
            'vendor_id' => $vendor1->id,
            'name' => 'Wireless Headphones',
            'slug' => 'wireless-headphones',
            'description' => 'Noise-cancelling wireless headphones with 30-hour battery',
            'price' => 199.99,
            'stock_quantity' => 40,
            'status' => 'published',
        ]);
        $headphones->categories()->attach([$electronics->id]);

        // Create Reviews
        Review::create([
            'user_id' => $customer1->id,
            'product_id' => $laptop->id,
            'rating' => 5,
            'comment' => 'Excellent laptop! Very fast and reliable.',
        ]);

        Review::create([
            'user_id' => $customer2->id,
            'product_id' => $laptop->id,
            'rating' => 4,
            'comment' => 'Great performance but a bit expensive.',
        ]);

        Review::create([
            'user_id' => $customer1->id,
            'product_id' => $phone->id,
            'rating' => 5,
            'comment' => 'Best phone I ever had!',
        ]);

        Review::create([
            'user_id' => $customer2->id,
            'product_id' => $tshirt->id,
            'rating' => 4,
            'comment' => 'Very comfortable and good quality.',
        ]);

        Review::create([
            'user_id' => $customer1->id,
            'product_id' => $dress->id,
            'rating' => 5,
            'comment' => 'Beautiful dress, fits perfectly!',
        ]);

        // Create Orders
        $order1 = Order::create([
            'user_id' => $customer1->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'total_amount' => 0,
            'status' => 'delivered',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $laptop->id,
            'quantity' => 1,
            'price' => $laptop->price,
            'subtotal' => $laptop->price,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $headphones->id,
            'quantity' => 2,
            'price' => $headphones->price,
            'subtotal' => $headphones->price * 2,
        ]);

        $order1->total_amount = $order1->items->sum('subtotal');
        $order1->save();

        $order2 = Order::create([
            'user_id' => $customer2->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'total_amount' => 0,
            'status' => 'processing',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $phone->id,
            'quantity' => 1,
            'price' => $phone->price,
            'subtotal' => $phone->price,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $tshirt->id,
            'quantity' => 3,
            'price' => $tshirt->price,
            'subtotal' => $tshirt->price * 3,
        ]);

        $order2->total_amount = $order2->items->sum('subtotal');
        $order2->save();

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Test Users Created:');
        $this->command->info('-------------------');
        $this->command->info('Admin: admin@example.com / password123');
        $this->command->info('Vendor 1: vendor1@example.com / password123');
        $this->command->info('Vendor 2: vendor2@example.com / password123');
        $this->command->info('Customer 1: customer1@example.com / password123');
        $this->command->info('Customer 2: customer2@example.com / password123');
    }
}
