@extends('layouts.app')
@section('title','Form Prediksi')
@section('breadcrumbs','Beranda / Form Prediksi')

@section('content')
<div class="mx-auto w-full max-w-4xl">
  <!-- CARD -->
  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl backdrop-blur">
    <div class="mb-6 flex items-start justify-between gap-4">
      <div class="mb-3 flex items-center justify-between">
  <h3 class="text-lg font-semibold">Input Parameter Fisik</h3>
  <a href="{{ route('prediksi.import.form') }}"
     class="rounded-lg border border-amber-400/40 bg-amber-500/15 px-3 py-1.5 text-sm text-amber-100 hover:bg-amber-500/25">
    ⬆ Import CSV/Excel
  </a>
</div>

      <a href="{{ route('prediksi.riwayat') }}"
         class="inline-flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-200 hover:bg-cyan-500/20">
        Riwayat
      </a>
    </div>

    {{-- FORM (progressive enhancement: tetap bisa submit tanpa JS) --}}
    <form id="form-prediksi"
          action="{{ route('prediksi.store') }}"
          method="POST"
          data-endpoint="{{ route('prediksi.store') }}"
          class="grid gap-5 md:grid-cols-2">
      @csrf

      {{-- Suhu --}}
      <div>
        <label class="mb-1.5 block text-sm text-slate-300">Suhu (°C)</label>
        <input type="number" step="0.1" name="suhu_c" value="25.0" required
               class="w-full rounded-xl border border-white/10 bg-slate-800/70 px-3 py-2 text-slate-100 outline-none ring-0 transition focus:border-cyan-400/40 focus:bg-slate-800">
        <p class="mt-1 text-xs text-slate-400">Gunakan titik desimal (mis. 25.0).</p>
      </div>

      {{-- Warna --}}
      <div>
        <label class="mb-1.5 block text-sm text-slate-300">Warna</label>
        <select name="warna"
                class="w-full rounded-xl border border-white/10 bg-slate-800/70 px-3 py-2 text-slate-100 outline-none focus:border-cyan-400/40">
          <option>tidak berwarna</option>
          <option>berwarna</option>
        </select>
      </div>

      {{-- Bau --}}
      <div>
        <label class="mb-1.5 block text-sm text-slate-300">Bau</label>
        <select name="bau"
                class="w-full rounded-xl border border-white/10 bg-slate-800/70 px-3 py-2 text-slate-100 outline-none focus:border-cyan-400/40">
          <option>tidak berbau</option>
          <option>berbau</option>
        </select>
      </div>

      {{-- Rasa --}}
      <div>
        <label class="mb-1.5 block text-sm text-slate-300">Rasa</label>
        <select name="rasa"
                class="w-full rounded-xl border border-white/10 bg-slate-800/70 px-3 py-2 text-slate-100 outline-none focus:border-cyan-400/40">
          <option>tawar</option>
          <option>tidak berasa</option>
          <option>asam</option>
          <option>manis</option>
        </select>
      </div>

      {{-- CTA --}}
      <div class="md:col-span-2 flex items-center justify-between gap-3">
        <p class="text-xs text-slate-400">
          Tip: kombinasi “tidak berwarna • tidak berbau • tawar/ tidak berasa” cenderung memenuhi Permenkes.
        </p>
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/25">
          Prediksi &amp; Simpan
        </button>
      </div>
    </form>

    {{-- HASIL --}}
    <div id="result-card" class="mt-6 hidden"></div>
  </div>
</div>
@endsection
