@php
    $icon = $icon ?? 'spark';
@endphp
@switch($icon)
    @case('chart')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 14V10M8 14V6M12 14V9M16 14V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            <path d="M3 16h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        @break
    @case('rocket')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 16c3-2.5 5-6 5-10a5 5 0 00-10 0c0 4 2 7.5 5 10z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            <circle cx="10" cy="8" r="1.5" fill="currentColor"/>
            <path d="M7 14l-2 2M13 14l2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break
    @case('crown')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 14h14v2H3v-2zM4 14l2.5-7 3.5 4 3-6 3 6 3.5-4L16 14H4z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        </svg>
        @break
    @case('phone')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.5 4h2l1 3-1.5 1a9 9 0 004 4L13 10.5l3 1v2a1.5 1.5 0 01-1.4 1.5C8.8 15.2 4.8 11.2 4.5 7.9A1.5 1.5 0 016.5 4z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        </svg>
        @break
    @case('social')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="10" cy="10" r="6.5" stroke="currentColor" stroke-width="1.5"/>
            <path d="M7 10h6M10 7v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break
    @case('clock')
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="10" cy="10" r="6.5" stroke="currentColor" stroke-width="1.5"/>
            <path d="M10 7v3.5l2.5 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @default
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 3l1.2 3.6L15 8l-3.8 1.4L10 13l-1.2-3.6L5 8l3.8-1.4L10 3z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        </svg>
@endswitch
