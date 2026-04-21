<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeadManagementController as AdminLeadManagementController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Agent\LeadController as AgentLeadController;
use App\Http\Controllers\Agent\PortalController as AgentPortalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RealtorController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Webhooks\GoHighLevelWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
Route::get('/payment-refund-cancellation-policy', function () {
    return view('pages.payment-policy', [
        'meta' => [
            'title' => 'Payment, Refund & Cancellation Policy | OmniReferral',
            'description' => 'Review OmniReferral payment terms, refund eligibility, cancellation rules, and promotional policy details.',
        ],
    ]);
})->name('payment.policy');
Route::get('/scam-prevention', function () {
    return view('pages.scam-prevention', [
        'meta' => [
            'title' => 'Scam Prevention | OmniReferral',
            'description' => 'Learn how to spot impersonation scams, fake websites, and suspicious payment requests claiming to represent OmniReferral.',
        ],
    ]);
})->name('scam.prevention');
Route::get('/communication-policy', function () {
    return view('pages.communication-policy', [
        'meta' => [
            'title' => 'Communication Policy | OmniReferral',
            'description' => 'Review OmniReferral communication consent, SMS terms, call recording practices, data-sharing rules, and dispute-resolution terms.',
        ],
    ]);
})->name('communication.policy');
Route::get('/resources', [HomeController::class, 'resources'])->name('resources');
Route::get('/news', [HomeController::class, 'news'])->name('news');
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/careers', [HomeController::class, 'careers'])->name('careers');
Route::get('/surveys-campaigns', [HomeController::class, 'surveys'])->name('surveys');
Route::get('/listings', [HomeController::class, 'listings'])->name('listings');
Route::get('/listings/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/onboarding/{role}', function (string $role): RedirectResponse {
    abort_unless(in_array($role, ['buyer', 'seller', 'agent', 'admin'], true), 404);

    $dashboardRoute = match ($role) {
        'buyer' => route('dashboard.buyer'),
        'seller' => route('dashboard.seller'),
        'admin' => route('admin.dashboard'),
        default => route('dashboard.agent'),
    };

    if (Auth::check()) {
        return redirect()
            ->to($dashboardRoute)
            ->with('info', 'GoHighLevel handles onboarding automatically now. Continue from your dashboard.');
    }

    return redirect()
        ->route('login')
        ->with('info', 'GoHighLevel handles onboarding automatically now. Sign in to continue to your dashboard.');
})->name('onboarding');

Route::get('/client-submission-form7', [HomeController::class, 'clientFormSubmission'])->name('client.form.submission');
Route::get('/client-form-submission7', [HomeController::class, 'clientFormSubmission']);
Route::get('/form-submission', [HomeController::class, 'formSubmission'])->name('form.submission');
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
Route::post('/webhooks/gohighlevel/purchase', [GoHighLevelWebhookController::class, 'packagePurchased'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.gohighlevel.purchase');
Route::post('/webhooks/gohighlevel/lead-status', [GoHighLevelWebhookController::class, 'leadStatusUpdated'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.gohighlevel.lead-status');
Route::post('/webhooks/stripe', StripeWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('webhooks.stripe');

Route::middleware(['auth'])->group(function () {
    Route::get('/account/security', [SecurityController::class, 'show'])->name('account.security');
    Route::post('/account/password', [SecurityController::class, 'updatePassword'])->name('account.password.update');
    Route::post('/properties/{property}/favorite', [PropertyController::class, 'toggleFavorite'])->name('properties.favorite.toggle');

    Route::middleware(['must_reset_password'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');

    Route::middleware(['role:buyer'])->group(function () {
        Route::get('/buyer/dashboard', [DashboardController::class, 'buyer'])->name('dashboard.buyer');
    });

    Route::middleware(['role:seller'])->group(function () {
        Route::get('/seller/dashboard', [DashboardController::class, 'seller'])->name('dashboard.seller');
        Route::post('/seller/properties', [PropertyController::class, 'store'])->name('properties.store');
    });

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/agent/dashboard', [AgentPortalController::class, 'overview'])->name('dashboard.agent');
        Route::get('/agent/profile', [AgentPortalController::class, 'profile'])->name('agent.profile');
        Route::put('/agent/profile', [AgentPortalController::class, 'updateProfile'])->name('agent.profile.update');
        Route::get('/agent/leads', [AgentPortalController::class, 'leads'])->name('agent.leads.index');
        Route::post('/agent/leads/{lead}/status', [AgentLeadController::class, 'updateStatus'])->name('agent.leads.status');
        Route::get('/agent/listings', [AgentPortalController::class, 'listings'])->name('agent.listings.index');
        Route::post('/agent/listings', [PropertyController::class, 'store'])->name('agent.listings.store');
        Route::get('/agent/messages', [AgentPortalController::class, 'messages'])->name('agent.messages.index');
        Route::post('/agent/messages/{contact}/status', [AgentPortalController::class, 'updateMessageStatus'])->name('agent.messages.status');
    });

    Route::middleware(['role:seller,agent'])->group(function () {
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    });

    Route::middleware(['role:admin,staff'])->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/leads', [AdminLeadManagementController::class, 'index'])->name('admin.leads.index');
        Route::get('/admin/leads/export/csv', [AdminLeadManagementController::class, 'exportCsv'])->name('admin.leads.export.csv');
        Route::post('/admin/leads/import/csv', [AdminLeadManagementController::class, 'importCsv'])->name('admin.leads.import.csv');
        Route::get('/admin/leads/import/preview', [AdminLeadManagementController::class, 'previewImport'])->name('admin.leads.import.preview');
        Route::post('/admin/leads/import/commit', [AdminLeadManagementController::class, 'commitImport'])->name('admin.leads.import.commit');
        Route::post('/admin/leads/sync/google-sheets', [AdminLeadManagementController::class, 'syncGoogleSheet'])->name('admin.leads.sync.google-sheets');

        Route::resource('admin/blog', \App\Http\Controllers\Admin\BlogController::class)->names([
            'index' => 'admin.blog.index',
            'create' => 'admin.blog.create',
            'store' => 'admin.blog.store',
            'show' => 'admin.blog.show',
            'edit' => 'admin.blog.edit',
            'update' => 'admin.blog.update',
            'destroy' => 'admin.blog.destroy',
        ]);
        Route::resource('admin/testimonials', \App\Http\Controllers\Admin\TestimonialController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.testimonials.index',
                'create' => 'admin.testimonials.create',
                'store' => 'admin.testimonials.store',
                'edit' => 'admin.testimonials.edit',
                'update' => 'admin.testimonials.update',
                'destroy' => 'admin.testimonials.destroy',
            ]);
        Route::post('admin/testimonials/{testimonial}/review', [\App\Http\Controllers\Admin\TestimonialController::class, 'review'])->name('admin.testimonials.review');

        Route::post('admin/leads/{lead}/status', [\App\Http\Controllers\Admin\LeadController::class, 'status'])->name('admin.leads.status');
        Route::post('admin/leads/{lead}/assign', [\App\Http\Controllers\Admin\LeadController::class, 'assign'])->name('admin.leads.assign');
        Route::post('admin/leads/{lead}/activity', [\App\Http\Controllers\Admin\LeadController::class, 'activity'])->name('admin.leads.activity');
        Route::post('admin/properties/{property}/review', [PropertyController::class, 'review'])->name('admin.properties.review');
    });
    });
});
