<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use App\Support\HtmlSanitizer;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $position = ((int) $course->chapters()->max('position')) + 1;

        Chapter::query()->create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'content' => $this->sanitizeContent($data['content']),
            'position' => $position,
            'slug' => UniqueSlug::make(Chapter::class, $data['title'], ['course_id' => $course->id], fallback: 'chapitre').'-'.$position,
            'is_published' => true,
        ]);

        return redirect()->route('admin.courses.show', $course)->with('status', 'Chapitre ajouté.');
    }

    public function edit(Request $request, Course $course, Chapter $chapter): View
    {
        $this->authorize('update', $course);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);
        $chapter->loadMissing('quiz');

        return view('admin.chapters.form', compact('course', 'chapter'));
    }

    public function update(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $chapter->update([
            'title' => $data['title'],
            'content' => $this->sanitizeContent($data['content']),
            'is_published' => $request->boolean('is_published', $chapter->is_published),
            'slug' => UniqueSlug::make(Chapter::class, $data['title'], ['course_id' => $course->id], $chapter->id, 'chapitre'),
        ]);

        return redirect()->route('admin.courses.show', $course)->with('status', 'Chapitre mis à jour.');
    }

    public function destroy(Request $request, Course $course, Chapter $chapter): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless((int) $chapter->course_id === (int) $course->id, 404);

        $chapter->delete();

        $course->chapters()->orderBy('position')->get()->each(function (Chapter $item, int $index): void {
            $item->update(['position' => $index + 1]);
        });

        return redirect()->route('admin.courses.show', $course)->with('status', 'Chapitre supprimé.');
    }

    private function sanitizeContent(string $content): string
    {
        $clean = $this->sanitizer->looksLikeHtml($content)
            ? $this->sanitizer->sanitize($content)
            : $content;

        return trim($clean) !== '' ? $clean : $content;
    }
}
