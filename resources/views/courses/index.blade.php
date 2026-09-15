<x-layouts.app title="Mes cours">
    <h1 class="mb-2 text-xl font-semibold sm:text-2xl">Mes cours</h1>
    <p class="mb-8 text-slate-600">
        {{ $user->faculty?->name }} · {{ $user->option?->name }} · {{ $user->promotion?->name }}
    </p>

    @forelse ($courses as $course)
        <a href="{{ route('courses.show', $course) }}"
           class="mb-3 block rounded-xl border bg-white p-5 hover:border-indigo-300">
            <h2 class="font-medium">{{ $course->title }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $course->published_chapters_count }} chapitre(s)
            </p>
        </a>
    @empty
        <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">
            Aucun cours n'est disponible pour votre faculté, option et promotion.
        </p>
    @endforelse
</x-layouts.app>
