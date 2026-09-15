<?php

use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\ChapterQuizController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\CourseImportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\UserAuditController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizAttemptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->homeRoute());
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/accueil', DashboardController::class)->name('dashboard');
    Route::get('/progression', ProgressController::class)->name('progress');
    Route::get('/profil', ProfileController::class)->name('profile');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    Route::get('/courses/{course}/chapters/{chapter}', [ChapterController::class, 'show'])
        ->scopeBindings()
        ->name('chapters.show');

    Route::post('/courses/{course}/chapters/{chapter}/read', [ChapterController::class, 'markAsRead'])
        ->scopeBindings()
        ->name('chapters.read');

    Route::post('/courses/{course}/chapters/{chapter}/quizzes/{quiz}', [QuizAttemptController::class, 'store'])
        ->middleware('throttle:quiz-submissions')
        ->scopeBindings()
        ->name('quizzes.submit');

    Route::get('/courses/{course}/chapters/{chapter}/quizzes/{quiz}/attempts/{attempt}', [QuizAttemptController::class, 'show'])
        ->scopeBindings()
        ->name('quizzes.result');
});

Route::middleware(['auth', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [AdminNotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [AdminNotificationController::class, 'show'])->name('notifications.show');

    Route::get('/courses/import', [CourseImportController::class, 'create'])->name('courses.import');
    Route::get('/courses/import/modele', [CourseImportController::class, 'template'])->name('courses.import.template');
    Route::post('/courses/import', [CourseImportController::class, 'store'])->name('courses.import.store');

    Route::resource('courses', AdminCourseController::class);
    Route::post('/courses/{course}/chapters', [AdminChapterController::class, 'store'])->name('chapters.store');
    Route::get('/courses/{course}/chapters/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('chapters.edit');
    Route::put('/courses/{course}/chapters/{chapter}', [AdminChapterController::class, 'update'])->name('chapters.update');
    Route::delete('/courses/{course}/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('chapters.destroy');

    Route::get('/courses/{course}/chapters/{chapter}/interrogation', [ChapterQuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/courses/{course}/chapters/{chapter}/interrogation', [ChapterQuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/courses/{course}/chapters/{chapter}/interrogation', [ChapterQuizController::class, 'destroy'])->name('quizzes.destroy');

    Route::get('/users/{user}/audit', [UserAuditController::class, 'show'])->name('users.audit');
    Route::resource('users', AdminUserController::class)->except(['show']);

    Route::middleware('superadmin')->group(function () {
        Route::resource('faculties', FacultyController::class)->except(['show']);
        Route::resource('options', OptionController::class)->except(['show']);
        Route::resource('promotions', PromotionController::class)->except(['show']);
    });
});
