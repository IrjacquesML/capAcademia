<x-layouts.app title="Mon profil">
    <h1 class="mb-6 text-xl font-semibold sm:text-2xl">Mon profil</h1>

    <dl class="divide-y rounded-xl border bg-white">
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">Nom</dt>
            <dd class="sm:col-span-2 font-medium">{{ $user->name }}</dd>
        </div>
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">E-mail</dt>
            <dd class="sm:col-span-2">{{ $user->email }}</dd>
        </div>
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">Rôle</dt>
            <dd class="sm:col-span-2">{{ $user->roleLabel() }}</dd>
        </div>
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">Faculté</dt>
            <dd class="sm:col-span-2">{{ $user->faculty?->name ?? '—' }}</dd>
        </div>
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">Option</dt>
            <dd class="sm:col-span-2">{{ $user->option?->name ?? '—' }}</dd>
        </div>
        <div class="grid gap-1 px-5 py-4 sm:grid-cols-3">
            <dt class="text-sm text-slate-500">Promotion</dt>
            <dd class="sm:col-span-2">{{ $user->promotion?->name ?? '—' }}</dd>
        </div>
    </dl>

    <p class="mt-6 text-sm text-slate-500">
        Votre espace n’affiche que les cours de votre faculté, option et promotion.
    </p>

    @if (auth()->user()->isPrivileged())
        <a href="{{ route('admin.dashboard') }}"
           class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">
            Administration
        </a>
    @endif
</x-layouts.app>
