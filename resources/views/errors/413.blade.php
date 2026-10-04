<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.meta />
    <title>Fichier trop volumineux — CapAcademia</title>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-lg px-4 py-16 sm:py-24" style="padding-top: max(4rem, env(safe-area-inset-top))">
        <x-brand class="mb-2" />
        <h1 class="mt-2 text-2xl font-semibold">Le fichier est trop volumineux</h1>
        <p class="mt-4 text-slate-600">
            L’envoi dépasse la taille maximale autorisée
            ({{ $maxLabel ?? 'limite du serveur' }}).
            Un cours Word avec beaucoup d’images peut atteindre plusieurs dizaines de mégaoctets.
        </p>
        <p class="mt-3 text-sm text-slate-500">
            Réduisez le poids des images dans Word, ou découpez le cours en plusieurs fichiers.
        </p>
        <a href="{{ $backUrl ?? url()->previous() ?: url('/') }}"
           class="mt-8 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-white hover:bg-indigo-700 sm:w-auto">
            Retour
        </a>
    </main>
</body>
</html>
