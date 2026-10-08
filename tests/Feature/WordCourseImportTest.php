<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Question;
use App\Models\User;
use App\Services\WordImport\WordCourseDocumentParser;
use App\Services\WordImport\WordCourseTemplateGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesAcademicWorld;
use Tests\TestCase;
use ZipArchive;

class WordCourseImportTest extends TestCase
{
    use CreatesAcademicWorld;
    use RefreshDatabase;

    public function test_a_super_admin_can_import_a_word_course_split_into_chapters_and_quizzes(): void
    {
        $world = $this->createAcademicWorld();
        $super = User::factory()->superAdmin()->create();
        $path = $this->makeDocx();

        try {
            $response = $this->actingAs($super)->post(route('admin.courses.import.store'), [
                'faculty_id' => $world['faculty']->id,
                'option_id' => $world['option']->id,
                'promotion_id' => $world['promotion']->id,
                'is_published' => '1',
                'document' => new UploadedFile(
                    $path,
                    'algorithmique.docx',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    null,
                    true,
                ),
            ]);
        } finally {
            @unlink($path);
        }

        $course = Course::query()->where('title', 'Algorithmique importée')->first();
        $this->assertNotNull($course);
        $response->assertRedirect(route('admin.courses.show', $course));

        $this->assertSame(2, $course->chapters()->count());
        $this->assertSame(2, Question::query()->whereHas('quiz.chapter', fn ($query) => $query->where('course_id', $course->id))->count());
        $this->assertTrue(
            Chapter::query()->where('course_id', $course->id)->where('title', 'Les algorithmes')->exists()
        );
    }

    public function test_a_student_cannot_import_a_word_course(): void
    {
        $world = $this->createAcademicWorld();
        $path = $this->makeDocx();

        try {
            $this->actingAs($world['student'])
                ->post(route('admin.courses.import.store'), [
                    'faculty_id' => $world['faculty']->id,
                    'option_id' => $world['option']->id,
                    'promotion_id' => $world['promotion']->id,
                    'document' => new UploadedFile($path, 'cours.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                ])
                ->assertRedirect(route('dashboard'));
        } finally {
            @unlink($path);
        }
    }

    public function test_ajax_import_redirects_to_the_course_with_a_success_message(): void
    {
        $world = $this->createAcademicWorld();
        $super = User::factory()->superAdmin()->create();
        $path = $this->makeDocx();

        try {
            $response = $this->actingAs($super)->postJson(route('admin.courses.import.store'), [
                'faculty_id' => $world['faculty']->id,
                'option_id' => $world['option']->id,
                'promotion_id' => $world['promotion']->id,
                'is_published' => '1',
                'document' => new UploadedFile(
                    $path,
                    'algorithmique.docx',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    null,
                    true,
                ),
            ]);
        } finally {
            @unlink($path);
        }

        $course = Course::query()->where('title', 'Algorithmique importée')->firstOrFail();
        $message = 'Cours importé : 2 chapitre(s).';

        $response
            ->assertOk()
            ->assertJsonPath('message', $message)
            ->assertJsonPath('redirect_url', route('admin.courses.show', $course));

        $this->get(route('admin.courses.show', $course))
            ->assertOk()
            ->assertSee($message);
    }

    public function test_a_faculty_admin_cannot_import_into_another_faculty(): void
    {
        $world = $this->createAcademicWorld();
        $admin = User::factory()->admin()->create(['faculty_id' => $world['faculty']->id]);
        $foreign = $this->createAcademicWorld();
        $path = $this->makeDocx();

        try {
            $this->actingAs($admin)
                ->post(route('admin.courses.import.store'), [
                    'faculty_id' => $foreign['faculty']->id,
                    'option_id' => $foreign['option']->id,
                    'promotion_id' => $foreign['promotion']->id,
                    'document' => new UploadedFile($path, 'cours.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true),
                ])
                ->assertRedirect();
        } finally {
            @unlink($path);
        }

        $this->assertFalse(Course::query()->where('title', 'Algorithmique importée')->exists());
    }

    public function test_an_admin_can_download_the_word_course_template(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.courses.import.template'))
            ->assertOk()
            ->assertDownload(WordCourseTemplateGenerator::FILENAME)
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_the_word_template_can_be_parsed_into_chapters_and_quizzes(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ca-tpl-').'.docx';
        file_put_contents($path, (new WordCourseTemplateGenerator)->bytes());

        try {
            $parsed = (new WordCourseDocumentParser)->parseFile($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame('Titre du cours (à modifier)', $parsed->title);
        $this->assertCount(2, $parsed->chapters);
        $this->assertSame('Chapitre 1 — titre du chapitre', $parsed->chapters[0]->title);
        $this->assertCount(2, $parsed->chapters[0]->questions);
        $this->assertStringContainsString('<strong>algorithme</strong>', $parsed->chapters[0]->content);
        $this->assertStringContainsString('<table>', $parsed->chapters[0]->content);
        $this->assertCount(1, $parsed->chapters[1]->questions);
    }

    public function test_the_import_form_shows_the_upload_size_limit(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.courses.import'))
            ->assertOk()
            ->assertSee('Taille maximale')
            ->assertSee('data-course-import-form', false)
            ->assertSee('data-upload-progress', false)
            ->assertSee('request.upload.addEventListener', false);
    }

    public function test_a_student_cannot_download_the_word_template(): void
    {
        $world = $this->createAcademicWorld();

        $this->actingAs($world['student'])
            ->get(route('admin.courses.import.template'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_an_oversized_post_shows_a_french_error_page(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.courses.import'))
            ->withServerVariables(['CONTENT_LENGTH' => 999_999_999])
            ->post(route('admin.courses.import.store'))
            ->assertStatus(413)
            ->assertSee('trop volumineux', false)
            ->assertDontSee('PostTooLargeException');
    }

    private function makeDocx(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
            .$this->p('Heading1', 'Algorithmique importée')
            .$this->p(null, 'Description du cours importé.')
            .$this->p('Heading2', 'Les algorithmes')
            .$this->p(null, 'Un algorithme est une suite d’instructions.')
            .$this->p('Heading3', 'Interrogation')
            .$this->p(null, '1. Un algorithme doit-il se terminer ?')
            .$this->p(null, 'a) Oui *')
            .$this->p(null, 'b) Non')
            .$this->p('Heading2', 'Complexité')
            .$this->p(null, 'Le grand O mesure le pire des cas.')
            .$this->p(null, '1. Quelle notation pour le pire cas ?')
            .$this->p(null, 'a) Big O (juste)')
            .$this->p(null, 'b) Aléatoire')
            .'</w:body></w:document>';

        $path = tempnam(sys_get_temp_dir(), 'ca').'.docx';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return $path;
    }

    private function p(?string $style, string $text): string
    {
        $styleXml = $style ? '<w:pPr><w:pStyle w:val="'.$style.'"/></w:pPr>' : '';

        return '<w:p>'.$styleXml.'<w:r><w:t>'.htmlspecialchars($text, ENT_XML1).'</w:t></w:r></w:p>';
    }
}
