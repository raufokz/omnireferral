<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GoHighLevelController as AdminGoHighLevelController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\LeadManagementController as AdminLeadManagementController;
use App\Http\Controllers\Admin\PlatformSearchController;
use App\Http\Controllers\Admin\PropertyManagementController as AdminPropertyManagementController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PricingPlanController as AdminPricingPlanController;
use App\Http\Controllers\Admin\StaffAgentProfileController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\DataExportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserModerationController;
use App\Http\Controllers\Admin\WebhookEventController as AdminWebhookEventController;
use App\Http\Controllers\Admin\EmailToolsController as AdminEmailToolsController;
use App\Http\Controllers\Admin\MailSettingsController as AdminMailSettingsController;
use App\Http\Controllers\Agent\LeadController as AgentLeadController;
use App\Http\Controllers\Agent\PortalController as AgentPortalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Dashboard\EnquiryController as DashboardEnquiryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestFavouritesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PropertyCommentController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RealtorController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Webhooks\GoHighLevelWebhookController;
use App\Http\Controllers\Webhooks\GoHighLevelEventWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Models\Property;
use App\Models\RealtorProfile;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', function () {
    $xml = Cache::remember('public:sitemap.xml', now()->addHours(6), function () {
        $properties = Property::query()
            ->marketplaceVisible()
            ->latest('updated_at')
            ->take(500)
            ->get(['slug', 'updated_at']);

        $agents = RealtorProfile::query()
            ->publicDirectory()
            ->latest('updated_at')
            ->take(500)
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', [
                'properties' => $properties,
                'agents' => $agents,
            ])
            ->header('Content-Type', 'text/xml')
            ->getContent();
    });

    return response($xml, 200)->header('Content-Type', 'text/xml');
})->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

/**
 * Pricing detail URLs use the Starter, Growth, and Elite plan names.
 */
Route::redirect('/pricing/starter-lead', '/packages/starter-leads/checkout', 301)->name('pricing.starter-lead');
Route::redirect('/pricing/growth-lead', '/packages/growth-leads/checkout', 301)->name('pricing.growth-lead');
Route::redirect('/pricing/elite-lead', '/packages/elite-leads/checkout', 301)->name('pricing.elite-lead');
Route::redirect('/pricing/quick-lead', '/pricing/starter-lead', 301);
Route::redirect('/pricing/power-lead', '/pricing/growth-lead', 301);
Route::redirect('/pricing/prime-lead', '/pricing/elite-lead', 301);

Route::get('/packages/{packageSlug}/checkout', [PricingController::class, 'checkout'])->name('packages.checkout');
Route::post('/packages/{packageSlug}/stripe-checkout', [PricingController::class, 'stripeCheckout'])->name('packages.stripe-checkout');
Route::get('/packages/{packageSlug}/success', [PricingController::class, 'success'])->name('packages.success');
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
Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:reviews')->name('reviews.store');
Route::get('/careers', [HomeController::class, 'careers'])->name('careers');
Route::get('/surveys-campaigns', [HomeController::class, 'surveys'])->name('surveys');
Route::get('/listings', [HomeController::class, 'listings'])->name('listings');
Route::get('/listings/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/my-favourites', [GuestFavouritesController::class, 'index'])->name('guest.favourites');
Route::post('/properties/{property}/favorite', [PropertyController::class, 'toggleFavorite'])
    ->middleware('throttle:property-favorite')
    ->name('properties.favorite.toggle');
Route::post('/properties/{property}/comments', [PropertyCommentController::class, 'store'])
    ->middleware('throttle:property-comments')
    ->name('properties.comments.store');
Route::get('/onboarding/{role}', function (string $role): RedirectResponse {
    abort_unless(in_array($role, ['buyer', 'seller', 'agent', 'admin'], true), 404);

    $dashboardRoute = match ($role) {
        'buyer' => route('dashboard.buyer'),
        'seller' => route('dashboard.seller'),
        'admin' => route('admin.dashboard'),
        'agent' => route('dashboard.agent'),
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
Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:contact')->name('contact.submit');
Route::post('/lead-store', [LeadController::class, 'store'])->middleware('throttle:leads')->name('leads.store');
Route::post('/properties/{property}/enquiry', [PropertyController::class, 'storeEnquiry'])
    ->middleware('throttle:property-enquiry')
    ->name('properties.enquiry.store');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/agents', [RealtorController::class, 'index'])->name('agents.index');
Route::post('/agents', [RealtorController::class, 'submitAgentProfile'])
    ->middleware('throttle:auth-register')
    ->name('agents.submit');
Route::get('/agents/{location}', [RealtorController::class, 'location'])
    ->where('location', '[a-z0-9\-]+')
    ->name('agents.location');
Route::get('/agent/{agent}', [RealtorController::class, 'profile'])
    ->where('agent', '(?!dashboard|profile|leads|listings|messages)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('agents.profile');
Route::get('/agent/{agent}/preview', [RealtorController::class, 'preview'])
    ->where('agent', '(?!dashboard|profile|leads|listings|messages)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('agents.preview');
Route::post('/agent/{agent}/inquiry', [RealtorController::class, 'inquiry'])
    ->where('agent', '(?!dashboard|profile|leads|listings|messages)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->middleware('throttle:contact')
    ->name('agents.inquiry');
Route::redirect('/join-as-agent', '/agents', 301);
Route::redirect('/join-as-agent/success', '/agents', 301);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:auth-password-reset')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Secure one-time password-setup links emailed after GoHighLevel onboarding (24h expiry, single use).
Route::get('/password/setup/{token}', [PasswordSetupController::class, 'show'])->name('password.setup');
Route::post('/password/setup/{token}', [PasswordSetupController::class, 'store'])
    ->middleware('throttle:auth-password-reset')
    ->name('password.setup.store');

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

Route::post('/webhooks/gohighlevel/events', GoHighLevelEventWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware('throttle:30,1')
    ->name('webhooks.gohighlevel.events');

Route::middleware(['auth', 'active.account'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'active.account', 'must_reset_password'])->group(function () {
    Route::get('/account/profile', [ProfileController::class, 'show'])->name('account.profile');
    Route::put('/account/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:account-profile')
        ->name('account.profile.update');
    Route::get('/account/security', [SecurityController::class, 'show'])->name('account.security');
    Route::post('/account/password', [SecurityController::class, 'updatePassword'])->name('account.password.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/affiliate', [DashboardController::class, 'affiliate'])->name('dashboard.affiliate');

    Route::middleware(['role:agent'])->group(function () {
        Route::get('/dashboard/enquiries', [DashboardEnquiryController::class, 'index'])->name('dashboard.enquiries.index');
        Route::get('/dashboard/enquiries/{enquiry}', [DashboardEnquiryController::class, 'show'])->name('dashboard.enquiries.show');
        Route::post('/dashboard/enquiries/{enquiry}/replies', [DashboardEnquiryController::class, 'storeReply'])
            ->middleware('throttle:enquiry-replies')
            ->name('dashboard.enquiries.replies.store');
        Route::patch('/dashboard/enquiries/{enquiry}/status', [DashboardEnquiryController::class, 'updateStatus'])
            ->name('dashboard.enquiries.status');
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

    Route::middleware(['role:staff'])->group(function () {
        Route::get('/staff/dashboard', [AdminDashboardController::class, 'staff'])->name('staff.dashboard');
    });

    Route::middleware(['can:super-admin.access'])->group(function () {
        Route::get('/super-admin/dashboard', [AdminDashboardController::class, 'superAdmin'])->name('super-admin.dashboard');
    });

    Route::middleware(['can:admin.access'])->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/exports', [DataExportController::class, 'index'])->name('admin.exports.index');
        Route::get('/admin/exports/{export}/download', [DataExportController::class, 'download'])->name('admin.exports.download');
        Route::get('/admin/search', PlatformSearchController::class)->name('admin.search');
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/export/csv', [UserManagementController::class, 'exportCsv'])->name('admin.users.export.csv');
        Route::get('/admin/users/export/xlsx', [UserManagementController::class, 'exportXlsx'])->name('admin.users.export.xlsx');
        Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::get('/admin/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');
        Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::patch('/admin/users/{user}/quick', [UserManagementController::class, 'quickUpdate'])->name('admin.users.quick-update');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/enquiries', [AdminEnquiryController::class, 'index'])->name('admin.enquiries.index');
        Route::get('/admin/enquiries/export/csv', [AdminEnquiryController::class, 'exportCsv'])->name('admin.enquiries.export.csv');
        Route::get('/admin/enquiries/export/xlsx', [AdminEnquiryController::class, 'exportXlsx'])->name('admin.enquiries.export.xlsx');
        Route::get('/admin/enquiries/{enquiry}', [AdminEnquiryController::class, 'show'])->name('admin.enquiries.show');
        Route::post('/admin/enquiries/{enquiry}/replies', [AdminEnquiryController::class, 'storeReply'])
            ->middleware('throttle:enquiry-replies')
            ->name('admin.enquiries.replies.store');
        Route::patch('/admin/enquiries/{enquiry}/status', [AdminEnquiryController::class, 'updateStatus'])->name('admin.enquiries.status');
        Route::get('/admin/activity', [ActivityLogController::class, 'index'])->name('admin.activity.index');
        Route::get('/admin/leads', [AdminLeadManagementController::class, 'index'])->name('admin.leads.index');
        Route::get('/admin/leads/export/csv', [AdminLeadManagementController::class, 'exportCsv'])->name('admin.leads.export.csv');
        Route::post('/admin/leads/import/csv', [AdminLeadManagementController::class, 'importCsv'])->name('admin.leads.import.csv');
        Route::get('/admin/leads/import/preview', [AdminLeadManagementController::class, 'previewImport'])->name('admin.leads.import.preview');
        Route::post('/admin/leads/import/commit', [AdminLeadManagementController::class, 'commitImport'])->name('admin.leads.import.commit');
        Route::post('/admin/leads/sync/google-sheets', [AdminLeadManagementController::class, 'syncGoogleSheet'])->name('admin.leads.sync.google-sheets');
        Route::resource('admin/properties', AdminPropertyManagementController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.properties.index',
                'create' => 'admin.properties.create',
                'store' => 'admin.properties.store',
                'edit' => 'admin.properties.edit',
                'update' => 'admin.properties.update',
                'destroy' => 'admin.properties.destroy',
            ]);

        Route::resource('admin/blog', App\Http\Controllers\Admin\BlogController::class)->names([
            'index' => 'admin.blog.index',
            'create' => 'admin.blog.create',
            'store' => 'admin.blog.store',
            'show' => 'admin.blog.show',
            'edit' => 'admin.blog.edit',
            'update' => 'admin.blog.update',
            'destroy' => 'admin.blog.destroy',
        ]);
        Route::resource('admin/testimonials', TestimonialController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.testimonials.index',
                'create' => 'admin.testimonials.create',
                'store' => 'admin.testimonials.store',
                'edit' => 'admin.testimonials.edit',
                'update' => 'admin.testimonials.update',
                'destroy' => 'admin.testimonials.destroy',
            ]);
        Route::post('admin/testimonials/{testimonial}/review', [TestimonialController::class, 'review'])->name('admin.testimonials.review');

        Route::resource('admin/packages', AdminPackageController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.packages.index',
                'create' => 'admin.packages.create',
                'store' => 'admin.packages.store',
                'edit' => 'admin.packages.edit',
                'update' => 'admin.packages.update',
                'destroy' => 'admin.packages.destroy',
            ]);

        Route::resource('admin/pricing-plans', AdminPricingPlanController::class)
            ->except(['show'])
            ->names([
                'index' => 'admin.pricing-plans.index',
                'create' => 'admin.pricing-plans.create',
                'store' => 'admin.pricing-plans.store',
                'edit' => 'admin.pricing-plans.edit',
                'update' => 'admin.pricing-plans.update',
                'destroy' => 'admin.pricing-plans.destroy',
            ]);

        Route::get('admin/webhook-events', [AdminWebhookEventController::class, 'index'])->name('admin.webhook-events.index');
        Route::get('admin/webhook-events/{webhookEvent}', [AdminWebhookEventController::class, 'show'])->name('admin.webhook-events.show');

        // Email & Auth diagnostics — test email, SMTP connection test, delivery + auth logs.
        Route::get('admin/email', [AdminEmailToolsController::class, 'index'])->name('admin.email.index');
        Route::post('admin/email/test', [AdminEmailToolsController::class, 'sendTest'])->name('admin.email.test');
        Route::post('admin/email/smtp-test', [AdminEmailToolsController::class, 'smtpTest'])->name('admin.email.smtp-test');

        // Mail settings — SMTP / mail driver config, test tools
        Route::get('admin/mail-settings', [AdminMailSettingsController::class, 'index'])->name('admin.mail-settings.index');
        Route::put('admin/mail-settings', [AdminMailSettingsController::class, 'update'])->name('admin.mail-settings.update');
        Route::post('admin/mail-settings/test-connection', [AdminMailSettingsController::class, 'testConnection'])->name('admin.mail-settings.test-connection');
        Route::post('admin/mail-settings/test', [AdminMailSettingsController::class, 'sendTest'])->name('admin.mail-settings.test');

        // GoHighLevel control panel — view for all admins, write for super-admin only
        Route::get('admin/gohighlevel', [AdminGoHighLevelController::class, 'index'])->name('admin.ghl.index');
        Route::get('admin/gohighlevel/settings', [AdminGoHighLevelController::class, 'settings'])->name('admin.ghl.settings');
        Route::put('admin/gohighlevel/settings', [AdminGoHighLevelController::class, 'updateSettings'])->name('admin.ghl.settings.update');
        Route::get('admin/gohighlevel/mappings', [AdminGoHighLevelController::class, 'mappings'])->name('admin.ghl.mappings');
        Route::post('admin/gohighlevel/mappings', [AdminGoHighLevelController::class, 'addMapping'])->name('admin.ghl.mappings.add');
        Route::post('admin/gohighlevel/mappings/{mapping}/toggle', [AdminGoHighLevelController::class, 'toggleMapping'])->name('admin.ghl.mappings.toggle');
        Route::delete('admin/gohighlevel/mappings/{mapping}', [AdminGoHighLevelController::class, 'deleteMapping'])->name('admin.ghl.mappings.delete');
        Route::get('admin/gohighlevel/debug', [AdminGoHighLevelController::class, 'debug'])->name('admin.ghl.debug');
        Route::get('admin/gohighlevel/logs', [AdminGoHighLevelController::class, 'logs'])->name('admin.ghl.logs');
        Route::post('admin/gohighlevel/logs/{webhookEventId}/retry', [AdminGoHighLevelController::class, 'retrySync'])->name('admin.ghl.retry');
        Route::post('admin/gohighlevel/logs/{onboardingLogId}/resend-email', [AdminGoHighLevelController::class, 'resendPortalAccessEmail'])->name('admin.ghl.resend-email');
        Route::get('admin/gohighlevel/testing', [AdminGoHighLevelController::class, 'testing'])->name('admin.ghl.testing');
        Route::post('admin/gohighlevel/test/connection', [AdminGoHighLevelController::class, 'testConnection'])->name('admin.ghl.test.connection');
        Route::post('admin/gohighlevel/test/webhook', [AdminGoHighLevelController::class, 'testWebhook'])->name('admin.ghl.test.webhook');
        Route::post('admin/gohighlevel/test/sync', [AdminGoHighLevelController::class, 'testSync'])->name('admin.ghl.test.sync');

        Route::post('admin/leads/{lead}/status', [App\Http\Controllers\Admin\LeadController::class, 'status'])->name('admin.leads.status');
        Route::post('admin/leads/{lead}/assign', [App\Http\Controllers\Admin\LeadController::class, 'assign'])->name('admin.leads.assign');
        Route::post('admin/leads/{lead}/activity', [App\Http\Controllers\Admin\LeadController::class, 'activity'])->name('admin.leads.activity');
        Route::post('admin/properties/{property}/review', [PropertyController::class, 'review'])->name('admin.properties.review');
        Route::post('admin/users/{user}/review', [UserModerationController::class, 'review'])->name('admin.users.review');

        Route::get('admin/agents/manage', [StaffAgentProfileController::class, 'index'])->name('admin.agents.manage');
        Route::get('admin/agents/import', [StaffAgentProfileController::class, 'create'])->name('admin.agents.import');
        Route::post('admin/agents/import', [StaffAgentProfileController::class, 'store'])->name('admin.agents.import.store');
        Route::get('admin/agent-profiles', [StaffAgentProfileController::class, 'index'])->name('admin.agent-profiles.index');
        Route::get('admin/agent-profiles/create', [StaffAgentProfileController::class, 'create'])->name('admin.agent-profiles.create');
        Route::post('admin/agent-profiles', [StaffAgentProfileController::class, 'store'])->name('admin.agent-profiles.store');
        Route::get('admin/agent-profiles/{agentProfile}', [StaffAgentProfileController::class, 'show'])->name('admin.agent-profiles.show');
        Route::put('admin/agent-profiles/{agentProfile}', [StaffAgentProfileController::class, 'update'])->name('admin.agent-profiles.update');
        Route::post('admin/agent-profiles/{agentProfile}/feature', [StaffAgentProfileController::class, 'feature'])->name('admin.agent-profiles.feature');
        Route::post('admin/agent-profiles/{agentProfile}/publish', [StaffAgentProfileController::class, 'publish'])->name('admin.agent-profiles.publish');
        Route::post('admin/agent-profiles/{agentProfile}/suspend', [StaffAgentProfileController::class, 'suspend'])->name('admin.agent-profiles.suspend');
    });
});
