@php
    $item = 'flex min-h-[3.25rem] flex-col items-center justify-center gap-0.5 px-1 py-1 text-[11px] font-medium';
    $idle = $item.' text-slate-500';
    $active = $item.' text-indigo-700';
    $super = auth()->user()->isSuperAdmin();
    $moreOpen = request()->routeIs('admin.courses.import*', 'admin.faculties.*', 'admin.options.*', 'admin.promotions.*', 'admin.notifications.*');
@endphp

<nav class="tabbar z-40 border-t border-slate-200 bg-white/95 backdrop-blur lg:hidden"
     style="padding-bottom: env(safe-area-inset-bottom, 0px)"
     aria-label="Navigation administration">
    <div class="mx-auto grid max-w-6xl grid-cols-4">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? $active : $idle }}">
            <x-icon name="grid" class="h-6 w-6" />
            Accueil
        </a>
        <a href="{{ route('admin.courses.index') }}"
           class="{{ request()->routeIs('admin.courses.*', 'admin.chapters.*', 'admin.quizzes.*') && ! request()->routeIs('admin.courses.import*') ? $active : $idle }}">
            <x-icon name="book" class="h-6 w-6" />
            Cours
        </a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? $active : $idle }}">
            <x-icon name="users" class="h-6 w-6" />
            Comptes
        </a>
        <details class="relative">
            <summary class="{{ $moreOpen ? $active : $idle }} cursor-pointer list-none">
                <x-icon name="more" class="h-6 w-6" />
                Plus
            </summary>
            <div class="absolute bottom-[calc(100%+0.5rem)] right-2 w-56 rounded-2xl border bg-white p-2 shadow-xl">
                <a href="{{ route('admin.notifications.index') }}" class="block rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.notifications.*') ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700' }}">
                    Mouvements
                </a>
                <a href="{{ route('admin.courses.import') }}" class="block rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.courses.import*') ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700' }}">
                    Import Word
                </a>
                @if ($super)
                    <a href="{{ route('admin.faculties.index') }}" class="block rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.faculties.*') ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700' }}">
                        Facultés
                    </a>
                    <a href="{{ route('admin.options.index') }}" class="block rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.options.*') ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700' }}">
                        Options
                    </a>
                    <a href="{{ route('admin.promotions.index') }}" class="block rounded-lg px-3 py-3 text-sm {{ request()->routeIs('admin.promotions.*') ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700' }}">
                        Promotions
                    </a>
                @endif
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-3 text-sm text-slate-700">
                    Espace étudiant
                </a>
            </div>
        </details>
    </div>
</nav>
