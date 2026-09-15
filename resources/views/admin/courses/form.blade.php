<x-layouts.admin :title="$course->exists ? 'Modifier le cours' : 'Nouveau cours'">
    <a href="{{ route('admin.courses.index') }}" class="text-sm text-indigo-700">← Cours</a>
    <h1 class="mb-6 mt-3 text-2xl font-semibold">{{ $course->exists ? 'Modifier le cours' : 'Nouveau cours' }}</h1>

    <form method="POST"
          action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @if ($course->exists)
            @method('PUT')
        @endif

        <label class="mb-2 block text-sm">Titre</label>
        <input name="title" value="{{ old('title', $course->title) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Description</label>
        <textarea name="description" rows="4" class="mb-4 w-full rounded-lg border px-3 py-2">{{ old('description', $course->description) }}</textarea>

        <x-academic-cascade
            :faculties="$faculties"
            :options-json="$optionsJson"
            :promotions="$promotions"
            :selected-faculty="old('faculty_id', $course->faculty_id ?? auth()->user()->faculty_id)"
            :selected-option="old('option_id', $course->option_id)"
            :selected-promotion="old('promotion_id', $course->promotion_id)"
            :lock-faculty="auth()->user()->isAdmin() && (bool) auth()->user()->faculty_id"
        />

        <label class="mb-6 flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published))>
            Publier (visible des étudiants du triplet académique)
        </label>

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
