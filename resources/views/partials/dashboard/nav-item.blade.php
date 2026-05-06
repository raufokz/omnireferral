@props([
    'isNavItemActive',
    'item',
    'level' => 0,
])

@php
    $children = collect($item['children'] ?? [])->values();
    $hasChildren = $children->isNotEmpty();
    $isActive = $isNavItemActive($item);
    $itemId = 'dashboard-nav-'.\Illuminate\Support\Str::slug(($item['label'] ?? 'item').'-'.$level);
    $icon = $item['icon'] ?? 'dashboard';
@endphp

@if ($hasChildren)
    <div class="dashboard-shell__nav-group {{ $isActive ? 'is-open is-active' : '' }}" data-dashboard-subnav>
        <button
            type="button"
            class="dashboard-shell__nav-trigger {{ $isActive ? 'is-active' : '' }}"
            data-dashboard-subnav-toggle
            aria-expanded="{{ $isActive ? 'true' : 'false' }}"
            aria-controls="{{ $itemId }}"
        >
            <span class="dashboard-shell__nav-icon">
                @include('partials.dashboard.nav-icon', ['icon' => $icon])
            </span>
            <span class="dashboard-shell__nav-label">{{ $item['label'] }}</span>
            <span class="dashboard-shell__nav-caret" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"></path>
                </svg>
            </span>
        </button>

        <div
            class="dashboard-shell__subnav"
            id="{{ $itemId }}"
            data-dashboard-subnav-panel
            aria-hidden="{{ $isActive ? 'false' : 'true' }}"
            @if(! $isActive) inert @endif
        >
            <div class="dashboard-shell__subnav-inner">
                @foreach ($children as $child)
                    @include('partials.dashboard.nav-item', [
                        'item' => $child,
                        'isNavItemActive' => $isNavItemActive,
                        'level' => $level + 1,
                    ])
                @endforeach
            </div>
        </div>
    </div>
@else
    <a
        href="{{ $item['route'] }}"
        class="dashboard-shell__nav-link dashboard-shell__nav-link--level-{{ $level }} {{ $isActive ? 'is-active' : '' }}"
        @if($isActive) aria-current="page" @endif
    >
        <span class="dashboard-shell__nav-icon">
            @include('partials.dashboard.nav-icon', ['icon' => $icon])
        </span>
        <span class="dashboard-shell__nav-label">{{ $item['label'] }}</span>
    </a>
@endif
