@extends('layouts.app')

@section('content')
@php($listed = $property->listedByPresentation())
<section class="page-hero property-hero">
    <div class="container property-hero__content">
        <span class="eyebrow">Property Details</span>
        <h1>{{ $property->title }}</h1>
        <p>{{ $property->location }} � {{ $property->zip_code }} � {{ $property->property_type }}</p>
    </div>
</section>

<section class="section property-page">
    <div class="container">
        <div class="property-layout">
            <article class="card-panel property-panel">
                <div class="property-panel__media">
                    <img src="{{ $property->image_url }}" alt="{{ $property->title }} listing image" loading="lazy">
                </div>
                <div class="property-panel__body">
                    <div class="property-panel__headline">
                        <div>
                            <span class="pricing-label">Verified Listing</span>
                            <h2>${{ number_format($property->price) }}</h2>
                            <p>{{ $property->location }}</p>
                        </div>
                        <span class="listing-badge">{{ $property->status }}</span>
                    </div>
                    <div class="listing-card__meta listing-card__meta--pills">
                        <span>{{ $property->beds }} bd</span>
                        <span>{{ $property->baths }} ba</span>
                        <span>{{ number_format($property->sqft) }} sqft</span>
                    </div>
                    <p>
                        This listing is part of the OmniReferral marketplace experience, designed to give buyers and agents a clearer handoff between
                        discovery, qualification, and direct follow-up.
                    </p>
                    <div class="hero__actions">
                        <button type="button" class="button" id="enquiry-modal-trigger">Contact Agent</button>
                        @if($property->realtorProfile)
                            <a href="{{ route('agents.show', $property->realtorProfile) }}" class="button button--ghost-blue">View Agent Profile</a>
                        @else
                            <a href="{{ route('agents.index') }}" class="button button--ghost-blue">Browse Agents</a>
                        @endif
                    </div>
                </div>
            </article>

            <aside class="card-panel property-sidebar">
                <span class="eyebrow">Listed By</span>
                <div class="property-sidebar__agent">
                    @if(!empty($listed['avatar_url']))
                        <img
                            src="{{ $listed['avatar_url'] }}"
                            alt=""
                            class="property-sidebar__agent-img"
                            loading="lazy"
                            decoding="async"
                            width="80"
                            height="80"
                        >
                    @else
                        <span class="listed-by-placeholder listed-by-placeholder--sidebar" role="img" aria-label="{{ $listed['name'] }}">{{ $listed['avatar_initials'] }}</span>
                    @endif
                    <div>
                        <h3>{{ $listed['name'] }}</h3>
                        <p><span class="pd-listed-by-badge">{{ $listed['role_badge'] }}</span></p>
                        @if(!empty($listed['brokerage_name']))
                            <p>{{ $listed['brokerage_name'] }}</p>
                        @endif
                        @if($listed['city_state'] !== '')
                            <span>{{ $listed['city_state'] }}</span>
                        @endif
                    </div>
                </div>
                <ul class="feature-list compact">
                    <li>Fast response routing for qualified opportunities.</li>
                    <li>Lead handoff visibility across buyer, seller, and agent workflows.</li>
                    <li>Support available through onboarding, package upgrades, and marketplace discovery.</li>
                </ul>
                <iframe title="Property location map" src="https://www.google.com/maps?q={{ urlencode($property->zip_code) }}&output=embed" loading="lazy"></iframe>
            </aside>
        </div>

        @if($relatedProperties->isNotEmpty())
            <div class="property-related">
                <div class="section-heading" style="text-align:left; margin: 0 0 2rem;">
                    <span class="eyebrow">More In This Area</span>
                    <h2>Related listings near {{ $property->zip_code }}</h2>
                </div>
                <div class="listing-grid listing-grid--showcase">
                    @foreach($relatedProperties as $relatedProperty)
                        <article class="listing-card listing-card--showcase">
                            <div class="listing-card__media">
                                <img src="{{ $relatedProperty->image_url }}" alt="{{ $relatedProperty->title }}" loading="lazy">
                                <span class="listing-badge">{{ $relatedProperty->status }}</span>
                            </div>
                            <div class="listing-card__body">
                                <div class="listing-card__top">
                                    <strong>${{ number_format($relatedProperty->price) }}</strong>
                                    <span class="listing-type">{{ $relatedProperty->property_type }}</span>
                                </div>
                                <h3>{{ $relatedProperty->title }}</h3>
                                <p class="listing-location">{{ $relatedProperty->location }}</p>
                                <a href="{{ route('properties.show', $relatedProperty) }}" class="button button--ghost-blue">View Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

<!-- Enquiry Modal -->
<div id="enquiry-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Enquire About This Property</h3>
            <button type="button" class="modal-close" id="enquiry-modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-property-title">{{ $property->title }}</p>
            <form id="enquiry-form" method="POST" action="{{ route('properties.enquiry.store', $property) }}">
                @csrf
                <div class="form-group">
                    <label for="enquiry-name">Full Name *</label>
                    <input type="text" id="enquiry-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="enquiry-email">Email Address *</label>
                    <input type="email" id="enquiry-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="enquiry-phone">Phone Number *</label>
                    <input type="tel" id="enquiry-phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="enquiry-type">Enquiry Type *</label>
                    <select id="enquiry-type" name="enquiry_type" required>
                        <option value="">Select enquiry type</option>
                        <option value="request_info">Request More Information</option>
                        <option value="schedule_viewing">Schedule a Viewing</option>
                        <option value="make_offer">Make an Offer</option>
                        <option value="ask_question">Ask a Question</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="enquiry-message">Message *</label>
                    <textarea id="enquiry-message" name="message" rows="4" required>Hi, I'm interested in {{ $property->title }}. Please contact me.</textarea>
                </div>
                <button type="submit" class="button">Send Enquiry</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('enquiry-modal');
    const trigger = document.getElementById('enquiry-modal-trigger');
    const close = document.getElementById('enquiry-modal-close');
    const form = document.getElementById('enquiry-form');

    if (trigger && modal) {
        trigger.addEventListener('click', function() {
            modal.style.display = 'block';
        });
    }

    if (close && modal) {
        close.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    if (modal) {
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    form.innerHTML = '<div style="text-align: center; padding: 20px;"><svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#38a169" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><h3 style="color: #38a169; margin: 15px 0 10px;">Enquiry Sent Successfully!</h3><p style="color: #666;">' + data.message + '</p></div>';
                    setTimeout(() => {
                        modal.style.display = 'none';
                        location.reload();
                    }, 3000);
                } else {
                    alert(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Enquiry';
            });
        });
    }
});
</script>
<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
}
.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.modal-header h3 {
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.modal-property-title {
    font-weight: 600;
    margin-bottom: 15px;
    color: #0b3668;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.form-group textarea {
    resize: vertical;
}
</style>
@endpush
