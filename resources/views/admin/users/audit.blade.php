<x-layouts.admin :title="'Audit — '.$user->name">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-700">← Utilisateurs</a>
    <div class="mb-6 mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Rapport d’audit</h1>
            <p class="mt-1 text-slate-600">{{ $user->name }} · {{ $user->email }} · {{ $user->roleLabel() }}</p>
            <p class="text-sm text-slate-500">
                {{ $user->faculty?->name ?? 'Toutes facultés' }}
                · {{ $user->option?->name ?? 'Toutes options' }}
                · {{ $user->promotion?->name ?? 'Toutes promotions' }}
                · inscrit le {{ $user->created_at?->format('d/m/Y') }}
            </p>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border px-4 py-2 text-sm">Modifier le compte</a>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-white p-5">
            <p class="text-sm text-slate-500">Connexions</p>
            <p class="mt-1 text-2xl font-semibold">{{ $stats['logins'] }}</p>
            <p class="mt-1 text-xs text-slate-500">
                @if ($stats['last_login_at'])
                    Dernière : {{ $stats['last_login_at']->format('d/m/Y H:i') }}
                    @if ($stats['last_login_ip']) · {{ $stats['last_login_ip'] }} @endif
                @else
                    Aucune connexion enregistrée
                @endif
            </p>
        </div>
        <div class="rounded-xl border bg-white p-5">
            <p class="text-sm text-slate-500">Échecs de connexion</p>
            <p class="mt-1 text-2xl font-semibold">{{ $stats['failed_logins'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-5">
            <p class="text-sm text-slate-500">Chapitres lus / validés</p>
            <p class="mt-1 text-2xl font-semibold">{{ $stats['chapters_read'] }} / {{ $stats['chapters_completed'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-5">
            <p class="text-sm text-slate-500">Interrogations</p>
            <p class="mt-1 text-2xl font-semibold">{{ $stats['quiz_passed'] }} / {{ $stats['quiz_attempts'] }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $stats['average_score'] !== null ? 'Moyenne '.$stats['average_score'].' %' : 'Aucune soumission' }}
            </p>
        </div>
    </div>

    <h2 class="mb-4 font-medium">Progression des cours</h2>
    @forelse ($courses as $course)
        <div class="mb-3 rounded-xl border bg-white p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-medium">{{ $course->title }}</p>
                    <p class="text-sm text-slate-500">{{ $course->progress_done }}/{{ $course->progress_total }} chapitres avancés</p>
                </div>
                <span class="text-sm text-indigo-700">{{ $course->progress_percent }} %</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $course->progress_percent }}%"></div>
            </div>
        </div>
    @empty
        <p class="mb-8 rounded-xl border border-dashed bg-white p-6 text-slate-500">Aucun cours dans le périmètre de cet utilisateur.</p>
    @endforelse

    <h2 class="mb-4 mt-10 font-medium">Interrogations soumises</h2>
    <div class="mb-8 space-y-3 md:hidden">
        @forelse ($attempts as $attempt)
            <article class="rounded-xl border bg-white p-4 text-sm">
                <p class="font-medium">{{ $attempt->quiz?->title ?? '—' }}</p>
                <p class="mt-1 text-slate-500">{{ $attempt->quiz?->chapter?->course?->title ?? '—' }} · {{ $attempt->quiz?->chapter?->title ?? '—' }}</p>
                <p class="mt-2">{{ $attempt->score }}/{{ $attempt->max_score }} ({{ $attempt->percentage }} %) ·
                    <span class="{{ $attempt->passed ? 'text-emerald-700' : 'text-amber-700' }}">{{ $attempt->passed ? 'Réussie' : 'Échouée' }}</span>
                </p>
                <p class="mt-1 text-xs text-slate-500">{{ $attempt->submitted_at?->format('d/m/Y H:i') }}</p>
            </article>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-6 text-center text-slate-500">Aucune interrogation soumise.</p>
        @endforelse
    </div>
    <div class="mb-8 hidden overflow-x-auto rounded-xl border bg-white md:block">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Cours / chapitre</th>
                    <th class="px-4 py-3">Interrogation</th>
                    <th class="px-4 py-3">Score</th>
                    <th class="px-4 py-3">Résultat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attempts as $attempt)
                    <tr class="border-t">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $attempt->submitted_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            {{ $attempt->quiz?->chapter?->course?->title ?? '—' }}
                            <span class="text-slate-500">· {{ $attempt->quiz?->chapter?->title ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $attempt->quiz?->title ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $attempt->score }}/{{ $attempt->max_score }} ({{ $attempt->percentage }} %)</td>
                        <td class="px-4 py-3">
                            <span class="{{ $attempt->passed ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $attempt->passed ? 'Réussie' : 'Échouée' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucune interrogation soumise.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mb-4 font-medium">Journal d’activité</h2>
    <div class="space-y-3 md:hidden">
        @forelse ($events as $event)
            <article class="rounded-xl border bg-white p-4 text-sm">
                <p class="font-medium">{{ $event->action->label() }}</p>
                <p class="mt-1 text-slate-700">{{ $event->description() }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $event->created_at->format('d/m/Y H:i') }} · {{ $event->ip_address ?? '—' }} · {{ $event->actor?->name ?? '—' }}</p>
            </article>
        @empty
            <p class="rounded-xl border border-dashed bg-white p-6 text-center text-slate-500">Aucun événement d’audit pour le moment.</p>
        @endforelse
    </div>
    <div class="hidden overflow-x-auto rounded-xl border bg-white md:block">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Détail</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Auteur</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr class="border-t">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $event->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $event->action->label() }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $event->description() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $event->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $event->actor?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Aucun événement d’audit pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
</x-layouts.admin>
