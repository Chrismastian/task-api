<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'status' => 'ok',
    'docs' => '/api',
]));

Route::get('/up', fn () => response('ok', 200));
