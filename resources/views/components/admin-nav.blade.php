@props(['class' => ''])

@php
    $idle = 'rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900';
    $active = 'rounded-lg px-3 py-2 text-sm font-medium bg-indigo-50 text-indigo-700';
    $super = auth()->user()->isSuperAdmin();
@endphp

<nav {{ $attributes->merge(['class' => $class, 'aria-label' => 'Menu administration']) }}>
    <a href="{{ route('admin.dashboard') }}"
       class="{{ request()->routeIs('admin.dashboard') ? $active : $idle }}">
        Tableau de bord
    </a>
    <a href="{{ route('admin.notifications.index') }}"
       class="{{ request()->routeIs('admin.notifications.*') ? $active : $idle }}">
        Mouvements
    </a>
    <a href="{{ route('admin.courses.index') }}"
       class="{{ request()->routeIs('admin.courses.*', 'admin.chapters.*', 'admin.quizzes.*') && ! request()->routeIs('admin.courses.import*') ? $active : $idle }}">
        Cours
    </a>
    <a href="{{ route('admin.courses.import') }}"
       class="{{ request()->routeIs('admin.courses.import*') ? $active : $idle }}">
        Import Word
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="{{ request()->routeIs('admin.users.*') ? $active : $idle }}">
        Utilisateurs
    </a>
    @if ($super)
        <a href="{{ route('admin.faculties.index') }}"
           class="{{ request()->routeIs('admin.faculties.*') ? $active : $idle }}">
            Facultés
        </a>
        <a href="{{ route('admin.options.index') }}"
           class="{{ request()->routeIs('admin.options.*') ? $active : $idle }}">
            Options
        </a>
        <a href="{{ route('admin.promotions.index') }}"
           class="{{ request()->routeIs('admin.promotions.*') ? $active : $idle }}">
            Promotions
        </a>
    @endif
    <a href="{{ route('dashboard') }}"
       class="{{ $idle }}">
        Espace étudiant
    </a>
</nav>
