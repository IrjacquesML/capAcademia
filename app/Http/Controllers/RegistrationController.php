<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        $faculties = Faculty::query()
            ->whereHas('courses', fn ($query) => $query->published())
            ->with([
                'options' => fn ($query) => $query
                    ->whereHas('courses', fn ($query) => $query->published())
                    ->select(['id', 'faculty_id', 'name']),
            ])
            ->orderBy('name')
            ->get();
        $promotionsJson = Course::query()
            ->published()
            ->with('promotion:id,name')
            ->get(['faculty_id', 'option_id', 'promotion_id'])
            ->groupBy('faculty_id')
            ->map(fn ($facultyCourses) => $facultyCourses
                ->groupBy('option_id')
                ->map(fn ($optionCourses) => $optionCourses
                    ->unique('promotion_id')
                    ->map(fn (Course $course) => [
                        'id' => $course->promotion->id,
                        'name' => $course->promotion->name,
                    ])
                    ->values()));
        $optionsJson = $faculties->mapWithKeys(
            fn (Faculty $faculty) => [
                $faculty->id => $faculty->options->map(fn (Option $option) => [
                    'id' => $option->id,
                    'name' => $option->name,
                ])->values(),
            ],
        );

        return view('auth.register', compact('faculties', 'optionsJson', 'promotionsJson'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'option_id' => [
                'required',
                Rule::exists('options', 'id')->where('faculty_id', $request->input('faculty_id')),
            ],
            'promotion_id' => ['required', 'exists:promotions,id'],
        ]);

        $hasPublishedCourse = Course::query()
            ->published()
            ->where('faculty_id', $data['faculty_id'])
            ->where('option_id', $data['option_id'])
            ->where('promotion_id', $data['promotion_id'])
            ->exists();

        if (! $hasPublishedCourse) {
            return back()
                ->withInput()
                ->withErrors(['promotion_id' => 'Aucun cours publié ne correspond à cette faculté, cette option et cette promotion.']);
        }

        $user = User::query()->create([
            ...$data,
            'role' => UserRole::Student,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
