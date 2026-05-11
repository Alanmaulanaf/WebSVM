@extends('layouts.base')
@section('title','Masuk • SVM AMDK')

@section('body')
<div class="grid min-h-screen md:grid-cols-2">
  {{-- Kiri (brand) --}}
  <section class="relative hidden md:block">
    <div class="absolute inset-0 bg-gradient-to-br from-cyan-600 via-sky-700 to-indigo-800"></div>
    <div class="absolute inset-0 opacity-20 [background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:18px_18px]"></div>
    <div class="relative z-10 flex h-full flex-col justify-between p-8 text-slate-50">
      <div class="flex items-center gap-3">
        <div>
          <div class="text-xl font-bold tracking-wide">SVM AMDK</div>
          <div class="text-xs/5 text-slate-100/80">Toyamilindo • Quality Prediction</div>
        </div>
      </div>
      <div class="max-w-lg">
        <h2 class="text-3xl font-semibold leading-tight">
          Prediksi kualitas air yang <span class="underline decoration-white/40">akurat</span> & <span class="underline decoration-white/40">konsisten</span>.
        </h2>
        <p class="mt-3 text-slate-100/90">Didukung SVM (RBF) dengan pipeline preprocessing.</p>
      </div>
      <div class="text-xs text-slate-100/70">© {{ date('Y') }} PT Toyamilindo</div>
    </div>
  </section>

  {{-- Kanan (form) --}}
  <section class="relative flex items-center justify-center p-6">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black"></div>
    <div class="relative w-full max-w-md">
      <div class="mb-6 flex items-center gap-3 md:hidden">
        <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-300 text-slate-900 font-black">S</div>
        <div class="text-lg font-semibold text-slate-100">SVM AMDK</div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.6)] backdrop-blur">
        <h1 class="mb-1 text-xl font-semibold text-slate-100">Masuk</h1>
        <p class="mb-5 text-sm text-slate-400">Gunakan email dan kata sandi akun Anda.</p>

        @if(session('ok'))
          <div class="mb-3 rounded-lg border border-emerald-400/40 bg-emerald-500/10 px-3 py-2 text-emerald-200">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
          <div class="mb-3 rounded-lg border border-rose-400/40 bg-rose-500/10 px-3 py-2 text-rose-200">
            @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('login.do') }}" class="space-y-4">
          @csrf
          <div>
            <label class="mb-1 block text-sm text-slate-300">Email</label>
            <input class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 text-slate-100 placeholder-slate-400 outline-none focus:ring-2 focus:ring-cyan-400"
                   type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
          </div>

          <div x-data="{show:false}">
            <label class="mb-1 block text-sm text-slate-300">Kata Sandi</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'"
                     class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-3 py-2 pr-20 text-slate-100 outline-none focus:ring-2 focus:ring-cyan-400"
                     name="password" required autocomplete="current-password">
              <button type="button" @click="show=!show"
                      class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-2 py-1 text-xs text-slate-300 hover:bg-white/5">
                <span x-show="!show">Tampilkan</span>
                <span x-show="show">Sembunyikan</span>
              </button>
            </div>
          </div>

          <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="ingat" value="1" class="h-4 w-4 rounded border-slate-600 bg-slate-800">
            Ingat saya
          </label>

          <button class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-400 to-sky-500 px-4 py-2 text-sm font-semibold text-slate-900 hover:from-cyan-300 hover:to-sky-400 active:opacity-90 transition">
            Masuk
          </button>
        </form>

        
      </div>
    </div>
  </section>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
