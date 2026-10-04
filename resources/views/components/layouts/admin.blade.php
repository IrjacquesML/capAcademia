@props(['title' => 'Administration'])

<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.meta />
    <title>{{ $title }} — CapAcademia</title>
</head>
<body class="app-shell bg-slate-50 text-slate-900 antialiased">
    <header class="z-30 shrink-0 border-b bg-white/95 backdrop-blur"
            style="padding-top: env(safe-area-inset-top)">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4">
            <x-brand :href="route('admin.dashboard')">
                <span class="ml-1 text-xs font-normal uppercase tracking-wide text-slate-400">Admin</span>
            </x-brand>

            <x-admin-nav class="hidden items-center gap-1 lg:flex" />

            <div class="flex items-center gap-1 sm:gap-3">
                <x-admin-notifications />
                <div class="hidden text-right text-sm leading-tight sm:block">
                    <p class="font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->roleLabel() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            aria-label="Déconnexion">
                        <span class="hidden sm:inline">Déconnexion</span>
                        <x-icon name="logout" class="h-5 w-5 sm:hidden" />
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="admin-main mx-auto w-full max-w-6xl px-4 pt-5 pb-8 lg:pt-8 lg:pb-8">
        @if (session('status'))
            <p class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-emerald-800 lg:mb-6">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-red-800 lg:mb-6">{{ session('error') }}</p>
        @endif

        {{ $slot }}
    </main>

    <x-admin-tabbar />
</body>
</html>
