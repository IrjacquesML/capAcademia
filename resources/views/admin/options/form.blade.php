<x-layouts.admin :title="$option->exists ? 'Modifier l’option' : 'Nouvelle option'">
    <a href="{{ route('admin.options.index') }}" class="text-sm text-indigo-700">← Options</a>
    <h1 class="mb-6 mt-3 text-2xl font-semibold">{{ $option->exists ? 'Modifier l’option' : 'Nouvelle option' }}</h1>

    <form method="POST"
          action="{{ $option->exists ? route('admin.options.update', $option) : route('admin.options.store') }}"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @if ($option->exists)
            @method('PUT')
        @endif

        <label class="mb-2 block text-sm">Faculté</label>
        <select name="faculty_id" required class="mb-4 w-full rounded-lg border px-3 py-2">
            @foreach ($faculties as $faculty)
                <option value="{{ $faculty->id }}" @selected((string) old('faculty_id', $option->faculty_id) === (string) $faculty->id)>
                    {{ $faculty->name }}
                </option>
            @endforeach
        </select>

        <label class="mb-2 block text-sm">Nom</label>
        <input name="name" value="{{ old('name', $option->name) }}" required class="mb-6 w-full rounded-lg border px-3 py-2">

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
