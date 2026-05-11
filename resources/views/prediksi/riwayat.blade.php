@extends('layouts.app')
@section('title','Riwayat Prediksi')
@section('breadcrumbs','Beranda / Riwayat Prediksi')

@section('content')
<div class="mx-auto w-full max-w-6xl">
  <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl backdrop-blur">

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-xl font-semibold tracking-tight">Riwayat Prediksi</h3>
        <p class="mt-1 text-sm text-slate-400">
          Total <span class="font-semibold text-slate-200">{{ $rows->total() }}</span> entri tersimpan.
        </p>
      </div>

      <div class="flex items-center gap-3">
        {{-- Per-page dropdown sederhana --}}
        <form method="GET" class="flex items-center gap-2">
          <span class="text-sm text-slate-400">Tampilkan</span>
          <select name="per"
            class="rounded-lg border border-white/10 bg-slate-800/70 px-2 py-1 text-sm text-slate-100"
            onchange="this.form.submit()">
            @foreach([10,25,50,100] as $n)
            <option value="{{ $n }}" {{ (int)request('per',10)===$n?'selected':'' }}>{{ $n }}</option>
            @endforeach
          </select>
          <span class="text-sm text-slate-400">/ halaman</span>
        </form>

        <a href="{{ route('prediksi.form') }}"
          class="inline-flex items-center gap-2 rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-3 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/25">
          + Prediksi Baru
        </a>
      </div>
    </div>


    {{-- ===== Bulk form (DELETE) membungkus tabel & tombol Hapus Terpilih ===== --}}
    <form id="bulk-form" action="{{ route('prediksi.bulkDestroy') }}" method="POST" class="contents">
      @csrf
      @method('DELETE')

      <div class="mb-5">
        <button type="submit"
          onclick="return confirm('Hapus semua yang dipilih?')"
          class="inline-flex items-center gap-2 rounded-xl border border-rose-400/40 bg-rose-500/15 px-3 py-2 text-sm font-medium text-rose-200 hover:bg-rose-500/25">
          Hapus Terpilih
        </button>
      </div>

      @if($rows->count())
      <div class="-mx-6 overflow-hidden rounded-xl border border-white/10">
        <div class="max-h-[80vh] overflow-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-900/70">
              <tr class="sticky top-0 z-10 text-left text-slate-300 bg-slate-900/80 backdrop-blur">
                <th class="px-4 py-3">
                  <input id="check-all" type="checkbox" class="h-4 w-4 rounded border-slate-500 bg-slate-800">
                </th>
                <th class="sticky left-0 z-10 bg-slate-900/70 px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Input</th>
                <th class="px-4 py-3">Label</th>
                <th class="px-4 py-3">Prob(ya)</th>
                <th class="px-4 py-3">Proxy Permenkes</th>
                <th class="px-4 py-3">Model</th>
                <th class="px-4 py-3">Oleh</th>
                <th class="px-4 py-3">Sumber</th>
                <th class="px-4 py-3">Aksi</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-white/10">
              @foreach($rows as $r)
              @php
              $prob = (float) $r->prob_ya;
              $pct = max(0, min(100, $prob * 100));
              @endphp
              <tr class="hover:bg-white/5 transition">
                <td class="px-4 py-3">
                  <input type="checkbox" name="ids[]" value="{{ $r->id }}" class="row-check h-4 w-4 rounded border-slate-500 bg-slate-800">
                </td>

                <td class="sticky left-0 z-10 bg-slate-900/40 px-4 py-3 text-slate-200 whitespace-nowrap">
                  {{ $r->diprediksi_pada?->format('d M Y H:i') ?? '-' }}
                </td>

                {{-- Input --}}
                <td class="px-4 py-3">
                  <div class="text-slate-200">
                    <span class="text-xs text-slate-400 mr-1">Suhu</span>{{ number_format($r->suhu_c,1) }}°C
                  </div>
                  <div class="mt-0.5 text-slate-400">
                    <span class="mr-2">Warna: <span class="text-slate-200">{{ $r->warna }}</span></span>
                    <span class="mr-2">Bau: <span class="text-slate-200">{{ $r->bau }}</span></span>
                    <span>Rasa: <span class="text-slate-200">{{ $r->rasa }}</span></span>
                  </div>
                </td>

                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium
                    {{ $r->label_prediksi==='ya'
                        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30'
                        : 'bg-rose-500/15 text-rose-300 border border-rose-400/30' }}">
                    {{ strtoupper($r->label_prediksi) }}
                  </span>
                </td>

                @php
                $prob = (float) $r->prob_ya;
                $pct = max(0, min(100, $prob * 100)); // 0–100
                @endphp

                <td class="px-4 py-3">
                  <div class="font-medium text-slate-200">{{ number_format($pct, 1) }}%</div>
                  <div class="mt-0.5 h-1.5 w-28 rounded-full bg-slate-700">
                    <div class="h-1.5 rounded-full bg-cyan-400/70 w-[var(--p)]" style="--p: {{ $pct }}%"></div>
                  </div>
                </td>

                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium
                    {{ $r->sesuai_permenkes_proxy
                        ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-400/30'
                        : 'bg-rose-500/15 text-rose-300 border border-rose-400/30' }}">
                    {{ $r->sesuai_permenkes_proxy ? 'Sesuai' : 'Tidak' }}
                  </span>
                </td>

                <td class="px-4 py-3 text-slate-300">{{ $r->versi_model }}</td>
                <td class="px-4 py-3 text-slate-300">{{ $r->user->nama ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span class="rounded px-2 py-1 text-xs {{ $r->sumber==='import' ? 'bg-amber-500/20 text-amber-200' : 'bg-slate-600/30 text-slate-200' }}">
                    {{ strtoupper($r->sumber) }}
                  </span>
                </td>

                {{-- Hapus per baris: pakai form terpisah via "form" & "formaction" agar tidak bentrok dengan bulk-form --}}
                <td class="px-4 py-3 text-right">
                  <button
                    type="submit"
                    form="single-delete-form"
                    formaction="{{ route('prediksi.destroy', $r->id) }}"
                    class="rounded-lg border border-rose-400/40 bg-rose-500/15 px-2.5 py-1.5 text-xs text-rose-200 hover:bg-rose-500/25"
                    onclick="return confirm('Hapus baris #{{ $r->id }}?')">
                    Hapus
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-5">
        {{ $rows->links() }}
      </div>
      @else
      {{-- empty state … (boleh tambahkan state kosongmu di sini) --}}
      @endif
    </form>

    {{-- Form DELETE tunggal untuk tombol "Hapus" per baris (tidak nested) --}}
    <form id="single-delete-form" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>

    @push('scripts')
    <script>
      // Check all untuk bulk
      const all = document.getElementById('check-all');
      if (all) {
        all.addEventListener('change', () => {
          document.querySelectorAll('.row-check').forEach(cb => cb.checked = all.checked);
        });
      }
    </script>
    @endpush

  </div>
</div>
@endsection