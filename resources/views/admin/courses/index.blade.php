<x-layouts.admin title="Cours">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold sm:text-2xl">Cours</h1>
        <div class="grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
            <a href="{{ route('admin.courses.import.template') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2 text-sm">Modèle Word</a>
            <a href="{{ route('admin.courses.import') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2 text-sm">Importer un Word</a>
            <a href="{{ route('admin.courses.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm text-white">Nouveau cours</a>
        </div>
    </div>

    <div class="space-y-3 md:hidden">
        @forelse ($courses as $course)
            <article class="rounded-xl border bg-white p-4">
                <a href="{{ route('admin.courses.show', $course) }}" class="font-medium">{{ $course->title }}</a>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $course->faculty->name }} · {{ $course->option->name }} · {{ $course->promotion->name }}
                </p>
                <p class="mt-1 text-sm text-slate-500">{{ $course->chapters_count }} chapitre(s) · {{ $course->is_published ? 'Publié' : 'Brouillon' }}</p>
                <div class="mt-3 flex gap-4 text-sm">
                    <a href="{{ route('admin.courses.edit', $course) }}" class="text-indigo-700">Modifier</a>
                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                          onsubmit="return confirm('Supprimer ce cours et tous ses chapitres ?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600">Supprimer</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">Aucun cours.</p>
        @endforelse
    </div>

    <div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Titre</th>
                    <th class="px-4 py-3">Affectation</th>
                    <th class="px-4 py-3">Chapitres</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('admin.courses.show', $course) }}" class="hover:text-indigo-700">{{ $course->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $course->faculty->name }} · {{ $course->option->name }} · {{ $course->promotion->name }}
                        </td>
                        <td class="px-4 py-3">{{ $course->chapters_count }}</td>
                        <td class="px-4 py-3">{{ $course->is_published ? 'Publié' : 'Brouillon' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.courses.edit', $course) }}" class="text-indigo-700">Modifier</a>
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="ml-3 inline"
                                  onsubmit="return confirm('Supprimer ce cours et tous ses chapitres ?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun cours.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $courses->links() }}</div>
</x-layouts.admin>
