<x-layouts.app :title="$course->title">
    <a href="{{ route('courses.index') }}" class="text-sm text-indigo-700">← Tous les cours</a>
    <h1 class="mt-3 text-xl font-semibold sm:text-2xl">{{ $course->title }}</h1>
    <p class="mb-2 text-sm text-slate-600 sm:text-base">
        {{ $course->faculty->name }} · {{ $course->option->name }} · {{ $course->promotion->name }}
    </p>

    @if ($course->description)
        <p class="mb-6 text-justify text-slate-700 hyphens-auto">{{ $course->description }}</p>
    @endif

    <p class="mb-6 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
        Progression obligatoire : chaque chapitre se débloque après la soumission de l’interrogation du chapitre précédent.
    </p>

    <h2 class="mb-4 font-medium">Chapitres</h2>
    @forelse ($course->chapters as $chapter)
        @php
            $progress = $chapter->progress->first();
            $unlocked = (bool) $chapter->is_unlocked;
            $submitted = $chapter->quiz && $chapter->quiz->attempts->isNotEmpty();
        @endphp

        @if ($unlocked)
            <a href="{{ route('chapters.show', [$course, $chapter]) }}"
               class="mb-3 flex flex-col gap-2 rounded-xl border bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 hover:border-indigo-300">
                <span class="font-medium">{{ $chapter->position }}. {{ $chapter->title }}</span>
                @if ($progress?->completed_at)
                    <span class="text-sm text-emerald-700">Validé</span>
                @elseif ($submitted)
                    <span class="text-sm text-indigo-700">Interrogation envoyée</span>
                @elseif ($progress?->read_at)
                    <span class="text-sm text-amber-700">En cours</span>
                @else
                    <span class="text-sm text-slate-500">À étudier</span>
                @endif
            </a>
        @else
            <div class="mb-3 flex flex-col gap-2 rounded-xl border border-dashed bg-slate-100 p-4 text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <span class="font-medium">{{ $chapter->position }}. {{ $chapter->title }}</span>
                <span class="text-sm">Verrouillé</span>
            </div>
        @endif
    @empty
        <p class="text-slate-500">Aucun chapitre publié pour le moment.</p>
    @endforelse
</x-layouts.app>
