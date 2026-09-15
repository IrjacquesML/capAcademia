<x-layouts.admin :title="$promotion->exists ? 'Modifier la promotion' : 'Nouvelle promotion'">
    <a href="{{ route('admin.promotions.index') }}" class="text-sm text-indigo-700">← Promotions</a>
    <h1 class="mb-6 mt-3 text-2xl font-semibold">{{ $promotion->exists ? 'Modifier la promotion' : 'Nouvelle promotion' }}</h1>

    <form method="POST"
          action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}"
          class="w-full max-w-lg rounded-xl border bg-white p-4 sm:p-6">
        @csrf
        @if ($promotion->exists)
            @method('PUT')
        @endif

        <label class="mb-2 block text-sm">Nom</label>
        <input name="name" value="{{ old('name', $promotion->name) }}" required class="mb-4 w-full rounded-lg border px-3 py-2">

        <label class="mb-2 block text-sm">Niveau (1 = L1, 2 = L2…)</label>
        <input type="number" min="1" max="10" name="level" value="{{ old('level', $promotion->level ?? 1) }}" required
               class="mb-6 w-full rounded-lg border px-3 py-2">

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
