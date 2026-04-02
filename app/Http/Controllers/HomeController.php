<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $realtors = RealtorProfile::with('user')->get();

        $testimonialQuotes = [
            'The ISA team does not hand us cold names. They hand us lead-ready conversations with budget, intent, and location already clarified.',
            'OmniReferral gave our brokerage a much cleaner conversion process. The lead notes feel specific, the routing feels intentional, and our first calls go better.',
            'Prime leads changed how quickly we can move from inquiry to appointment. The level of context makes the entire sales conversation warmer from the start.',
            'We finally have a platform that feels as polished as the service we promise our clients. Our team trusts the lead quality and the follow-up rhythm.',
            'The dashboard, package structure, and onboarding flow make OmniReferral feel like a real operating system for lead generation, not just another vendor.',
            'Between the ISA qualification and the sales support, our agents spend less time filtering noise and more time working high-intent opportunities.',
        ];

        $testimonials = $realtors
            ->take(12)
            ->values()
            ->map(function ($realtor, $index) use ($testimonialQuotes) {
                return [
                    'path' => $realtor->headshot,
                    'name' => $realtor->user->name,
                    'role' => 'Realtor · ' . ($realtor->brokerage_name ?: 'OmniReferral Partner Network'),
                    'location' => $realtor->city . ', ' . $realtor->state,
                    'quote' => $testimonialQuotes[$index % count($testimonialQuotes)],
                ];
            });

        $partnerLogos = collect(File::files(public_path('images/companies-logos')))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp']))
            ->sortBy(fn ($file) => $file->getFilename())
            ->take(10)
            ->values()
            ->map(function ($file) {
                $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $readable = str($name)
                    ->replace(['-150x150', '-300x137', '-300x189', '-e1742235865385'], '')
                    ->replace(['-', '_'], ' ')
                    ->title()
                    ->toString();

                return [
                    'name' => $readable,
                    'path' => 'images/companies-logos/' . $file->getFilename(),
                ];
            });

        return view('home', [
            'packages' => Package::active()->leadPlans()->orderBy('sort_order')->orderBy('one_time_price')->get(),
            'assistantPackages' => Package::active()->assistantPlans()->orderBy('sort_order')->orderBy('monthly_price')->get(),
            'testimonials' => $testimonials,
            'partners' => Partner::orderBy('sort_order')->get(),
            'partnerLogos' => $partnerLogos,
            'realtors' => $realtors->take(12),
            'blogs' => Blog::latest()->take(3)->get(),
            'team' => TeamMember::latest()->get(),
            'properties' => Property::with('realtorProfile.user')->latest()->take(6)->get(),
            'meta' => [
                'title' => 'OmniReferral | Premium Real Estate Lead Generation for High-Performing Agents',
                'description' => 'OmniReferral helps real estate teams grow with ISA-qualified buyer and seller leads, premium package options, and a polished referral workflow built for conversion.',
            ],
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'team' => TeamMember::latest()->get(),
            'meta' => [
                'title' => 'About OmniReferral | Human-Centered Real Estate Referral Platform',
                'description' => 'Learn how OmniReferral helps buyers, sellers, agents, and partner teams work together through smarter lead qualification and modern real estate technology.',
            ],
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', ['meta' => ['title' => 'FAQ | OmniReferral', 'description' => 'Answers to common questions about OmniReferral packages, leads, dashboards, and services.']]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', ['meta' => ['title' => 'Privacy Policy | OmniReferral', 'description' => 'Read the OmniReferral privacy policy and learn how your data is handled.']]);
    }

    public function terms(): View
    {
        return view('pages.terms', ['meta' => ['title' => 'Terms of Service | OmniReferral', 'description' => 'Review the terms of service for using OmniReferral.']]);
    }

    public function resources(): View
    {
        return view('pages.resources', ['blogs' => Blog::latest()->take(6)->get(), 'meta' => ['title' => 'Resources & Guides | OmniReferral', 'description' => 'Explore OmniReferral resources, guides, and practical content for buyers, sellers, and real estate agents.']]);
    }

    public function news(): View
    {
        return view('pages.news', ['blogs' => Blog::latest()->get(), 'meta' => ['title' => 'News & Updates | OmniReferral', 'description' => 'Latest OmniReferral updates, campaign news, and growth announcements.']]);
    }

    public function reviews(): View
    {
        return view('pages.reviews', ['testimonials' => Testimonial::latest()->get(), 'meta' => ['title' => 'Reviews | OmniReferral', 'description' => 'Read reviews from agents and partner teams who use OmniReferral to grow their business.']]);
    }

    public function careers(): View
    {
        return view('pages.careers', ['meta' => ['title' => 'Careers | OmniReferral', 'description' => 'Join the OmniReferral team across ISA, sales, marketing, and web development roles.']]);
    }

    public function surveys(): View
    {
        return view('pages.surveys', ['meta' => ['title' => 'Surveys & Campaigns | OmniReferral', 'description' => 'Collect feedback, capture leads, and launch automated outreach through OmniReferral surveys and campaigns.']]);
    }

    public function listings(): View
    {
        return view('pages.listings', [
            'properties' => Property::with('realtorProfile.user')->latest()->get(),
            'meta' => [
                'title' => 'Property Listings | OmniReferral',
                'description' => 'Browse OmniReferral property listings by zip code, property type, and price range.',
            ],
        ]);
    }

    public function onboarding(string $role): View
    {
        abort_unless(in_array($role, ['buyer', 'seller', 'agent', 'admin']), 404);

        $dashboardRoute = match ($role) {
            'buyer' => route('dashboard.buyer'),
            'seller' => route('dashboard.seller'),
            'admin' => route('admin.dashboard'),
            default => route('dashboard.agent'),
        };

        return view('pages.onboarding', [
            'role' => $role,
            'dashboardRoute' => $dashboardRoute,
            'onboardingFormSrc' => 'https://api.leadconnectorhq.com/widget/form/1KzI6i1lZ4rDTDZF02ot',
            'meta' => [
                'title' => ucfirst($role) . ' Onboarding | OmniReferral',
                'description' => 'Complete your OmniReferral onboarding and get your dashboard ready.',
            ],
        ]);
    }

    public function clientFormSubmission(): View
    {
        $role = request()->string('role')->lower()->value() ?: 'agent';
        abort_unless(in_array($role, ['buyer', 'seller', 'agent']), 404);

        $dashboardRoute = match ($role) {
            'buyer' => route('dashboard.buyer'),
            'seller' => route('dashboard.seller'),
            default => route('dashboard.agent'),
        };

        return view('pages.client-form-submission7', [
            'role' => $role,
            'dashboardRoute' => $dashboardRoute,
            'onboardingFormSrc' => 'https://api.leadconnectorhq.com/widget/form/1KzI6i1lZ4rDTDZF02ot',
            'meta' => [
                'title' => 'Complete Your Onboarding | OmniReferral',
                'description' => 'Finish your OmniReferral onboarding after package selection and payment.',
            ],
        ]);
    }
}




