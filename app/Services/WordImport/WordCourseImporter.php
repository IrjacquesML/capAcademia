<?php

namespace App\Services\WordImport;

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WordCourseImporter
{
    public function __construct(
        private readonly WordCourseDocumentParser $parser,
        private readonly HtmlSanitizer $sanitizer,
    ) {}

    public function import(
        User $actor,
        UploadedFile $file,
        int $facultyId,
        int $optionId,
        int $promotionId,
        bool $publish = true,
    ): Course {
        if (! $actor->isPrivileged()) {
            throw new InvalidArgumentException('Seuls les administrateurs peuvent importer un cours.');
        }

        $option = Option::query()->findOrFail($optionId);
        if ((int) $option->faculty_id !== $facultyId) {
            throw new InvalidArgumentException('L’option ne correspond pas à la faculté choisie.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'docx') {
            throw new InvalidArgumentException('Le fichier doit être au format Word .docx (pas .doc).');
        }

        $parsed = $this->parser->parseFile(
            $file->getRealPath(),
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        );

        $course = new Course([
            'faculty_id' => $facultyId,
            'option_id' => $optionId,
            'promotion_id' => $promotionId,
        ]);

        if (! $actor->canAccessCourse($course)) {
            throw new InvalidArgumentException('Vous ne pouvez pas importer un cours hors de votre faculté.');
        }

        return DB::transaction(function () use ($parsed, $facultyId, $optionId, $promotionId, $publish): Course {
            $slug = $this->uniqueSlug($facultyId, $optionId, $promotionId, $parsed->title);

            $course = Course::query()->create([
                'faculty_id' => $facultyId,
                'option_id' => $optionId,
                'promotion_id' => $promotionId,
                'title' => $parsed->title,
                'slug' => $slug,
                'description' => $parsed->description,
                'is_published' => $publish,
            ]);

            $mediaUrls = $this->storeMedia($course, $parsed->media);

            foreach ($parsed->chapters as $index => $parsedChapter) {
                $this->storeChapter($course, $parsedChapter, $index + 1, $mediaUrls);
            }

            return $course->load('chapters.quiz.questions.answers');
        });
    }

    /**
     * @param  array<string, string>  $mediaUrls
     */
    private function storeChapter(Course $course, ParsedChapter $parsedChapter, int $position, array $mediaUrls): void
    {
        $content = $parsedChapter->content !== ''
            ? $this->sanitizer->sanitize(strtr($parsedChapter->content, $mediaUrls))
            : '<p>Contenu à compléter.</p>';

        $chapter = Chapter::query()->create([
            'course_id' => $course->id,
            'title' => $parsedChapter->title,
            'slug' => Str::slug($parsedChapter->title).'-'.$position,
            'content' => $content !== '' ? $content : '<p>Contenu à compléter.</p>',
            'position' => $position,
            'is_published' => true,
        ]);

        if ($parsedChapter->questions === []) {
            return;
        }

        $quiz = Quiz::query()->create([
            'chapter_id' => $chapter->id,
            'title' => 'Interrogation — '.$parsedChapter->title,
            'description' => 'Interrogation générée à partir du document Word.',
            'passing_score' => 50,
        ]);

        foreach ($parsedChapter->questions as $qIndex => $parsedQuestion) {
            $question = Question::query()->create([
                'quiz_id' => $quiz->id,
                'type' => $parsedQuestion->type,
                'prompt' => $parsedQuestion->prompt,
                'points' => 1,
                'position' => $qIndex + 1,
            ]);

            if ($parsedQuestion->type === QuestionType::Text) {
                foreach ($parsedQuestion->accepted as $aIndex => $label) {
                    Answer::query()->create([
                        'question_id' => $question->id,
                        'label' => $label,
                        'is_correct' => true,
                        'position' => $aIndex + 1,
                    ]);
                }

                continue;
            }

            foreach ($parsedQuestion->choices as $aIndex => $choice) {
                Answer::query()->create([
                    'question_id' => $question->id,
                    'label' => $choice['label'],
                    'is_correct' => $choice['is_correct'],
                    'position' => $aIndex + 1,
                ]);
            }
        }
    }

    private function uniqueSlug(int $facultyId, int $optionId, int $promotionId, string $title): string
    {
        $base = Str::slug($title) ?: 'cours-importe';
        $slug = $base;
        $i = 2;

        while (Course::query()->where([
            'faculty_id' => $facultyId,
            'option_id' => $optionId,
            'promotion_id' => $promotionId,
            'slug' => $slug,
        ])->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<string, array{name: string, bytes: string, mime: string}>  $media
     * @return array<string, string>
     */
    private function storeMedia(Course $course, array $media): array
    {
        $urls = [];

        foreach ($media as $rId => $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (! in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true)) {
                continue;
            }

            $name = Str::slug($rId).'-'.Str::lower(Str::random(6)).'.'.$ext;
            $path = 'course-media/'.$course->id.'/'.$name;
            Storage::disk('public')->put($path, $file['bytes']);
            $urls['media://'.$rId] = '/storage/'.$path;
        }

        return $urls;
    }
}
