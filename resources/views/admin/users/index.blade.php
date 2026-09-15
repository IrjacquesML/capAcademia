<x-layouts.admin title="Utilisateurs">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold sm:text-2xl">Utilisateurs</h1>
        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm text-white">Nouvel utilisateur</a>
    </div>

    <div class="space-y-3 md:hidden">
        @forelse ($users as $user)
            <article class="rounded-xl border bg-white p-4">
                <p class="font-medium">{{ $user->name }}</p>
                <p class="text-sm text-slate-600">{{ $user->email }}</p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $user->roleLabel() }}
                    · {{ $user->faculty?->name ?? 'Toutes facultés' }}
                </p>
                <div class="mt-3 flex flex-wrap gap-4 text-sm">
                    <a href="{{ route('admin.users.audit', $user) }}" class="text-indigo-700">Audit</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-700">Modifier</a>
                    @if (! $user->is(auth()->user()))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Supprimer</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">Aucun utilisateur.</p>
        @endforelse
    </div>

    <div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">Rôle</th>
                    <th class="px-4 py-3">Affectation</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->roleLabel() }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $user->faculty?->name ?? 'Toutes facultés' }}
                            @if ($user->option) · {{ $user->option->name }} @endif
                            @if ($user->promotion) · {{ $user->promotion->name }} @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.audit', $user) }}" class="text-indigo-700">Audit</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="ml-3 text-indigo-700">Modifier</a>
                            @if (! $user->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="ml-3 inline"
                                      onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600">Supprimer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun utilisateur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
