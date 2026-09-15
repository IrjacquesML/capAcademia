<x-layouts.admin :title="$chapter->title">
    <a href="{{ route('admin.courses.show', $course) }}" class="text-sm text-indigo-700">← {{ $course->title }}</a>
    <h1 class="mb-6 mt-3 text-2xl font-semibold">Modifier le chapitre</h1>

    <form method="POST" action="{{ route('admin.chapters.update', [$course, $chapter]) }}"
          class="rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @method('PUT')

        <label class="mb-2 block text-sm">Titre</label>
        <input name="title" value="{{ old('title', $chapter->title) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Contenu</label>
        <p class="mb-2 text-xs text-slate-500">
            La mise en forme Word (gras, italique, listes, tableaux, images, couleurs, alignement) est conservée.
            Vous pouvez ajuster le HTML si besoin.
        </p>
        <textarea name="content" rows="16" required class="mb-4 w-full rounded-lg border px-3 py-2 font-mono text-sm">{{ old('content', $chapter->content) }}</textarea>

        <p class="mb-2 text-sm font-medium">Aperçu</p>
        <x-chapter-body class="mb-6" :html="app(\App\Support\HtmlSanitizer::class)->sanitizeForDisplay((string) old('content', $chapter->content))" />

        <label class="mb-6 flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $chapter->is_published))>
            Chapitre publié
        </label>

        @if ($errors->any())
            <ul class="mb-4 list-disc ps-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">Enregistrer</button>
    </form>

    <p class="mt-8 mb-3 text-sm font-medium">Interrogation</p>
    <a href="{{ route('admin.quizzes.edit', [$course, $chapter]) }}"
       class="inline-block rounded-lg border px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">
        {{ $chapter->quiz ? 'Modifier l’interrogation' : 'Ajouter une interrogation' }}
    </a>
</x-layouts.admin>
