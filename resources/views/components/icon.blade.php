@props(['name', 'class' => 'h-6 w-6'])

@php
    $paths = [
        'home' => 'M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z',
        'book' => 'M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2V5zm4 2h8M8 11h8',
        'chart' => 'M4 19h16M7 16V9m5 7V5m5 11v-6',
        'user' => 'M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 9a7 7 0 0 1 14 0',
        'grid' => 'M4 4h7v7H4zm9 0h7v7h-7zM4 13h7v7H4zm9 0h7v7h-7z',
        'users' => 'M16 11a3 3 0 1 0-3-3 3 3 0 0 0 3 3zM8 12a3 3 0 1 0-3-3 3 3 0 0 0 3 3zm8 2c2.7 0 5 1.5 5 3.5V20H11v-2.5c0-2 2.3-3.5 5-3.5zM8 14c-2.7 0-5 1.5-5 3.5V20h5',
        'more' => 'M6 12a1.5 1.5 0 1 1-1.5-1.5A1.5 1.5 0 0 1 6 12zm7.5 0A1.5 1.5 0 1 1 12 10.5 1.5 1.5 0 0 1 13.5 12zm7.5 0A1.5 1.5 0 1 1 19.5 10.5 1.5 1.5 0 0 1 21 12z',
        'logout' => 'M10 6H6v12h4m4-9 4 3-4 3m4-3H10',
        'import' => 'M12 4v10m0 0 4-4m-4 4-4-4M5 18h14',
        'plus' => 'M12 5v14M5 12h14',
        'download' => 'M12 4v12m0 0 4-4m-4 4-4-4M5 20h14',
        'file' => 'M7 3h8l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm8 0v5h5',
        'building' => 'M4 21h16M6 21V8l6-4 6 4v13M9 21v-5h6v5M10 11h.01M14 11h.01M10 15h.01M14 15h.01',
        'play' => 'M8 5.5v13l11-6.5z',
        'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9',
    ];
@endphp

<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
    <path d="{{ $paths[$name] ?? $paths['more'] }}"/>
</svg>
