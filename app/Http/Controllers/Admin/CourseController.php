<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->visibleTo($request->user())
            ->with(['faculty:id,name', 'option:id,name', 'promotion:id,name'])
            ->withCount('chapters')
            ->latest()
            ->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(Request $request): View
    {
        return view('admin.courses.form', $this->formData($request, new Course(['is_published' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->assertActorCanAssign($request, $data);

        $data['slug'] = UniqueSlug::make(Course::class, $data['title'], [
            'faculty_id' => $data['faculty_id'],
            'option_id' => $data['option_id'],
            'promotion_id' => $data['promotion_id'],
        ], fallback: 'cours');
        $data['is_published'] = $request->boolean('is_published');

        $course = Course::query()->create($data);

        return redirect()->route('admin.courses.show', $course)->with('status', 'Cours créé.');
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        $course->load([
            'faculty:id,name',
            'option:id,name',
            'promotion:id,name',
            'chapters.quiz.questions',
        ]);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        return view('admin.courses.form', $this->formData($request, $course));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $data = $this->validated($request);
        $this->assertActorCanAssign($request, $data);

        $data['slug'] = UniqueSlug::make(Course::class, $data['title'], [
            'faculty_id' => $data['faculty_id'],
            'option_id' => $data['option_id'],
            'promotion_id' => $data['promotion_id'],
        ], $course->id, 'cours');
        $data['is_published'] = $request->boolean('is_published');

        $course->update($data);

        return redirect()->route('admin.courses.show', $course)->with('status', 'Cours mis à jour.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Cours supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request, Course $course): array
    {
        $faculties = Faculty::query()
            ->visibleToStaff($request->user())
            ->with('options:id,faculty_id,name')
            ->orderBy('name')
            ->get();

        return [
            'course' => $course,
            'faculties' => $faculties,
            'promotions' => Promotion::query()->orderBy('level')->get(),
            'optionsJson' => $faculties->mapWithKeys(
                fn (Faculty $faculty) => [
                    $faculty->id => $faculty->options->map(fn (Option $option) => [
                        'id' => $option->id,
                        'name' => $option->name,
                    ])->values(),
                ],
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'option_id' => ['required', 'exists:options,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
        ]);

        $optionFacultyId = Option::query()->whereKey($data['option_id'])->value('faculty_id');
        abort_unless((int) $optionFacultyId === (int) $data['faculty_id'], 422, 'L’option ne correspond pas à la faculté.');

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertActorCanAssign(Request $request, array $data): void
    {
        $probe = new Course($data);
        abort_unless($request->user()->canAccessCourse($probe), 403);
    }
}
