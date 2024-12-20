<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(base_path('routes/v1/api.php'));

Route::prefix('v2')
    ->group(base_path('routes/v2/api.php'));
