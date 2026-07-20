<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Cms\DashboardController;
use App\Http\Controllers\Cms\HomepageSettingController;
use App\Http\Controllers\Cms\InquiryController as CmsInquiryController;
use App\Http\Controllers\Cms\MediaUploadController;
use App\Http\Controllers\Cms\PostController as CmsPostController;
use App\Http\Controllers\Cms\PostWorkflowController;
use App\Http\Controllers\Cms\ProductCategoryController;
use App\Http\Controllers\Cms\ProductController as CmsProductController;
use App\Http\Controllers\Cms\TestimonialController;
use App\Http\Controllers\Cms\UserController;
use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{post:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/cms/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/cms/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/cms/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/cms/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/cms/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/cms/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::prefix('cms')->name('cms.')->middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::resource('posts', CmsPostController::class)->except('show');
    Route::get('/posts/{post}/preview', [CmsPostController::class, 'preview'])->name('posts.preview');
    Route::post('/posts/{post}/submit', [PostWorkflowController::class, 'submit'])->name('posts.submit');
    Route::post('/posts/{post}/media', [MediaUploadController::class, 'store'])->name('posts.media.store');

    Route::middleware('can:admin')->group(function () {
        Route::post('/posts/{post}/publish', [PostWorkflowController::class, 'publish'])->name('posts.publish');
        Route::post('/posts/{post}/return', [PostWorkflowController::class, 'return'])->name('posts.return');
        Route::post('/posts/{post}/archive', [PostWorkflowController::class, 'archive'])->name('posts.archive');
        Route::get('/homepage', [HomepageSettingController::class, 'edit'])->name('homepage.edit');
        Route::put('/homepage', [HomepageSettingController::class, 'update'])->name('homepage.update');
        Route::resources([
            'testimonials' => TestimonialController::class,
            'users' => UserController::class,
            'categories' => ProductCategoryController::class,
            'products' => CmsProductController::class,
        ], ['except' => ['show']]);
        Route::get('/inquiries', [CmsInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [CmsInquiryController::class, 'show'])->name('inquiries.show');
        Route::put('/inquiries/{inquiry}', [CmsInquiryController::class, 'update'])->name('inquiries.update');
    });
});
