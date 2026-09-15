<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveChapterQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $course instanceof Course
            && $this->user()?->can('update', $course) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'passing_score' => ['required', 'integer', 'min:1', 'max:100'],
            'questions' => ['required', 'array', 'min:1', 'max:40'],
            'questions.*.prompt' => ['required', 'string', 'max:2000'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:20'],
            'questions.*.choices' => ['nullable', 'array', 'max:8'],
            'questions.*.choices.*' => ['nullable', 'string', 'max:500'],
            'questions.*.correct' => ['nullable'],
            'questions.*.accepted_text' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'questions.required' => 'Ajoutez au moins une question.',
            'questions.min' => 'Ajoutez au moins une question.',
            'questions.*.prompt.required' => 'Chaque question doit avoir un énoncé.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('questions', []) as $index => $raw) {
                if (! is_array($raw)) {
                    continue;
                }

                $type = $raw['type'] ?? '';
                if ($type === QuestionType::Text->value) {
                    if ($this->acceptedAnswers($raw) === []) {
                        $validator->errors()->add(
                            "questions.$index.accepted_text",
                            'Indiquez au moins une réponse acceptée (une par ligne).',
                        );
                    }

                    continue;
                }

                $choices = $this->filledChoices($raw);
                if (count($choices) < 2) {
                    $validator->errors()->add(
                        "questions.$index.choices",
                        'Ajoutez au moins deux propositions non vides.',
                    );
                }

                $correct = $this->correctChoiceLabel($raw);
                if ($correct === null) {
                    $validator->errors()->add(
                        "questions.$index.correct",
                        'Cochez la bonne réponse parmi les propositions renseignées.',
                    );
                }
            }
        });
    }

    /**
     * @return array{
     *     title: string,
     *     description: ?string,
     *     passing_score: int,
     *     questions: list<array{
     *         prompt: string,
     *         type: QuestionType,
     *         points: int,
     *         choices?: list<array{label: string, is_correct: bool}>,
     *         accepted?: list<string>
     *     }>
     * }
     */
    public function payload(): array
    {
        $questions = [];

        foreach ($this->input('questions', []) as $raw) {
            if (! is_array($raw)) {
                continue;
            }

            $type = QuestionType::from((string) $raw['type']);
            $item = [
                'prompt' => trim((string) $raw['prompt']),
                'type' => $type,
                'points' => max(1, (int) ($raw['points'] ?? 1)),
            ];

            if ($type === QuestionType::Text) {
                $item['accepted'] = $this->acceptedAnswers($raw);
            } else {
                $correctIndex = array_key_exists('correct', $raw) ? (int) $raw['correct'] : -1;
                $choices = [];
                foreach ($raw['choices'] ?? [] as $index => $choice) {
                    $label = trim((string) $choice);
                    if ($label === '') {
                        continue;
                    }
                    $choices[] = [
                        'label' => $label,
                        'is_correct' => (int) $index === $correctIndex,
                    ];
                }
                $item['choices'] = $choices;
            }

            $questions[] = $item;
        }

        return [
            'title' => trim((string) $this->input('title')),
            'description' => trim((string) $this->input('description')) ?: null,
            'passing_score' => (int) $this->input('passing_score', 50),
            'questions' => $questions,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return list<string>
     */
    private function filledChoices(array $raw): array
    {
        $choices = [];
        foreach ($raw['choices'] ?? [] as $choice) {
            $label = trim((string) $choice);
            if ($label !== '') {
                $choices[] = $label;
            }
        }

        return array_values($choices);
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return list<string>
     */
    private function acceptedAnswers(array $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) ($raw['accepted_text'] ?? '')) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn (string $line): bool => $line !== ''));
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    private function correctChoiceLabel(array $raw): ?string
    {
        $choices = $raw['choices'] ?? [];
        if (! is_array($choices) || ! array_key_exists('correct', $raw) || $raw['correct'] === '' || $raw['correct'] === null) {
            return null;
        }

        $index = (int) $raw['correct'];
        $label = trim((string) ($choices[$index] ?? ''));

        return $label !== '' ? $label : null;
    }
}
