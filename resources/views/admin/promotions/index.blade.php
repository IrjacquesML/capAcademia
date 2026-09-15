<x-layouts.admin title="Promotions">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold sm:text-2xl">Promotions</h1>
        <a href="{{ route('admin.promotions.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm text-white">Nouvelle promotion</a>
    </div>

    <div class="space-y-3 md:hidden">
        @forelse ($promotions as $promotion)
            <article class="rounded-xl border bg-white p-4">
                <p class="font-medium">{{ $promotion->name }}</p>
                <p class="text-sm text-slate-500">Niveau {{ $promotion->level }} · {{ $promotion->courses_count }} cours</p>
                <div class="mt-3 flex gap-4 text-sm">
                    <a href="{{ route('admin.promotions.edit', $promotion) }}" class="text-indigo-700">Modifier</a>
                    <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Supprimer cette promotion ?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600">Supprimer</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">Aucune promotion.</p>
        @endforelse
    </div>

    <div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">Niveau</th>
                    <th class="px-4 py-3">Cours</th>
                    <th class="px-4 py-3">Utilisateurs</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promotions as $promotion)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $promotion->name }}</td>
                        <td class="px-4 py-3">{{ $promotion->level }}</td>
                        <td class="px-4 py-3">{{ $promotion->courses_count }}</td>
                        <td class="px-4 py-3">{{ $promotion->users_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.promotions.edit', $promotion) }}" class="text-indigo-700">Modifier</a>
                            <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" class="ml-3 inline"
                                  onsubmit="return confirm('Supprimer cette promotion ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune promotion.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
