@props(['attempt'])

@php
    $percentage = rtrim(rtrim(number_format((float) $attempt->percentage, 2, ',', ' '), '0'), ',');
@endphp

<div class="rounded-xl border {{ $attempt->passed ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-5">
    <p class="text-lg font-semibold {{ $attempt->passed ? 'text-emerald-800' : 'text-amber-800' }}">
        {{ $attempt->passed ? 'Interrogation réussie' : 'Interrogation non validée' }}
    </p>
    <p class="mt-1 text-sm text-slate-700">
        Score : {{ $attempt->score }} / {{ $attempt->max_score }}
        ({{ $percentage }} %)
        · Soumise le {{ $attempt->submitted_at?->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}
    </p>
</div>

<ol class="mt-6 space-y-4">
    @foreach ($attempt->breakdown ?? [] as $index => $item)
        @php
            $isCorrect = (bool) ($item['correct'] ?? false);
            $studentAnswer = $item['student_answer'] ?? 'Non répondu';
            $expected = collect($item['expected_answers'] ?? [])->filter()->values();
        @endphp
        <li class="rounded-xl border bg-white p-5">
            <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                <p class="font-medium">
                    <span class="mr-2 inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-slate-800 px-2 text-xs text-white">
                        {{ $index + 1 }}
                    </span>
                    Question {{ $index + 1 }} — {{ $item['prompt'] ?? '' }}
                </p>
                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                    · {{ $item['earned'] ?? 0 }}/{{ $item['points'] ?? 0 }} pt
                </span>
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-lg {{ $isCorrect ? 'bg-emerald-50' : 'bg-red-50' }} p-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Votre réponse</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $studentAnswer }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Bonne réponse</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $expected->isEmpty() ? 'Non définie' : $expected->implode(' · ') }}
                    </dd>
                </div>
            </dl>
        </li>
    @endforeach
</ol>
