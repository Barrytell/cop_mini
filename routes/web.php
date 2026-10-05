<?php

use App\Http\Controllers\AccountSuspendedController;
use App\Http\Controllers\Public\CmsPageController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LibraryController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Webhook\FlutterwaveWebhookController;
use App\Support\SitePages;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

foreach (SitePages::PATHS as $slug => $path) {
    Route::get($path, [CmsPageController::class, 'named'])->defaults('slug', $slug)->name('site.'.$slug);
}

Route::get('/pages/{page:slug}', [CmsPageController::class, 'show'])->name('pages.show');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post:slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/faq', [LibraryController::class, 'faq'])->name('faq');
Route::get('/testimonials', [LibraryController::class, 'testimonials'])->name('testimonials');
Route::get('/gallery', [LibraryController::class, 'gallery'])->name('gallery');
Route::get('/downloads', [LibraryController::class, 'downloads'])->name('downloads');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::post('/webhooks/flutterwave', FlutterwaveWebhookController::class)
    ->middleware('throttle:webhooks')
    ->name('webhooks.flutterwave');

Route::middleware('auth')->group(function () {
    Route::get('/account/suspended', AccountSuspendedController::class)->name('account.suspended');
});

require __DIR__.'/auth.php';
require __DIR__.'/member.php';
require __DIR__.'/admin.php';
