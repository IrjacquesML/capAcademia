<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.meta />
    <title>Connexion — CapAcademia</title>
</head>
<body class="min-h-screen bg-indigo-600 sm:bg-slate-100">
    <div class="flex min-h-screen flex-col sm:items-center sm:justify-center sm:p-6"
         style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex flex-1 flex-col justify-center px-6 py-10 text-white sm:hidden">
            <x-brand size="lg" light class="mb-3" />
            <h1 class="mt-2 text-3xl font-semibold">Vos cours, toujours avec vous</h1>
            <p class="mt-3 text-indigo-100">Connectez-vous pour continuer l’étude sur téléphone comme sur ordinateur.</p>
        </div>

        <form method="POST" action="{{ route('login') }}"
              class="w-full rounded-t-3xl bg-white p-6 shadow-lg sm:max-w-sm sm:rounded-2xl sm:p-8">
            @csrf
            <x-brand size="lg" class="mb-4 hidden sm:flex" />
            <h2 class="mb-6 text-xl font-semibold text-slate-900">Connexion</h2>

            <label class="mb-2 block text-sm">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            <label class="mb-2 block text-sm">Mot de passe</label>
            <input type="password" name="password" required autocomplete="current-password"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            @error('email')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button class="min-h-12 w-full rounded-xl bg-indigo-600 py-3 font-medium text-white hover:bg-indigo-700">
                Se connecter
            </button>
            <p class="mt-4 text-center text-sm text-slate-600">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="font-medium text-indigo-700 hover:underline">Créer un compte étudiant</a>
            </p>
        </form>
    </div>
</body>
</html>
