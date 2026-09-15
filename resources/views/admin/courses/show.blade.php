<x-layouts.admin :title="$course->title">
    <a href="{{ route('admin.courses.index') }}" class="text-sm text-indigo-700">← Cours</a>
    <div class="mb-6 mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $course->title }}</h1>
            <p class="mt-1 text-slate-600">
                {{ $course->faculty->name }} · {{ $course->option->name }} · {{ $course->promotion->name }}
                · {{ $course->is_published ? 'Publié' : 'Brouillon' }}
            </p>
        </div>
        <a href="{{ route('admin.courses.edit', $course) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border px-4 py-2 text-sm sm:w-auto">Modifier le cours</a>
    </div>

    @if ($course->description)
        <p class="mb-8 text-justify text-slate-700 hyphens-auto">{{ $course->description }}</p>
    @endif

    <h2 class="mb-4 font-medium">Chapitres</h2>
    @forelse ($course->chapters as $chapter)
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-white p-5">
            <div>
                <p class="font-medium">{{ $chapter->position }}. {{ $chapter->title }}</p>
                <p class="text-sm text-slate-500">
                    {{ $chapter->quiz ? $chapter->quiz->questions->count().' question(s) d’interrogation' : 'Pas d’interrogation' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ route('admin.quizzes.edit', [$course, $chapter]) }}" class="text-indigo-700">
                    {{ $chapter->quiz ? 'Modifier l’interrogation' : 'Ajouter une interrogation' }}
                </a>
                <a href="{{ route('admin.chapters.edit', [$course, $chapter]) }}" class="text-indigo-700">Modifier le chapitre</a>
                <form method="POST" action="{{ route('admin.chapters.destroy', [$course, $chapter]) }}"
                      onsubmit="return confirm('Supprimer ce chapitre ?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600">Supprimer</button>
                </form>
            </div>
        </div>
    @empty
        <p class="mb-6 rounded-xl border border-dashed bg-white p-6 text-slate-500">Aucun chapitre pour l’instant.</p>
    @endforelse

    <h2 class="mb-4 mt-10 font-medium">Ajouter un chapitre</h2>
    <form method="POST" action="{{ route('admin.chapters.store', $course) }}" class="rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        <label class="mb-2 block text-sm">Titre</label>
        <input name="title" value="{{ old('title') }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Contenu</label>
        <p class="mb-2 text-xs text-slate-500">Texte simple ou HTML (gras, listes, tableaux…).</p>
        <textarea name="content" rows="8" required class="mb-4 w-full rounded-lg border px-3 py-2">{{ old('content') }}</textarea>

        @error('title') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('content') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

        <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">Ajouter le chapitre</button>
    </form>
</x-layouts.admin>
