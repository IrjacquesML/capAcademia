<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacultyController extends Controller
{
    public function index(): View
    {
        $faculties = Faculty::query()->withCount(['options', 'courses', 'users'])->orderBy('name')->get();

        return view('admin.faculties.index', compact('faculties'));
    }

    public function create(): View
    {
        return view('admin.faculties.form', ['faculty' => new Faculty]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32', 'unique:faculties,code'],
        ]);
        $data['slug'] = UniqueSlug::make(Faculty::class, $data['name'], fallback: 'faculte');

        Faculty::query()->create($data);

        return redirect()->route('admin.faculties.index')->with('status', 'Faculté créée.');
    }

    public function edit(Faculty $faculty): View
    {
        return view('admin.faculties.form', compact('faculty'));
    }

    public function update(Request $request, Faculty $faculty): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32', 'unique:faculties,code,'.$faculty->id],
        ]);
        $data['slug'] = UniqueSlug::make(Faculty::class, $data['name'], ignoreId: $faculty->id, fallback: 'faculte');
        $faculty->update($data);

        return redirect()->route('admin.faculties.index')->with('status', 'Faculté mise à jour.');
    }

    public function destroy(Faculty $faculty): RedirectResponse
    {
        try {
            $faculty->delete();
        } catch (\Throwable) {
            return back()->with('error', 'Impossible de supprimer cette faculté : des options, cours ou utilisateurs y sont encore rattachés.');
        }

        return redirect()->route('admin.faculties.index')->with('status', 'Faculté supprimée.');
    }
}
