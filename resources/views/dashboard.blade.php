@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumbs','Beranda / Dashboard')

@section('content')
@if($total === 0)
{{-- Empty state sederhana --}}
<div class="grid place-items-center rounded-2xl border border-dashed border-white/15 bg-slate-900/60 p-12 text-center">
  <div class="text-3xl">🧪</div>
  <h3 class="mt-2 text-lg font-semibold">Belum ada prediksi</h3>
  <p class="mt-1 text-sm text-slate-400">Mulai dengan membuat prediksi pertama Anda.</p>
  <div class="mt-4 flex gap-2">
    <a href="{{ route('prediksi.form') }}" class="btn">+ Prediksi Baru</a>
    <a href="{{ route('prediksi.import.form') }}" class="rounded-xl border border-amber-400/40 bg-amber-500/15 px-3 py-2 text-sm text-amber-100 hover:bg-amber-500/25">
      ⬆ Import CSV/Excel
    </a>
  </div>
</div>
@else
<div class="grid gap-4 md:grid-cols-4">
  {{-- KPI: Total --}}
  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <div class="text-xs text-slate-400">Total Prediksi</div>
    <div class="mt-1 text-2xl font-bold">{{ number_format($total) }}</div>
  </div>

  {{-- KPI: Hari ini --}}
  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <div class="text-xs text-slate-400">Hari Ini</div>
    <div class="mt-1 text-2xl font-bold">{{ number_format($today) }}</div>
  </div>

  {{-- KPI: % Label YA --}}
  @php
  $p = max(0, min(100, (float) $pYa));
  @endphp

  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <div class="text-xs text-slate-400">Distribusi Label: YA</div>
    <div class="mt-1 text-2xl font-bold">{{ number_format($p,1) }}%</div>

    <div class="mt-2 h-1.5 w-full rounded-full bg-slate-700" style="--w: {{ (int)round($p) }};">
      <div class="h-1.5 rounded-full bg-emerald-400/80" style="width: calc(var(--w) * 1%);"></div>
    </div>

    <div class="mt-1 text-xs text-slate-400">YA: {{ $ya }}, TIDAK: {{ $tidak }}</div>
  </div>


  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <div class="text-xs text-slate-400">Proxy Permenkes Sesuai</div>
    <div class="mt-1 text-2xl font-bold">{{ number_format($pProxy,1) }}%</div>
    <div class="mt-1 text-xs text-slate-400">({{ $proxyOk }} dari {{ $total }})</div>
  </div>
</div>

<div class="mt-6 grid gap-4 md:grid-cols-2">
  {{-- Mini donut (tanpa library) --}}
  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <div class="mb-2 flex items-center justify-between">
      <h3 class="text-lg font-semibold">Distribusi Label</h3>
      <div class="flex items-center gap-3 text-xs">
        <span class="inline-flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-emerald-400 inline-block"></i> YA</span>
        <span class="inline-flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-rose-400 inline-block"></i> TIDAK</span>
      </div>
    </div>
    @php
    $yaPct = min(100, max(0, $pYa)); // 0–100
    @endphp

    <div class="grid place-items-center py-2">
      <div
        class="relative h-28 w-28 rounded-full"
        style="--p: {{ $yaPct }}%; background: conic-gradient(#34d399 var(--p), #f43f5e 0);">
        <div class="absolute inset-2 grid place-items-center rounded-full bg-slate-900/90 text-sm font-semibold">
          {{ number_format($pYa,0) }}%
        </div>
      </div>
    </div>

    <div class="mt-2 text-center text-xs text-slate-400">YA: {{ $ya }} • TIDAK: {{ $tidak }}</div>
  </div>

  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
    <h3 class="mb-3 text-lg font-semibold">Aksi Cepat</h3>
    <div class="flex flex-wrap gap-2">
      <a class="btn" href="{{ route('prediksi.form') }}">+ Prediksi Baru</a>
      <a class="rounded-xl border border-amber-400/40 bg-amber-500/15 px-3 py-2 text-sm text-amber-100 hover:bg-amber-500/25"
        href="{{ route('prediksi.import.form') }}">⬆ Import CSV/Excel</a>
      <a class="rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-3 py-2 text-sm text-cyan-100 hover:bg-cyan-500/25"
        href="{{ route('prediksi.riwayat') }}">Riwayat</a>
    </div>
  </div>
</div>

<div class="mt-6 rounded-2xl border border-white/10 bg-slate-900/70 p-4">
  <div class="mb-3 flex items-center justify-between">
    <h3 class="text-lg font-semibold">5 Prediksi Terakhir</h3>
    <a href="{{ route('prediksi.riwayat') }}" class="text-sm text-cyan-300 hover:underline">Lihat semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="text-left text-slate-300">
        <tr>
          <th class="py-2 pr-3">Waktu</th>
          <th class="py-2 pr-3">Input</th>
          <th class="py-2 pr-3">Label</th>
          <th class="py-2 pr-3">Prob(ya)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-white/10">
        @foreach($latest as $r)
        @php $pct = max(0,min(100, (float)$r->prob_ya*100 )); @endphp
        <tr>
          <td class="py-2 pr-3 whitespace-nowrap text-slate-200">{{ $r->diprediksi_pada?->format('d M Y H:i') ?? '-' }}</td>
          <td class="py-2 pr-3">
            <div class="text-slate-200"><span class="text-xs text-slate-400 mr-1">Suhu</span>{{ number_format($r->suhu_c,1) }}°C</div>
            <div class="text-slate-400 text-xs">
              Warna: <span class="text-slate-200">{{ $r->warna }}</span>,
              Bau: <span class="text-slate-200">{{ $r->bau }}</span>,
              Rasa: <span class="text-slate-200">{{ $r->rasa }}</span>
            </div>
          </td>
          <td class="py-2 pr-3">
            <span class="inline-block rounded-lg border px-2 py-0.5 text-xs
                {{ $r->label_prediksi==='ya'
                    ? 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30'
                    : 'bg-rose-500/15 text-rose-300 border-rose-400/30' }}">
              {{ strtoupper($r->label_prediksi) }}
            </span>
          </td>
          @php
          $pct = max(0, min(100, (float) $pct));
          @endphp
          <td class="py-2 pr-3">
            <div class="font-medium text-slate-200">{{ number_format($pct,1) }}%</div>
            <div class="mt-0.5 h-1.5 w-28 rounded-full bg-slate-700">
              <div class="h-1.5 rounded-full bg-cyan-400/70" style="--w: {{ $pct }}%; width: var(--w);"></div>
            </div>
          </td>

        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection