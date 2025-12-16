<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to JWT Authentication API',
        'version' => '1.0.0',
        'api_url' => url('/api'),
    ]);
});
