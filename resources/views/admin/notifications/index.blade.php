<x-layouts.admin title="Mouvements">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold sm:text-2xl">Notifications</h1>
            <p class="mt-1 text-sm text-slate-600">Uniquement lorsqu’un étudiant a lu un chapitre ou terminé une interrogation.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border px-4 py-2 text-sm sm:w-auto">
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <a href="{{ route('admin.notifications.show', $notification) }}"
               class="block rounded-2xl border bg-white p-4 shadow-sm hover:border-indigo-300 {{ $notification->read_at ? '' : 'border-indigo-200 bg-indigo-50/40' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium">{{ $notification->data['action_label'] ?? 'Activité' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-indigo-600" aria-label="Non lue"></span>
                    @endunless
                </div>
                <p class="mt-2 text-xs text-slate-400">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
            </a>
        @empty
            <p class="rounded-2xl border border-dashed bg-white p-8 text-center text-slate-500">
                Aucun chapitre lu ni interrogation terminée pour le moment.
            </p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</x-layouts.admin>
