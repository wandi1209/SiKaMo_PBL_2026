<?php

use Illuminate\Support\Facades\Route;

// Landing page — selalu tampil, tombol adaptif sesuai status login (@auth di Blade)
Route::get('/', function () {
    return view('landing');
});
