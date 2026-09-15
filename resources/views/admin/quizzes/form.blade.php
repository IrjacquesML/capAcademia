<x-layouts.admin :title="$chapter->quiz ? 'Modifier l’interrogation' : 'Ajouter une interrogation'">
    <a href="{{ route('admin.courses.show', $course) }}" class="text-sm text-indigo-700">← {{ $course->title }}</a>
    <h1 class="mb-2 mt-3 text-2xl font-semibold">
        {{ $chapter->quiz ? 'Modifier l’interrogation' : 'Ajouter une interrogation' }}
    </h1>
    <p class="mb-6 text-slate-600">Chapitre {{ $chapter->position }} — {{ $chapter->title }}</p>

    <form method="POST" action="{{ route('admin.quizzes.update', [$course, $chapter]) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border bg-white p-6">
            <label class="mb-2 block text-sm">Titre de l’interrogation</label>
            <input name="title" value="{{ old('title', $quiz?->title ?? 'Interrogation — '.$chapter->title) }}"
                   required class="mb-4 w-full rounded-lg border px-3 py-2">

            <label class="mb-2 block text-sm">Consigne (facultatif)</label>
            <textarea name="description" rows="2" class="mb-4 w-full rounded-lg border px-3 py-2">{{ old('description', $quiz?->description) }}</textarea>

            <label class="mb-2 block text-sm">Seuil de réussite (%)</label>
            <input type="number" name="passing_score" min="1" max="100"
                   value="{{ old('passing_score', $quiz?->passing_score ?? 50) }}"
                   required class="w-32 rounded-lg border px-3 py-2">
        </div>

        <div id="questions" class="space-y-4">
            @foreach ($questions as $index => $question)
                @include('admin.quizzes.partial-question', ['index' => $index, 'question' => $question])
            @endforeach
        </div>

        <button type="button" id="add-question"
                class="rounded-lg border border-dashed px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">
            Ajouter une question
        </button>

        @if ($errors->any())
            <ul class="list-disc ps-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">Enregistrer l’interrogation</button>
            @if ($quiz)
                <button form="delete-quiz" class="rounded-lg border border-red-200 px-4 py-2 text-red-700"
                        onclick="return confirm('Supprimer cette interrogation ? Les tentatives des étudiants seront aussi effacées.')">
                    Supprimer
                </button>
            @endif
        </div>
    </form>

    @if ($quiz)
        <form id="delete-quiz" method="POST" action="{{ route('admin.quizzes.destroy', [$course, $chapter]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <template id="question-template">
        @include('admin.quizzes.partial-question', [
            'index' => '__INDEX__',
            'question' => [
                'prompt' => '',
                'type' => 'multiple_choice',
                'points' => 1,
                'choices' => ['', '', '', ''],
                'correct' => '0',
                'accepted_text' => '',
            ],
        ])
    </template>

    <script>
        (function () {
            const list = document.getElementById('questions');
            const template = document.getElementById('question-template');
            const addButton = document.getElementById('add-question');
            if (!list || !template || !addButton) return;

            const nextIndex = () => {
                let max = -1;
                list.querySelectorAll('[data-question] textarea[name*="[prompt]"]').forEach((el) => {
                    const match = el.name.match(/questions\[(\d+)\]/);
                    if (match) {
                        max = Math.max(max, parseInt(match[1], 10));
                    }
                });
                return max + 1;
            };

            let seq = nextIndex();

            const bindQuestion = (root) => {
                const typeSelect = root.querySelector('[data-type]');
                const mcq = root.querySelector('[data-mcq]');
                const text = root.querySelector('[data-text]');
                const remove = root.querySelector('[data-remove]');
                const sync = () => {
                    const isText = typeSelect.value === 'text';
                    mcq.classList.toggle('hidden', isText);
                    text.classList.toggle('hidden', !isText);
                };
                typeSelect.addEventListener('change', sync);
                sync();
                remove.addEventListener('click', () => {
                    if (list.querySelectorAll('[data-question]').length < 2) return;
                    root.remove();
                });
            };

            list.querySelectorAll('[data-question]').forEach(bindQuestion);

            addButton.addEventListener('click', () => {
                const html = template.innerHTML.replaceAll('__INDEX__', String(seq++));
                const wrap = document.createElement('div');
                wrap.innerHTML = html.trim();
                const node = wrap.firstElementChild;
                list.appendChild(node);
                bindQuestion(node);
            });
        })();
    </script>
</x-layouts.admin>
