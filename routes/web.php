<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "Hello World";
});


Route::view('/privacy-policy', 'privacy-policy');
