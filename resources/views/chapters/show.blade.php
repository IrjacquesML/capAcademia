<x-layouts.app :title="$chapter->title">
    <a href="{{ route('courses.show', $course) }}" class="text-sm text-indigo-700">← {{ $course->title }}</a>
    <p class="mt-2 text-sm text-slate-500">Chapitre {{ $chapter->position }}</p>
    <h1 class="mt-1 mb-6 text-xl font-semibold sm:text-2xl">{{ $chapter->title }}</h1>

    <x-chapter-body class="mb-8" :html="$chapter->renderedHtml()" />

    @if (! $progress?->read_at)
        <form method="POST" action="{{ route('chapters.read', [$course, $chapter]) }}" class="mb-10">
            @csrf
            <button class="min-h-12 w-full rounded-xl bg-slate-800 px-4 py-3 text-white sm:w-auto">Marquer comme lu</button>
        </form>
    @else
        <p class="mb-10 text-sm text-emerald-700">Chapitre lu.</p>
    @endif

    @if ($chapter->quiz)
        <section id="interrogation" class="rounded-xl border bg-white p-4 sm:p-6">
            <h2 class="mb-2 text-lg font-medium">{{ $chapter->quiz->title }}</h2>
            @if ($chapter->quiz->description)
                <p class="mb-4 text-justify text-slate-600">{{ $chapter->quiz->description }}</p>
            @endif
            <p class="mb-6 text-sm text-slate-500">
                Seuil de réussite : {{ $chapter->quiz->passing_score }} %.
                La soumission débloque le chapitre suivant, même en cas d’échec.
            </p>

            @if ($latestAttempt)
                <div class="mb-8">
                    <h3 class="mb-4 font-medium">Dernier corrigé</h3>
                    <x-quiz-correction :attempt="$latestAttempt" />
                    <p class="mt-4 text-sm text-slate-500">Vous pouvez repasser l'interrogation ci-dessous. Le score retenu pour la validation reste une réussite déjà obtenue.</p>
                </div>
            @endif

            <h3 class="mb-4 font-medium">{{ $latestAttempt ? 'Nouvelle tentative' : 'Répondre à l\'interrogation' }}</h3>

            <form method="POST" action="{{ route('quizzes.submit', [$course, $chapter, $chapter->quiz]) }}">
                @csrf
                @php($totalQuestions = $chapter->quiz->questions->count())
                @foreach ($chapter->quiz->questions as $question)
                    <fieldset class="mb-8 rounded-lg border border-slate-200 p-4">
                        <legend class="mb-3 px-1 text-base font-semibold">
                            <span class="mr-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-indigo-600 px-2 text-sm text-white">
                                {{ $loop->iteration }}
                            </span>
                            Question {{ $loop->iteration }}/{{ $totalQuestions }}
                        </legend>
                        <p class="mb-4 text-justify font-medium">{{ $question->prompt }}</p>

                        @if ($question->type->value === 'multiple_choice')
                            @foreach ($question->answers as $answer)
                                <label class="mb-2 flex min-h-12 items-start gap-3 rounded-xl border border-slate-200 p-3">
                                    <input type="radio"
                                           class="mt-1 h-4 w-4"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $answer->id }}"
                                           required>
                                    <span>
                                        <span class="font-semibold text-slate-500">{{ chr(64 + $loop->iteration) }}.</span>
                                        {{ $answer->label }}
                                    </span>
                                </label>
                            @endforeach
                        @else
                            <input type="text"
                                   name="answers[{{ $question->id }}]"
                                   class="min-h-12 w-full rounded-xl border px-3 py-3"
                                   required>
                        @endif
                    </fieldset>
                @endforeach

                <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white hover:bg-indigo-700 sm:w-auto">
                    Soumettre l'interrogation
                </button>
            </form>
        </section>
    @elseif ($nextUnlocked && $nextChapter)
        <p class="mt-6 text-sm text-emerald-700">Chapitre suivant débloqué.</p>
    @endif

    @if ($nextUnlocked && $nextChapter)
        <a href="{{ route('chapters.show', [$course, $nextChapter]) }}"
           class="mt-8 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-center text-white hover:bg-indigo-700 sm:w-auto">
            Chapitre suivant : {{ $nextChapter->title }}
        </a>
    @elseif ($chapter->quiz && ! $latestAttempt)
        <p class="mt-8 text-sm text-slate-500">Soumettez l’interrogation pour débloquer le chapitre suivant.</p>
    @endif
</x-layouts.app>
