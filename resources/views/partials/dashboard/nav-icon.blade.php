@props(['icon' => 'dashboard'])

@switch($icon)
    @case('search')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.35-4.35"></path><circle cx="11" cy="11" r="6"></circle></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-8 0"></path><path d="M3.5 20a8.5 8.5 0 0 1 17 0"></path><path d="M18 8.5a3 3 0 0 1 2.6 4.5"></path></svg>
        @break
    @case('properties')
    @case('marketplace')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11.5 12 4l9 7.5"></path><path d="M5.5 10.5V20h13v-9.5"></path><path d="M9.5 20v-5h5v5"></path></svg>
        @break
    @case('listings')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path><path d="M17 16l3 2-3 2z"></path></svg>
        @break
    @case('enquiries')
    @case('messages')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H9l-5 3v-3H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"></path><path d="M7 10h10"></path><path d="M7 14h6"></path></svg>
        @break
    @case('leads')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"></path><path d="M8 9h8"></path><path d="M8 13h5"></path><path d="m16 16 2 2 3-4"></path></svg>
        @break
    @case('requests')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4h10v4H7z"></path><path d="M6 8h12v12H6z"></path><path d="M9 13h6"></path><path d="M9 16h4"></path></svg>
        @break
    @case('saved')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h12v17l-6-3.5L6 21z"></path></svg>
        @break
    @case('profile')
    @case('agent')
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 20a7.5 7.5 0 0 1 15 0"></path></svg>
        @break
    @case('security')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.4 2.9 8.3 7 9.5 4.1-1.2 7-5.1 7-9.5V6z"></path><path d="M9.5 12.5 11.4 14.4 15 10.6"></path></svg>
        @break
    @case('content')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5z"></path><path d="M8 8h8"></path><path d="M8 12h8"></path><path d="M8 16h5"></path></svg>
        @break
    @case('audit')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18H6z"></path><path d="M9 7h6"></path><path d="M9 11h6"></path><path d="M9 15h3"></path></svg>
        @break
    @case('operations')
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M4 12h16"></path><path d="M4 17h16"></path><circle cx="8" cy="7" r="1.5"></circle><circle cx="16" cy="12" r="1.5"></circle><circle cx="10" cy="17" r="1.5"></circle></svg>
        @break
    @default
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h7v7H4z"></path><path d="M13 5h7v7h-7z"></path><path d="M4 14h7v5H4z"></path><path d="M13 14h7v5h-7z"></path></svg>
@endswitch
