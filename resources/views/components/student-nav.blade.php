@props(['class' => ''])

@php
    $idle = 'rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900';
    $active = 'rounded-lg px-3 py-2 text-sm font-medium bg-indigo-50 text-indigo-700';
@endphp

<nav {{ $attributes->merge(['class' => $class, 'aria-label' => 'Menu étudiant']) }}>
    <a href="{{ route('dashboard') }}"
       class="{{ request()->routeIs('dashboard') ? $active : $idle }}">
        Accueil
    </a>
    <a href="{{ route('courses.index') }}"
       class="{{ request()->routeIs('courses.*', 'chapters.*', 'quizzes.*') ? $active : $idle }}">
        Mes cours
    </a>
    <a href="{{ route('progress') }}"
       class="{{ request()->routeIs('progress') ? $active : $idle }}">
        Ma progression
    </a>
    <a href="{{ route('profile') }}"
       class="{{ request()->routeIs('profile') ? $active : $idle }}">
        Mon profil
    </a>
    @if (auth()->user()?->isPrivileged())
        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('admin.*') ? $active : $idle }}">
            Administration
        </a>
    @endif
</nav>
