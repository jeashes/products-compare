<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::redirect('/', '/home', 302);

Route::get('/home', function () {
    $path = public_path('index.html');
    abort_unless(File::exists($path), 404);
    return response()->file($path, ['Content-Type' => 'text/html; charset=UTF-8']);
});

Route::get('/listing', function () {
    $path = public_path('listing.html');
    abort_unless(File::exists($path), 404);
    return response()->file($path, ['Content-Type' => 'text/html; charset=UTF-8']);
});

Route::get('/compare', function () {
    $path = public_path('compare.html');
    abort_unless(File::exists($path), 404);
    return response()->file($path, ['Content-Type' => 'text/html; charset=UTF-8']);
});

Route::redirect('/index.html',   '/home',    301);
Route::redirect('/listing.html', '/listing', 301);
Route::redirect('/compare.html', '/compare', 301);
