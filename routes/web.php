<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('app'));

Route::fallback(fn () => view('app'));
