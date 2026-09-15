<x-layouts.app title="Accueil">
    <p class="text-sm text-slate-500">{{ $user->roleLabel() }}</p>
    <h1 class="mb-1 text-xl font-semibold sm:mb-2 sm:text-2xl">Bonjour, {{ $user->name }}</h1>
    <p class="mb-5 text-sm text-slate-600 sm:mb-8 sm:text-base">
        {{ $user->faculty?->name ?? 'Toutes facultés' }}
        · {{ $user->option?->name ?? 'Toutes options' }}
        · {{ $user->promotion?->name ?? 'Toutes promotions' }}
    </p>

    <div class="mb-5 grid grid-cols-3 gap-2 sm:mb-8 sm:gap-4">
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Cours</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $courses->count() }}</p>
        </div>
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Chapitres avancés</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $chaptersDone }} / {{ $chaptersTotal }}</p>
        </div>
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Interrogations soumises</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $attemptCount }}</p>
        </div>
    </div>

    @if ($resume)
        <a href="{{ route('chapters.show', [$resume['course'], $resume['chapter']]) }}"
           class="mb-5 flex items-center gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 hover:border-indigo-400 sm:mb-8 sm:p-5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white">
                <x-icon name="play" class="h-5 w-5" />
            </span>
            <span class="min-w-0">
                <p class="text-sm font-medium text-indigo-700">Continuer l’étude</p>
                <p class="truncate font-semibold">{{ $resume['course']->title }}</p>
                <p class="truncate text-sm text-slate-600">Chapitre {{ $resume['chapter']->position }} — {{ $resume['chapter']->title }}</p>
            </span>
        </a>
    @endif

    <div class="mb-3 flex items-center justify-between sm:mb-4">
        <h2 class="font-medium">Mes cours</h2>
        <a href="{{ route('courses.index') }}" class="text-sm text-indigo-700">Tout voir</a>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:hidden">
        @forelse ($courses as $course)
            <a href="{{ route('courses.show', $course) }}"
               class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm hover:border-indigo-300">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-sm font-semibold text-indigo-700">
                    {{ $course->progress_percent }} %
                </span>
                <h3 class="mt-3 line-clamp-2 text-sm font-medium leading-tight">{{ $course->title }}</h3>
                <p class="mt-1 text-[11px] text-slate-500">
                    {{ $course->progress_done }}/{{ $course->progress_total }} chapitres
                </p>
            </a>
        @empty
            <p class="col-span-2 rounded-2xl border border-dashed bg-white p-8 text-center text-slate-500">
                Aucun cours n'est disponible pour votre faculté, option et promotion.
            </p>
        @endforelse
    </div>

    <div class="hidden sm:block">
        @forelse ($courses as $course)
            <a href="{{ route('courses.show', $course) }}"
               class="mb-3 block rounded-xl border bg-white p-5 hover:border-indigo-300">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="font-medium">{{ $course->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $course->progress_done }}/{{ $course->progress_total }} chapitres
                        </p>
                    </div>
                    <span class="text-sm text-indigo-700">{{ $course->progress_percent }} %</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-indigo-600" style="width: {{ $course->progress_percent }}%"></div>
                </div>
            </a>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">
                Aucun cours n'est disponible pour votre faculté, option et promotion.
            </p>
        @endforelse
    </div>
</x-layouts.app>
