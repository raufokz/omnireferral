@props([
    'user' => null,
    'profile' => null,
    'size' => 120,
    'class' => '',
    'alt' => '',
])

@php
    $displayName = $alt !== ''
        ? $alt
        : ($user?->publicDisplayName() ?: 'OmniReferral Agent');
    $imageUrl = \App\Support\AgentAvatar::url($user, $profile);
@endphp

<img
    src="{{ $imageUrl }}"
    alt="{{ $displayName }} profile image"
    width="{{ $size }}"
    height="{{ $size }}"
    class="{{ $class }}"
    loading="lazy"
    style="object-fit: cover;"
>
