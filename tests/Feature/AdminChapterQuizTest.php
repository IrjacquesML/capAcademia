<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;

class AdminChapterQuizTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_an_admin_can_add_a_quiz_to_a_chapter(): void
    {
        $world = $this->createAcademicWorld();
        $world['quiz']->delete();
        $world['chapter']->refresh();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.quizzes.edit', [$world['course'], $world['chapter']]))
            ->assertOk()
            ->assertSee('Ajouter une interrogation');

        $this->actingAs($admin)
            ->put(route('admin.quizzes.update', [$world['course'], $world['chapter']]), $this->quizPayload())
            ->assertRedirect(route('admin.courses.show', $world['course']));

        $this->assertDatabaseHas('quizzes', [
            'chapter_id' => $world['chapter']->id,
            'title' => 'Interrogation test',
            'passing_score' => 60,
        ]);

        $quiz = Quiz::query()->where('chapter_id', $world['chapter']->id)->first();
        $this->assertNotNull($quiz);
        $this->assertSame(2, $quiz->questions()->count());
        $this->assertTrue(
            Question::query()->where('quiz_id', $quiz->id)->where('type', QuestionType::MultipleChoice)->exists()
        );
        $this->assertTrue(
            Question::query()->where('quiz_id', $quiz->id)->where('type', QuestionType::Text)->exists()
        );

        $this->actingAs($world['student'])
            ->get(route('chapters.show', [$world['course'], $world['chapter']]))
            ->assertOk()
            ->assertSee('Un algorithme doit-il se terminer')
            ->assertSee('Citez un langage');
    }

    public function test_an_admin_can_update_and_delete_a_quiz(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);

        $this->actingAs($admin)
            ->put(route('admin.quizzes.update', [$world['course'], $world['chapter']]), $this->quizPayload([
                'title' => 'Interrogation mise à jour',
            ]))
            ->assertRedirect(route('admin.courses.show', $world['course']));

        $this->assertDatabaseHas('quizzes', [
            'id' => $world['quiz']->id,
            'title' => 'Interrogation mise à jour',
        ]);
        $this->assertSame(2, $world['quiz']->questions()->count());

        $this->actingAs($admin)
            ->delete(route('admin.quizzes.destroy', [$world['course'], $world['chapter']]))
            ->assertRedirect(route('admin.courses.show', $world['course']));

        $this->assertDatabaseMissing('quizzes', ['id' => $world['quiz']->id]);
    }

    public function test_a_student_cannot_manage_quizzes(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('admin.quizzes.edit', [$world['course'], $world['chapter']]))
            ->assertRedirect(route('dashboard'));
    }

    public function test_a_faculty_admin_cannot_edit_a_quiz_from_another_faculty(): void
    {
        $world = $this->createAcademicWorld();
        $foreign = Course::factory()->create(['is_published' => true]);
        $chapter = Chapter::factory()->create(['course_id' => $foreign->id]);
        $admin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);

        $this->actingAs($admin)
            ->get(route('admin.quizzes.edit', [$foreign, $chapter]))
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function quizPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Interrogation test',
            'description' => 'À partir du chapitre.',
            'passing_score' => 60,
            'questions' => [
                [
                    'prompt' => 'Un algorithme doit-il se terminer ?',
                    'type' => QuestionType::MultipleChoice->value,
                    'points' => 1,
                    'choices' => ['Oui', 'Non', '', ''],
                    'correct' => '0',
                    'accepted_text' => '',
                ],
                [
                    'prompt' => 'Citez un langage',
                    'type' => QuestionType::Text->value,
                    'points' => 1,
                    'choices' => ['', '', '', ''],
                    'correct' => '0',
                    'accepted_text' => "Python\nJava",
                ],
            ],
        ], $overrides);
    }
}
