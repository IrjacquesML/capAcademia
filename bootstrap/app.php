<?php

use App\Http\Middleware\EnsureStaff;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Support\UploadLimits;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'staff' => EnsureStaff::class,
            'superadmin' => EnsureSuperAdmin::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route(auth()->user()->homeRoute()));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport(PostTooLargeException::class);

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $maxLabel = UploadLimits::phpPostMaxLabel();
            $message = 'Le fichier est trop volumineux (limite '.$maxLabel.'). Réduisez les images du document Word ou découpez le cours.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.413', [
                'maxLabel' => $maxLabel,
                'backUrl' => url()->previous() ?: url('/'),
            ], 413);
        });
    })->create();
