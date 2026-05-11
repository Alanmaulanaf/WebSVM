<!doctype html>
<html lang="id" class="dark">

<head>
  <meta charset="utf-8">
  <title>@yield('title','Dashboard') • SVM AMDK</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>
    window.routes = {
      prediksiriwayat: "{{ route('prediksi.riwayat') }}"
    };
  </script>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-950 to-slate-900 text-slate-100">

  <div x-data="{
      open: false,
      close() { this.open = false },
      toggle() { this.open = !this.open }
    }"
    @keydown.window.escape="close()"
    class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">

    {{-- OVERLAY (mobile) --}}
    <div x-show="open"
      x-transition.opacity
      @click="close()"
      class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside
      :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
      class="fixed inset-y-0 left-0 z-40 w-64
         overflow-y-auto
         transform border-r border-white/10 bg-slate-950/90 p-4 backdrop-blur transition lg:static">
      <div class="mb-6 flex items-center gap-3">
        <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-300 text-slate-900 font-black">S</div>
        <div class="text-lg font-bold tracking-wide">SVM AMDK</div>
      </div>

      <nav class="space-y-1">
        <div class="mb-2 px-2 text-xs uppercase tracking-wide text-slate-400/70">Menu</div>

        {{-- saat klik menu di mobile, sidebar menutup --}}
        <a href="{{ route('dashboard') }}"
          @click="close()"
          class="block rounded-xl px-3 py-2 {{ request()->routeIs('dashboard')?'bg-cyan-500/15 text-cyan-200':'hover:bg-white/5' }}">
          🏠 Dashboard
        </a>

        <a href="{{ route('prediksi.form') }}"
          @click="close()"
          class="block rounded-xl px-3 py-2 {{ request()->routeIs('prediksi.form')?'bg-cyan-500/15 text-cyan-200':'hover:bg-white/5' }}">
          🧪 Form Prediksi
        </a>

        <a href="{{ route('prediksi.riwayat') }}"
          @click="close()"
          class="block rounded-xl px-3 py-2 {{ request()->routeIs('prediksi.riwayat')?'bg-cyan-500/15 text-cyan-200':'hover:bg-white/5' }}">
          📄 Riwayat Prediksi
        </a>

        <div class="mt-5 mb-2 px-2 text-xs uppercase tracking-wide text-slate-400/70">Akun</div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button @click="close()" class="w-full rounded-xl px-3 py-2 text-left hover:bg-white/5">🚪 Keluar</button>
        </form>
      </nav>
    </aside>

    {{-- MAIN --}}
    <main class="min-h-screen">
      <header class="sticky top-0 z-30 border-b border-white/10 bg-slate-900/70 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3 lg:px-6">
          <div class="flex items-center gap-2">
            <button @click="toggle()" class="lg:hidden rounded-lg border border-white/10 bg-slate-800/70 px-3 py-1.5" aria-label="Toggle menu">☰</button>
            <div class="text-sm text-slate-400">@yield('breadcrumbs','Beranda / Dashboard')</div>
          </div>
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full border border-white/20 bg-slate-800/70 px-3 py-1 text-xs">
              {{ auth()->user()->nama ?? 'Admin' }}
            </span>
          </div>
        </div>
      </header>
      @if (session('ok'))
      <div class="mx-auto mb-4 w-full max-w-4xl rounded-xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-3 text-emerald-200">
        {{ session('ok') }}
      </div>
      @endif
      @if ($errors->any())
      <div class="mx-auto mb-4 w-full max-w-4xl rounded-xl border border-rose-400/40 bg-rose-500/15 px-4 py-3 text-rose-200">
        @foreach ($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
      </div>
      @endif
      <section class="p-5">
        <div class="mx-auto w-full max-w-7xl px-4 lg:px-6">
          @yield('content')
        </div>
      </section>
      <div id="toast-root" class="pointer-events-none fixed bottom-4 right-4 z-[9999] space-y-2"></div>
      @stack('scripts')
    </main>
  </div>

  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>


</html>