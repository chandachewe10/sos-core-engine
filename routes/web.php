<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// in routes/web.php
Route::get('/reset-password/{token}', function ($token) {
})->name('password.reset');

