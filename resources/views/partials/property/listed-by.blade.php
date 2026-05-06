@props([
    'property',
    'variant' => 'card',
    'showHeading' => false,
])

@php
    $lb = $property->listedByPresentation();
    $avatarSize = match ($variant) {
        'detail' => 72,
        'property-card' => 40,
        default => 36,
    };
    $placeholderClass = $variant === 'detail' ? 'listed-by-placeholder listed-by-placeholder--md' : 'listed-by-placeholder listed-by-placeholder--sm';
    $avatarUrl = $lb['avatar_url'];
    $showAvatarImage = ! empty($avatarUrl);
@endphp

<div
    class="listed-by-inline listed-by-inline--{{ $variant }}"
    @if($property->listed_by_id) data-listed-by-id="{{ $property->listed_by_id }}" @endif
>
    <span class="listed-by-inline__avatar-wrap">
        @if($showAvatarImage)
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $lb['name'] }} profile photo"
                class="listed-by-inline__avatar"
                width="{{ $avatarSize }}"
                height="{{ $avatarSize }}"
                loading="lazy"
                decoding="async"
                onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
            >
        @endif
        <span
            class="{{ $placeholderClass }}"
            role="img"
            aria-label="{{ $lb['name'] }}"
            @if($showAvatarImage) hidden @endif
        >{{ $lb['avatar_initials'] }}</span>
    </span>
    <span class="listed-by-inline__meta">
        @if($showHeading)
            <small class="listed-by-inline__heading">Listed by</small>
        @endif
        <span class="listed-by-inline__row">
            <span class="listed-by-inline__name" title="{{ $lb['name'] }}">{{ $lb['name'] }}</span>
            <span class="listed-by-inline__badge">{{ $lb['role_badge'] }}</span>
        </span>
    </span>
</div>
