<x-layouts.app title="Ma progression">
    <h1 class="mb-2 text-xl font-semibold sm:text-2xl">Ma progression</h1>
    <p class="mb-8 text-slate-600">
        Avancement chapitre par chapitre. Un chapitre se débloque après soumission de l’interrogation précédente.
    </p>

    @forelse ($courses as $course)
        <section class="mb-8 rounded-xl border bg-white p-5">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-medium">{{ $course->title }}</h2>
                    <p class="text-sm text-slate-500">{{ $course->progress_done }}/{{ $course->progress_total }} chapitres</p>
                </div>
                <span class="text-sm font-medium text-indigo-700">{{ $course->progress_percent }} %</span>
            </div>
            <div class="mb-5 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $course->progress_percent }}%"></div>
            </div>

            <ol class="space-y-2">
                @foreach ($course->chapters as $chapter)
                    @php
                        $done = $chapter->quiz
                            ? $chapter->quiz->attempts->isNotEmpty()
                            : (bool) $chapter->progress->first()?->read_at;
                        $validated = (bool) $chapter->progress->first()?->completed_at;
                    @endphp
                    <li class="flex flex-col gap-1 rounded-lg bg-slate-50 px-3 py-3 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:py-2">
                        <span>
                            {{ $chapter->position }}. {{ $chapter->title }}
                        </span>
                        @if (! $chapter->is_unlocked)
                            <span class="text-slate-400">Verrouillé</span>
                        @elseif ($validated)
                            <a href="{{ route('chapters.show', [$course, $chapter]) }}" class="text-emerald-700">Validé</a>
                        @elseif ($done)
                            <a href="{{ route('chapters.show', [$course, $chapter]) }}" class="text-indigo-700">Interrogation envoyée</a>
                        @else
                            <a href="{{ route('chapters.show', [$course, $chapter]) }}" class="text-amber-700">En cours</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @empty
        <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">
            Aucune progression à afficher.
        </p>
    @endforelse
</x-layouts.app>
