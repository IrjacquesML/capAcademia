@props([
    'href' => null,
    'size' => 'sm',
    'light' => false,
])

@php
    $imgClass = $size === 'lg' ? 'h-16 w-auto sm:h-20' : 'h-11 w-auto';
    $nameClass = $light
        ? 'text-sm font-medium text-indigo-100'
        : 'text-base font-semibold text-indigo-700';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class('inline-flex shrink-0 items-center gap-2') }}>
@else
    <div {{ $attributes->class('inline-flex items-center gap-2') }}>
@endif
        <img src="{{ asset('images/logo-hec-kin.jpg') }}"
             alt="HEC-KIN"
             class="{{ $imgClass }} object-contain">
        <span class="{{ $nameClass }}">
            CapAcademia
            {{ $slot }}
        </span>
@if ($href)
    </a>
@else
    </div>
@endif
