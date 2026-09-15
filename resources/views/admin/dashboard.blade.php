<x-layouts.admin title="Tableau de bord">
    <p class="text-sm text-slate-500">{{ $user->roleLabel() }}</p>
    <h1 class="mb-1 text-xl font-semibold sm:mb-2 sm:text-2xl">Administration CapAcademia</h1>
    <p class="mb-5 text-sm text-slate-600 sm:mb-8 sm:text-base">
        {{ $user->isSuperAdmin()
            ? 'Vous gérez toute la plateforme : facultés, utilisateurs, cours et imports Word.'
            : 'Vous gérez les utilisateurs (étudiants et enseignants) et les cours de votre périmètre.' }}
    </p>

    <div class="mb-5 grid grid-cols-2 gap-2 sm:mb-8 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4">
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Facultés</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $stats['faculties'] }}</p>
        </div>
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Utilisateurs</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $stats['users'] }}</p>
        </div>
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Cours</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $stats['courses'] }}</p>
        </div>
        <div class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-2 text-center shadow-sm sm:aspect-auto sm:items-start sm:p-5 sm:text-left">
            <p class="line-clamp-2 text-[11px] font-medium leading-tight text-slate-500 sm:text-sm">Cours publiés</p>
            <p class="mt-1 text-xl font-semibold tabular-nums sm:text-2xl">{{ $stats['published'] }}</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-3 gap-2 sm:mb-8 sm:flex sm:flex-wrap sm:gap-3">
        <a href="{{ route('admin.courses.create') }}"
           class="flex aspect-square min-w-0 flex-col items-center justify-center gap-2 rounded-2xl bg-indigo-600 p-2 text-center text-[11px] font-medium leading-tight text-white shadow-sm hover:bg-indigo-700 sm:aspect-auto sm:min-h-11 sm:flex-row sm:px-4 sm:py-2 sm:text-sm">
            <x-icon name="plus" class="h-6 w-6 sm:h-4 sm:w-4" />
            Nouveau cours
        </a>
        <a href="{{ route('admin.courses.import') }}"
           class="flex aspect-square min-w-0 flex-col items-center justify-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 p-2 text-center text-[11px] font-medium leading-tight text-indigo-800 shadow-sm hover:border-indigo-400 sm:aspect-auto sm:min-h-11 sm:flex-row sm:px-4 sm:py-2 sm:text-sm">
            <x-icon name="import" class="h-6 w-6 sm:h-4 sm:w-4" />
            Importer un Word
        </a>
        <a href="{{ route('admin.courses.import.template') }}"
           class="flex aspect-square min-w-0 flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-center text-[11px] font-medium leading-tight text-slate-800 shadow-sm hover:border-indigo-300 sm:aspect-auto sm:min-h-11 sm:flex-row sm:px-4 sm:py-2 sm:text-sm">
            <x-icon name="download" class="h-6 w-6 sm:h-4 sm:w-4" />
            Modèle Word
        </a>
        <a href="{{ route('admin.users.create') }}"
           class="flex aspect-square min-w-0 flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-center text-[11px] font-medium leading-tight text-slate-800 shadow-sm hover:border-indigo-300 sm:aspect-auto sm:min-h-11 sm:flex-row sm:px-4 sm:py-2 sm:text-sm">
            <x-icon name="users" class="h-6 w-6 sm:h-4 sm:w-4" />
            Nouvel utilisateur
        </a>
        @if ($user->isSuperAdmin())
            <a href="{{ route('admin.faculties.create') }}"
               class="flex aspect-square min-w-0 flex-col items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 text-center text-[11px] font-medium leading-tight text-slate-800 shadow-sm hover:border-indigo-300 sm:aspect-auto sm:min-h-11 sm:flex-row sm:px-4 sm:py-2 sm:text-sm">
                <x-icon name="building" class="h-6 w-6 sm:h-4 sm:w-4" />
                Nouvelle faculté
            </a>
        @endif
    </div>

    <div class="mb-8">
        <div class="mb-3 flex items-center justify-between sm:mb-4">
            <h2 class="font-medium">Derniers mouvements</h2>
            <a href="{{ route('admin.notifications.index') }}" class="text-sm text-indigo-700">Tout voir</a>
        </div>

        <div class="overflow-hidden rounded-2xl border bg-white">
            @forelse ($latestActivity as $event)
                <a href="{{ $event->user ? route('admin.users.audit', $event->user) : route('admin.notifications.index') }}"
                   class="block border-b px-4 py-3 last:border-b-0 hover:bg-slate-50">
                    <p class="text-sm font-medium">{{ $event->user?->name ?? 'Utilisateur' }} · {{ $event->action->label() }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $event->description() }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">{{ $event->created_at?->format('d/m/Y H:i') }}</p>
                </a>
            @empty
                <p class="px-4 py-8 text-center text-sm text-slate-500">
                    Aucune notification pour le moment. Les chapitres lus et les interrogations terminées apparaîtront ici.
                </p>
            @endforelse
        </div>
    </div>

    <h2 class="mb-3 font-medium sm:mb-4">Derniers cours</h2>

    <div class="grid grid-cols-2 gap-3 sm:hidden">
        @forelse ($latestCourses as $course)
            <a href="{{ route('admin.courses.show', $course) }}"
               class="flex aspect-square min-w-0 flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm hover:border-indigo-300">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700">
                    <x-icon name="book" class="h-5 w-5" />
                </span>
                <p class="mt-3 line-clamp-2 text-sm font-medium leading-tight">{{ $course->title }}</p>
                <p class="mt-1 line-clamp-2 text-[11px] leading-tight text-slate-500">
                    {{ $course->faculty->name }} · {{ $course->option->name }}
                </p>
                <p class="mt-1 text-[11px] font-medium {{ $course->is_published ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                </p>
            </a>
        @empty
            <p class="col-span-2 rounded-2xl border border-dashed bg-white p-8 text-center text-slate-500">
                Aucun cours pour le moment. Créez-en un ou importez un fichier Word.
            </p>
        @endforelse
    </div>

    <div class="hidden sm:block">
        @forelse ($latestCourses as $course)
            <a href="{{ route('admin.courses.show', $course) }}"
               class="mb-3 block rounded-xl border bg-white p-5 hover:border-indigo-300">
                <p class="font-medium">{{ $course->title }}</p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $course->faculty->name }} · {{ $course->option->name }} · {{ $course->promotion->name }}
                    · {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                </p>
            </a>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-8 text-center text-slate-500">
                Aucun cours pour le moment. Créez-en un ou importez un fichier Word.
            </p>
        @endforelse
    </div>
</x-layouts.admin>
