<x-layouts.admin :title="$faculty->exists ? 'Modifier la faculté' : 'Nouvelle faculté'">
    <a href="{{ route('admin.faculties.index') }}" class="text-sm text-indigo-700">← Facultés</a>
    <h1 class="mb-6 mt-3 text-2xl font-semibold">{{ $faculty->exists ? 'Modifier la faculté' : 'Nouvelle faculté' }}</h1>

    <form method="POST"
          action="{{ $faculty->exists ? route('admin.faculties.update', $faculty) : route('admin.faculties.store') }}"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @if ($faculty->exists)
            @method('PUT')
        @endif

        <label class="mb-2 block text-sm">Nom</label>
        <input name="name" value="{{ old('name', $faculty->name) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Code (optionnel)</label>
        <input name="code" value="{{ old('code', $faculty->code) }}" class="mb-6 w-full rounded-lg border px-3 py-2">

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
