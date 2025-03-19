<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  echo "hellow World";
});

Route::get('/show_data', [MainController::class, 'showData']);
