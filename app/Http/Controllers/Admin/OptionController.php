<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Option;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OptionController extends Controller
{
    public function index(): View
    {
        $options = Option::query()->with('faculty')->withCount(['courses', 'users'])->orderBy('name')->get();

        return view('admin.options.index', compact('options'));
    }

    public function create(): View
    {
        return view('admin.options.form', [
            'option' => new Option,
            'faculties' => Faculty::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $data['slug'] = UniqueSlug::make(Option::class, $data['name'], ['faculty_id' => $data['faculty_id']], fallback: 'option');

        Option::query()->create($data);

        return redirect()->route('admin.options.index')->with('status', 'Option créée.');
    }

    public function edit(Option $option): View
    {
        return view('admin.options.form', [
            'option' => $option,
            'faculties' => Faculty::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Option $option): RedirectResponse
    {
        $data = $request->validate([
            'faculty_id' => ['required', 'exists:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        $data['slug'] = UniqueSlug::make(Option::class, $data['name'], ['faculty_id' => $data['faculty_id']], $option->id, 'option');
        $option->update($data);

        return redirect()->route('admin.options.index')->with('status', 'Option mise à jour.');
    }

    public function destroy(Option $option): RedirectResponse
    {
        try {
            $option->delete();
        } catch (\Throwable) {
            return back()->with('error', 'Impossible de supprimer cette option : des cours ou utilisateurs y sont rattachés.');
        }

        return redirect()->route('admin.options.index')->with('status', 'Option supprimée.');
    }
}
