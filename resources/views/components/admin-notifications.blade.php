@php
    $unread = auth()->user()->unreadNotifications()->count();
    $latest = auth()->user()->notifications()->latest()->limit(8)->get();
    $badge = $unread > 99 ? '99+' : (string) $unread;
@endphp

<div class="relative" data-notification-root>
    <details class="relative">
        <summary class="relative inline-flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                 aria-label="Notifications{{ $unread ? ' ('.$unread.' non lues)' : '' }}">
            <x-icon name="bell" class="h-5 w-5" />
            <span data-unread-badge
                  data-count-url="{{ route('admin.notifications.unread-count') }}"
                  class="{{ $unread ? '' : 'hidden ' }}absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-semibold leading-none text-white">
                {{ $badge }}
            </span>
        </summary>
        <div class="absolute right-0 z-50 mt-2 w-[min(20rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-3 py-2">
                <p class="text-sm font-medium">Notifications</p>
                <a href="{{ route('admin.notifications.index') }}" class="text-xs text-indigo-700">Tout voir</a>
            </div>
            <div class="max-h-80 overflow-y-auto">
                @forelse ($latest as $notification)
                    <a href="{{ route('admin.notifications.show', $notification) }}"
                       class="block border-b px-3 py-3 text-sm last:border-b-0 hover:bg-slate-50 {{ $notification->read_at ? 'text-slate-600' : 'bg-indigo-50/60 text-slate-900' }}">
                        <p class="font-medium leading-tight">{{ $notification->data['action_label'] ?? 'Activité' }}</p>
                        <p class="mt-0.5 line-clamp-2 text-xs text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-1 text-[11px] text-slate-400">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
                    </a>
                @empty
                    <p class="px-3 py-6 text-center text-sm text-slate-500">Aucun chapitre lu ni interrogation terminée.</p>
                @endforelse
            </div>
        </div>
    </details>
</div>
<script>
    (function () {
        const badge = document.querySelector('[data-unread-badge]');
        const url = badge && badge.getAttribute('data-count-url');
        if (!badge || !url) return;

        function render(count) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.toggle('hidden', count < 1);
        }

        setInterval(function () {
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (response) { return response.ok ? response.json() : null; })
                .then(function (data) {
                    if (data && typeof data.count === 'number') render(data.count);
                })
                .catch(function () {});
        }, 20000);
    })();
</script>
