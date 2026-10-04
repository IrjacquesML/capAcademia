<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentCatalogService;
use App\Support\StudentApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __invoke(Request $request, StudentCatalogService $catalog): JsonResponse
    {
        $user = $request->user();
        $courses = $catalog->coursesWithProgress($user);

        return response()->json([
            'user' => StudentApi::user($user),
            'courses' => $courses->map(fn ($course) => StudentApi::courseCard($course))->values(),
        ]);
    }
}
