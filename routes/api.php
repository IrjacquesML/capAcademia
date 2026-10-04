<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:api-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/me', [ProfileController::class, 'show']);
    Route::get('/dashboard', DashboardController::class);
    Route::get('/progress', ProgressController::class);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    Route::get('/courses/{course}/chapters/{chapter}', [ChapterController::class, 'show'])->scopeBindings();
    Route::post('/courses/{course}/chapters/{chapter}/read', [ChapterController::class, 'markAsRead'])->scopeBindings();

    Route::post('/courses/{course}/chapters/{chapter}/quizzes/{quiz}', [QuizAttemptController::class, 'store'])
        ->middleware('throttle:quiz-submissions')
        ->scopeBindings();

    Route::get('/courses/{course}/chapters/{chapter}/quizzes/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'show'])
        ->scopeBindings();
});
