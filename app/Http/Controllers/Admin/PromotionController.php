<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::query()->withCount(['courses', 'users'])->orderBy('level')->get();

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.form', ['promotion' => new Promotion]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
        $data['slug'] = UniqueSlug::make(Promotion::class, $data['name'], fallback: 'promotion');

        Promotion::query()->create($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion créée.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
        $data['slug'] = UniqueSlug::make(Promotion::class, $data['name'], ignoreId: $promotion->id, fallback: 'promotion');
        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion mise à jour.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        try {
            $promotion->delete();
        } catch (\Throwable) {
            return back()->with('error', 'Impossible de supprimer cette promotion : des cours ou utilisateurs y sont rattachés.');
        }

        return redirect()->route('admin.promotions.index')->with('status', 'Promotion supprimée.');
    }
}
