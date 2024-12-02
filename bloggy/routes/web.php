<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

/**********************
 * Breeze starter kit *
 **********************/

 Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/**********************
 * Les meves rutes    *
 **********************/

Route::get('/', [SiteController::class, 'home'])->name('home');

Route::get('/contact', [SiteController::class, 'contact'])->name('contact.form');
Route::post('/contact', [SiteController::class, 'sendMail'])->name('contact.send');

Route::resource('posts', PostController::class)
    ->middleware(['auth']);

Route::get('/my-posts', [PostController::class, 'myIndex'])->name('posts.myIndex')
    ->middleware(['auth']);

Route::resource('posts.comments', CommentController::class)
    ->middleware(['auth']);