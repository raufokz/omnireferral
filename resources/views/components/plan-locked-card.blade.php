@props(['title', 'description' => null, 'requiredPlanLabel' => null])

<div {{ $attributes->merge(['class' => 'capacity-notice capacity-notice--info']) }} style="align-items:flex-start;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
        <rect x="3" y="11" width="18" height="10" rx="2"/>
        <circle cx="12" cy="16" r="1"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    <span>
        <strong>{{ $title }}</strong>
        @if($description)
            — {{ $description }}
        @endif
        @if($requiredPlanLabel)
            <span style="display:block; font-size:0.8rem; margin-top:0.2rem;">
                &#128274; Available in {{ $requiredPlanLabel }}.
                <a href="{{ route('pricing') }}" style="font-weight:700; color:inherit;">Upgrade your plan</a>
            </span>
        @endif
    </span>
</div>
