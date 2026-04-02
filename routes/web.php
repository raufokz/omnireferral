<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RealtorController;
use App\Http\Controllers\Webhooks\GoHighLevelWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use App\Models\Property;
use App\Models\RealtorProfile;

Route::get('/sitemap.xml', function () {
    $properties = Property::latest()->take(500)->get();
    $agents = RealtorProfile::latest()->take(500)->get();

    return response()->view('sitemap', [
        'properties' => $properties,
        'agents' => $agents
    ])->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/packages/{package:slug}/checkout', [PricingController::class, 'checkout'])->name('packages.checkout');
Route::post('/packages/{package:slug}/stripe-checkout', [PricingController::class, 'startCheckout'])->name('packages.checkout.start');
Route::get('/packages/{package:slug}/success', [PricingController::class, 'success'])->name('packages.success');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [HomeController::class, 'terms'])->name('terms');
Route::get('/resources', [HomeController::class, 'resources'])->name('resources');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/reviews', [HomeController::class, 'reviews'])->name('reviews');
Route::get('/careers', [HomeController::class, 'careers'])->name('careers');
Route::get('/surveys-campaigns', [HomeController::class, 'surveys'])->name('surveys');
Route::get('/listings', [HomeController::class, 'listings'])->name('listings');
Route::get('/listings/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/onboarding/{role}', [HomeController::class, 'onboarding'])->name('onboarding');
Route::get('/client-form-submission7', [HomeController::class, 'clientFormSubmission'])->name('client.form.submission');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::post('/lead-store', [LeadController::class, 'store'])->name('leads.store');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/agents', [RealtorController::class, 'index'])->name('agents.index');
Route::get('/agents/{realtor}', [RealtorController::class, 'show'])->name('agents.show');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/webhooks/gohighlevel/onboarding', [GoHighLevelWebhookController::class, 'onboardingCompleted'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.gohighlevel.onboarding');
Route::post('/webhooks/gohighlevel/lead-status', [GoHighLevelWebhookController::class, 'leadStatusUpdated'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.gohighlevel.lead-status');
Route::post('/webhooks/stripe', StripeWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.stripe');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');

    Route::middleware(['role:buyer'])->group(function () {
        Route::get('/buyer/dashboard', [DashboardController::class, 'buyer'])->name('dashboard.buyer');
    });

    Route::middleware(['role:seller'])->group(function () {
        Route::get('/seller/dashboard', [DashboardController::class, 'seller'])->name('dashboard.seller');
    });

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/agent/dashboard', [DashboardController::class, 'agent'])->name('dashboard.agent');
    });

    Route::middleware(['role:seller,agent'])->group(function () {
        Route::post('/seller/properties', [PropertyController::class, 'store'])->name('properties.store');
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    });

    Route::middleware(['role:admin,staff'])->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::resource('admin/blog', \App\Http\Controllers\Admin\BlogController::class)->names([
            'index' => 'admin.blog.index',
            'create' => 'admin.blog.create',
            'store' => 'admin.blog.store',
            'show' => 'admin.blog.show',
            'edit' => 'admin.blog.edit',
            'update' => 'admin.blog.update',
            'destroy' => 'admin.blog.destroy',
        ]);

        Route::post('admin/leads/{lead}/route', function (\App\Models\Lead $lead) {
            \App\Jobs\RouteLeadToAgent::dispatchSync($lead->id);
            return back()->with('success', 'Lead routing algorithm triggered successfully.');
        })->name('admin.leads.route');
    });
});

