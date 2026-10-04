<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.meta />
    <title>Créer un compte — CapAcademia</title>
</head>
<body class="min-h-screen bg-indigo-600 sm:bg-slate-100">
    <div class="flex min-h-screen flex-col sm:items-center sm:justify-center sm:p-6"
         style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom)">
        <div class="flex flex-1 flex-col justify-center px-6 py-10 text-white sm:hidden">
            <x-brand size="lg" light class="mb-3" />
            <h1 class="mt-2 text-3xl font-semibold">Rejoignez CapAcademia</h1>
            <p class="mt-3 text-indigo-100">Créez votre compte étudiant pour accéder à vos cours.</p>
        </div>

        <form method="POST" action="{{ route('register') }}"
              class="w-full rounded-t-3xl bg-white p-6 shadow-lg sm:max-w-lg sm:rounded-2xl sm:p-8">
            @csrf
            <x-brand size="lg" class="mb-4 hidden sm:flex" />
            <h2 class="mb-6 text-xl font-semibold text-slate-900">Créer un compte étudiant</h2>

            <label for="name" class="mb-2 block text-sm">Nom complet</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            <label for="email" class="mb-2 block text-sm">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            <label for="password" class="mb-2 block text-sm">Mot de passe</label>
            <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            <label for="password_confirmation" class="mb-2 block text-sm">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                   class="mb-4 min-h-12 w-full rounded-xl border px-3 py-3">

            <x-academic-cascade
                :faculties="$faculties"
                :options-json="$optionsJson"
                :promotions="null"
                :promotions-json="$promotionsJson"
                :selected-faculty="old('faculty_id')"
                :selected-option="old('option_id')"
                :selected-promotion="old('promotion_id')"
            />

            @if ($errors->any())
                <ul class="mb-4 list-disc ps-5 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <button class="min-h-12 w-full rounded-xl bg-indigo-600 py-3 font-medium text-white hover:bg-indigo-700">
                Créer mon compte
            </button>
            <p class="mt-4 text-center text-sm text-slate-600">
                Vous avez déjà un compte ?
                <a href="{{ route('login') }}" class="font-medium text-indigo-700 hover:underline">Se connecter</a>
            </p>
        </form>
    </div>
</body>
</html>
