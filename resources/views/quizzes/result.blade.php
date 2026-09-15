<x-layouts.app title="Corrigé de l'interrogation">
    <a href="{{ route('chapters.show', [$course, $chapter]) }}" class="text-sm text-indigo-700">← {{ $chapter->title }}</a>
    <h1 class="mt-3 mb-2 text-2xl font-semibold">Corrigé — {{ $quiz->title }}</h1>
    <p class="mb-6 text-slate-600">{{ $course->title }}</p>

    <x-quiz-correction :attempt="$attempt" />

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
        <a href="{{ route('chapters.show', [$course, $chapter]) }}"
           class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-700 hover:bg-slate-50">
            Retour au chapitre
        </a>
        <a href="{{ route('chapters.show', [$course, $chapter]) }}#interrogation"
           class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-700 hover:bg-slate-50">
            Repasser l'interrogation
        </a>
        @if ($nextUnlocked && $nextChapter)
            <a href="{{ route('chapters.show', [$course, $nextChapter]) }}"
               class="inline-flex min-h-12 items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-center text-white hover:bg-indigo-700">
                Chapitre suivant : {{ $nextChapter->title }}
            </a>
        @endif
    </div>
</x-layouts.app>
