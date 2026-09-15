<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#4f46e5">
<meta name="color-scheme" content="light">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="CapAcademia">
<meta name="format-detection" content="telephone=no">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    html { -webkit-text-size-adjust: 100%; }
    body { overflow-x: hidden; }
    input, select, textarea { font-size: 16px; }
    button, a, summary, [role="button"] { -webkit-tap-highlight-color: transparent; }
    body.app-shell {
        display: flex;
        flex-direction: column;
        height: 100vh;
        height: 100dvh;
        overflow: hidden;
    }
    .app-main,
    .admin-main {
        flex: 1 1 auto;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: contain;
        scroll-padding-bottom: 1.5rem;
    }
    .tabbar { flex-shrink: 0; }
    details summary { list-style: none; }
    details summary::-webkit-details-marker { display: none; }
</style>
