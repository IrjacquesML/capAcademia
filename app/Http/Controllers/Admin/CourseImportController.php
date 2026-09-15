<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Services\WordImport\WordCourseImporter;
use App\Services\WordImport\WordCourseTemplateGenerator;
use App\Support\UploadLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class CourseImportController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Course::class);

        $faculties = Faculty::query()
            ->visibleToStaff($request->user())
            ->with('options:id,faculty_id,name')
            ->orderBy('name')
            ->get();

        return view('admin.courses.import', [
            'faculties' => $faculties,
            'promotions' => Promotion::query()->orderBy('level')->get(),
            'maxUploadLabel' => UploadLimits::wordImportMaxMegabytesLabel(),
            'optionsJson' => $faculties->mapWithKeys(
                fn (Faculty $faculty) => [
                    $faculty->id => $faculty->options->map(fn (Option $option) => [
                        'id' => $option->id,
                        'name' => $option->name,
                    ])->values(),
                ],
            ),
        ]);
    }

    public function template(WordCourseTemplateGenerator $generator): Response
    {
        $this->authorize('create', Course::class);

        return response($generator->bytes(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.WordCourseTemplateGenerator::FILENAME.'"',
        ]);
    }

    public function store(Request $request, WordCourseImporter $importer): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $maxKilobytes = UploadLimits::wordImportMaxKilobytes();
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'option_id' => ['required', 'exists:options,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
            'document' => ['required', 'file', 'max:'.$maxKilobytes],
            'is_published' => ['sometimes', 'boolean'],
        ], [
            'document.max' => 'Le fichier Word est trop volumineux (maximum '.UploadLimits::wordImportMaxMegabytesLabel().').',
        ]);

        try {
            $course = $importer->import(
                actor: $request->user(),
                file: $request->file('document'),
                facultyId: (int) $data['faculty_id'],
                optionId: (int) $data['option_id'],
                promotionId: (int) $data['promotion_id'],
                publish: $request->boolean('is_published', true),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'L’import a échoué. Vérifiez que le fichier est un .docx bien structuré.');
        }

        return redirect()
            ->route('admin.courses.show', $course)
            ->with('status', 'Cours importé : '.$course->chapters->count().' chapitre(s).');
    }
}
