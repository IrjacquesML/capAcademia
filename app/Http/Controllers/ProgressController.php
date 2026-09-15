<?php

namespace App\Http\Controllers;

use App\Services\StudentCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __invoke(Request $request, StudentCatalogService $catalog): View
    {
        $user = $request->user();
        $user->loadMissing(['faculty:id,name', 'option:id,name', 'promotion:id,name']);
        $courses = $catalog->coursesWithProgress($user);

        return view('progress.index', compact('user', 'courses'));
    }
}
