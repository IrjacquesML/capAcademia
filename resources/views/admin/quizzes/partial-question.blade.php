@php
    $choices = array_values($question['choices'] ?? ['', '', '', '']);
    while (count($choices) < 4) {
        $choices[] = '';
    }
    $type = $question['type'] ?? 'multiple_choice';
    $correct = (string) ($question['correct'] ?? '0');
@endphp

<div data-question class="rounded-xl border bg-white p-6">
    <div class="mb-4 flex items-start justify-between gap-3">
        <p class="font-medium">Question</p>
        <button type="button" data-remove class="text-sm text-red-600">Retirer</button>
    </div>

    <label class="mb-2 block text-sm">Énoncé</label>
    <textarea name="questions[{{ $index }}][prompt]" rows="2" required
              class="mb-4 w-full rounded-lg border px-3 py-2">{{ $question['prompt'] ?? '' }}</textarea>

    <div class="mb-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-2 block text-sm">Type</label>
            <select name="questions[{{ $index }}][type]" data-type class="w-full rounded-lg border px-3 py-2">
                <option value="multiple_choice" @selected($type === 'multiple_choice')>Choix multiples</option>
                <option value="text" @selected($type === 'text')>Réponse libre</option>
            </select>
        </div>
        <div>
            <label class="mb-2 block text-sm">Points</label>
            <input type="number" name="questions[{{ $index }}][points]" min="1" max="20"
                   value="{{ $question['points'] ?? 1 }}" class="w-full rounded-lg border px-3 py-2">
        </div>
    </div>

    <div data-mcq class="{{ $type === 'text' ? 'hidden' : '' }}">
        <p class="mb-2 text-sm text-slate-600">Propositions — cochez la bonne réponse.</p>
        @foreach ($choices as $choiceIndex => $choice)
            <label class="mb-2 flex items-center gap-2">
                <input type="radio" name="questions[{{ $index }}][correct]" value="{{ $choiceIndex }}"
                       @checked($correct === (string) $choiceIndex)>
                <input name="questions[{{ $index }}][choices][{{ $choiceIndex }}]" value="{{ $choice }}"
                       placeholder="Proposition {{ $choiceIndex + 1 }}"
                       class="w-full rounded-lg border px-3 py-2">
            </label>
        @endforeach
    </div>

    <div data-text class="{{ $type === 'text' ? '' : 'hidden' }}">
        <label class="mb-2 block text-sm">Réponses acceptées (une par ligne)</label>
        <textarea name="questions[{{ $index }}][accepted_text]" rows="3"
                  class="w-full rounded-lg border px-3 py-2"
                  placeholder="Python&#10;python">{{ $question['accepted_text'] ?? '' }}</textarea>
    </div>
</div>
