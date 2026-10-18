<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'UTS PL 541304 Backend API',
        'status' => 'running',
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'FrankenPHP'
    ]);
});