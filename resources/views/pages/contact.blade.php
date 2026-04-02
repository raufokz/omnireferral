@extends('layouts.app')
@section('content')
<section class="page-hero dashboard-page-hero--agent" style="background-image: linear-gradient(rgba(11, 54, 104, 0.8), rgba(11, 54, 104, 0.8)), url('{{ asset('images/auth/gateway-hero.png') }}'); background-size: cover; background-position: center;">
    <div class="container page-hero__content" data-animate="up">
        <span class="eyebrow" style="color: var(--color-gateway-accent);">Get in Touch</span>
        <h1 style="color: white; font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem;">Let's talk about your next lead, listing, or partnership</h1>
        <p style="color: rgba(255,255,255,0.8); max-width: 700px;">We keep conversations simple, helpful, and fast. Expect a response within one business day.</p>
    </div>
</section>

<section class="section">
    <div class="container grid grid-cols-12 gap-12">
        <!-- Contact Form Column -->
        <div class="col-span-7">
            <div class="cockpit-table-card p-12">
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Send us a message</h2>
                    <p class="text-gray-500">Tell us what you need and we will guide you to the right next step.</p>
                </div>

                <div id="contactSuccess" style="display:none;padding:1.5rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;margin-bottom:2rem;">
                    <strong style="color:#16a34a;display:block;margin-bottom:.35rem;">&#10003; Message sent successfully!</strong>
                    <span style="color:#166534;font-size:.93rem;">Thank you for reaching out. Our team will get back to you within one business day.</span>
                </div>

                <form class="space-y-6" id="contactForm" method="POST" action="{{ route('contact.submit') }}" novalidate>
                    @csrf
                    <div class="grid grid-cols-2 gap-6">
                        <div class="floating-group">
                            <input type="text" name="name" id="contactName" placeholder=" " required autocomplete="name">
                            <label>Full name *</label>
                            <span class="field-error" id="nameError">Please enter your full name.</span>
                        </div>
                        <div class="floating-group">
                            <input type="email" name="email" id="contactEmail" placeholder=" " required autocomplete="email">
                            <label>Email address *</label>
                            <span class="field-error" id="emailError">Please enter a valid email.</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="floating-group">
                            <input type="tel" name="phone" placeholder=" " autocomplete="tel">
                            <label>Phone number</label>
                        </div>
                        <div class="floating-group">
                            <select name="role">
                                <option value="" disabled selected></option>
                                <option>Buyer</option>
                                <option>Seller</option>
                                <option>Agent / Realtor</option>
                                <option>Partner</option>
                            </select>
                            <label>I am a...</label>
                        </div>
                    </div>

                    <div class="floating-group">
                        <textarea name="message" id="contactMessage" rows="5" placeholder=" " required></textarea>
                        <label>Message *</label>
                        <span class="field-error" id="messageError">Please enter your message.</span>
                    </div>

                    <button class="button w-full py-4 text-lg font-bold" style="background: var(--color-gateway-brand-bg); color: white;" type="submit">Send Message</button>
                    <p class="text-center text-[10px] text-gray-400 uppercase tracking-widest mt-4">Safe & Encrypted Communication</p>
                </form>
            </div>
        </div>

        <!-- Info Column -->
        <div class="col-span-5 space-y-8">
            <div class="cockpit-table-card p-10">
                <span class="eyebrow">Connect</span>
                <h3 class="text-2xl font-bold mb-8">Reach OmniReferral</h3>
                
                <div class="space-y-8">
                    <div class="flex gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-900 flex-shrink-0">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Email Support</div>
                            <div class="text-lg font-bold text-gray-900">hello@omnireferral.us</div>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-900 flex-shrink-0">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Direct Line</div>
                            <div class="text-lg font-bold text-gray-900">(800) 555-0147</div>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-900 flex-shrink-0">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Office Hours</div>
                            <div class="text-lg font-bold text-gray-900">Mon–Fri, 9am–6pm ET</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cockpit-table-card overflow-hidden">
                <iframe title="OmniReferral location map" src="https://www.google.com/maps?q=New+York,+NY&output=embed" loading="lazy" style="width:100%;min-height:300px;border:0;"></iframe>
            </div>
        </div>
    </div>
</section>
@endsection
