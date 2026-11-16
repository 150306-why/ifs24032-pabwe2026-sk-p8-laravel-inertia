<?php

use App\Features\Admin\Controllers\PostController as AdminPostController;
use App\Features\Admin\Controllers\UserController as AdminUserController;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\PostAiController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PostCoverController;
use App\Features\Post\Controllers\PostTransferController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ---------- PUBLIC ----------
Route::get('/', [PublicPostController::class, 'home'])->name('home');
Route::get('/articles', [PublicPostController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [PublicPostController::class, 'show'])->name('articles.show');

// ---------- AUTHENTICATION ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ---------- MEMBER / PENULIS ----------
    Route::middleware('role:member')->prefix('dashboard')->name('dashboard.')->group(function () {
        Route::redirect('/', '/dashboard/posts')->name('home');

        // Route statis harus didaftarkan sebelum route berparameter {post}.
        Route::get('posts/export', [PostTransferController::class, 'export'])->name('posts.export');
        Route::post('posts/import', [PostTransferController::class, 'import'])->name('posts.import');
        Route::post('posts/ai-generate', [PostAiController::class, 'generate'])->name('posts.ai-generate');
        Route::post('posts/ai-save', [PostAiController::class, 'save'])->name('posts.ai-save');

        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');

        Route::get('posts/{post}', [PostController::class, 'show'])->whereNumber('post')->name('posts.show');
        Route::put('posts/{post}', [PostController::class, 'update'])->whereNumber('post')->name('posts.update');
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->whereNumber('post')->name('posts.destroy');
        Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->whereNumber('post')->name('posts.publish');
        Route::post('posts/{post}/cover', [PostCoverController::class, 'store'])->whereNumber('post')->name('posts.cover.store');
        Route::delete('posts/{post}/cover', [PostCoverController::class, 'destroy'])->whereNumber('post')->name('posts.cover.destroy');
    });

    // ---------- ADMIN (READ-ONLY monitoring) ----------
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/posts', [AdminUserController::class, 'posts'])->whereNumber('user')->name('users.posts');
        Route::get('users/{user}/posts/export', [AdminUserController::class, 'exportPosts'])->whereNumber('user')->name('users.posts.export');
        Route::get('posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('posts/export', [AdminPostController::class, 'export'])->name('posts.export');
    });
});
