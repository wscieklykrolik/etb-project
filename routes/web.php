<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/news', function () {
    return view('news');
});

Route::get('/team', function () {
    return view('team');
});

Route::get('/schedule', function () {
    return view('schedule');
});

Route::get('/contact', function () {
    return view('contact');
});
