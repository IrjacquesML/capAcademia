@php
    $item = 'flex min-h-[3.25rem] flex-col items-center justify-center gap-0.5 px-1 py-1 text-[11px] font-medium';
    $idle = $item.' text-slate-500';
    $active = $item.' text-indigo-700';
@endphp

<nav class="tabbar z-40 border-t border-slate-200 bg-white/95 backdrop-blur md:hidden"
     style="padding-bottom: env(safe-area-inset-bottom, 0px)"
     aria-label="Navigation principale">
    <div class="mx-auto grid max-w-6xl grid-cols-4">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $active : $idle }}">
            <x-icon name="home" class="h-6 w-6" />
            Accueil
        </a>
        <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*', 'chapters.*', 'quizzes.*') ? $active : $idle }}">
            <x-icon name="book" class="h-6 w-6" />
            Mes cours
        </a>
        <a href="{{ route('progress') }}" class="{{ request()->routeIs('progress') ? $active : $idle }}">
            <x-icon name="chart" class="h-6 w-6" />
            Progression
        </a>
        <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? $active : $idle }}">
            <x-icon name="user" class="h-6 w-6" />
            Profil
        </a>
    </div>
</nav>
