<?php

namespace App\Providers;

use App\Listeners\RecordAuthenticationAudit;
use App\Models\Course;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(
            ServeCommand::class,
            fn () => $this->app->make(\App\Console\Commands\ServeCommand::class),
        );
    }

    public function boot(): void
    {
        RateLimiter::for('quiz-submissions', function (Request $request) {
            return Limit::perMinute(8)->by((string) $request->user()?->id);
        });

        Event::listen(Login::class, [RecordAuthenticationAudit::class, 'handleLogin']);
        Event::listen(Logout::class, [RecordAuthenticationAudit::class, 'handleLogout']);
        Event::listen(Failed::class, [RecordAuthenticationAudit::class, 'handleFailed']);

        // Binding global : un ID de cours hors triplet académique renvoie 404, pas 403.
        Route::bind('course', function (string $value) {
            $user = auth()->user();
            abort_unless($user, 401);

            return Course::query()
                ->visibleTo($user)
                ->whereKey($value)
                ->firstOrFail();
        });
    }
}
