<x-layouts.admin :title="$user->exists ? 'Modifier l’utilisateur' : 'Nouvel utilisateur'">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-700">← Utilisateurs</a>
    @if ($user->exists)
        <a href="{{ route('admin.users.audit', $user) }}" class="ml-4 text-sm text-indigo-700">Rapport d’audit</a>
    @endif
    <h1 class="mb-6 mt-3 text-2xl font-semibold">{{ $user->exists ? 'Modifier l’utilisateur' : 'Nouvel utilisateur' }}</h1>

    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <label class="mb-2 block text-sm">Nom</label>
        <input name="name" value="{{ old('name', $user->name) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Mot de passe {{ $user->exists ? '(laisser vide pour ne pas changer)' : '' }}</label>
        <input type="password" name="password" @required(! $user->exists) minlength="8" class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Rôle</label>
        <select name="role" id="role" required class="mb-4 w-full rounded-lg border px-3 py-2">
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>

        <x-academic-cascade
            :faculties="$faculties"
            :options-json="$optionsJson"
            :promotions="$promotions"
            :selected-faculty="old('faculty_id', $user->faculty_id ?? $actor->faculty_id)"
            :selected-option="old('option_id', $user->option_id)"
            :selected-promotion="old('promotion_id', $user->promotion_id)"
            :lock-faculty="$actor->isAdmin() && (bool) $actor->faculty_id"
            :require-option="false"
            :require-promotion="false"
        />

        @if ($errors->any())
            <ul class="mb-4 list-disc ps-5 text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <button class="min-h-12 w-full rounded-xl bg-indigo-600 px-4 py-3 text-white sm:w-auto">Enregistrer</button>
    </form>
</x-layouts.admin>
