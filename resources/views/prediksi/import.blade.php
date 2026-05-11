@extends('layouts.app')
@section('title','Import Data')
@section('breadcrumbs','Beranda / Import Data')

@section('content')
<div class="mx-auto w-full max-w-3xl">
    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-6 shadow-xl backdrop-blur">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="text-xl font-semibold tracking-tight">Import CSV/Excel</h3>
                <p class="mt-1 text-sm text-slate-400">
                    Pastikan kolom: <code class="mx-0.5 rounded bg-slate-800/80 px-1 py-0.5">suhu_c</code>
                    <code class="mx-0.5 rounded bg-slate-800/80 px-1 py-0.5">warna</code>
                    <code class="mx-0.5 rounded bg-slate-800/80 px-1 py-0.5">bau</code>
                    <code class="mx-0.5 rounded bg-slate-800/80 px-1 py-0.5">rasa</code> (huruf kecil).
                </p>
            </div>

            <a href="{{ route('prediksi.riwayat') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-3 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/25">
                ← Kembali ke Riwayat
            </a>
        </div>

        {{-- Alert --}}
        @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-400/30 bg-rose-500/15 px-4 py-3 text-rose-200">
            @foreach ($errors->all() as $e)
            <div>• {{ $e }}</div>
            @endforeach
        </div>
        @endif
        @if (session('ok'))
        <div class="mb-4 rounded-xl border border-emerald-400/30 bg-emerald-500/15 px-4 py-3 text-emerald-200">
            {{ session('ok') }}
        </div>
        @endif

        {{-- Form --}}
        <form id="import-form" class="grid gap-5" action="{{ route('prediksi.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Dropzone --}}
            <div id="dropzone"
                class="group grid place-items-center rounded-2xl border border-dashed border-white/15 bg-slate-900/40 px-4 py-8 text-center transition
                  hover:border-cyan-400/40 focus-within:border-cyan-400/40 focus-within:ring-2 focus-within:ring-cyan-400/10">
                <input id="file" name="file" type="file" accept=".csv,.xlsx" class="sr-only" required>
                <label for="file" class="cursor-pointer">
                    <div class="text-4xl leading-none">📄</div>
                    <div class="mt-2 font-medium text-slate-200">Tarik & letakkan berkas ke sini</div>
                    <div class="text-xs text-slate-400">atau <span class="underline text-cyan-300">klik untuk memilih</span> (.csv / .xlsx)</div>
                </label>

                <div id="file-badge" aria-live="polite"
                    class="pointer-events-none mt-3 hidden rounded-full border border-white/10 bg-slate-800/70 px-3 py-1 text-xs text-slate-300">
                    <span class="font-medium">Berkas:</span> <span id="file-name">-</span>
                </div>

            </div>

            {{-- Opsi & Aksi --}}
            <div class="flex justify-center">
                <button id="submit-btn" class="inline-flex items-center justify-center gap-2 rounded-xl border border-cyan-400/40 bg-cyan-500/15 px-4 py-2 text-sm font-medium text-cyan-100 hover:bg-cyan-500/25 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                    Import
                </button>
            </div>

            {{-- Catatan --}}
            <div class="rounded-xl border border-white/10 bg-slate-800/40 px-4 py-3 text-xs leading-relaxed text-slate-300">
                <div class="font-medium text-slate-200">Catatan:</div>
                <ul class="mt-1 list-disc pl-5">
                    <li>Nilai valid: <b>warna</b> = <i>tidak berwarna</i>/<i>berwarna</i>, <b>bau</b> = <i>tidak berbau</i>/<i>berbau</i>, <b>rasa</b> = <i>tawar</i>/<i>tidak berasa</i>/<i>asam</i>/<i>manis</i>.</li>
                    <li>Setiap baris akan diprediksi oleh model (Flask) lalu disimpan; kolom <code>sumber</code> otomatis diisi <b>"import"</b>.</li>
                    <li>Gunakan titik untuk desimal suhu (mis. <code>25.0</code>).</li>
                </ul>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- Dropzone UX ---
    (function() {
        const dz = document.getElementById('dropzone');
        const input = document.getElementById('file');
        const badge = document.getElementById('file-badge');
        const nameEl = document.getElementById('file-name');
        const submit = document.getElementById('submit-btn');

        // drag styles
        ['dragenter', 'dragover'].forEach(ev => {
            dz.addEventListener(ev, e => {
                e.preventDefault();
                e.stopPropagation();
                dz.classList.add('ring-2', 'ring-cyan-400/30', 'border-cyan-400/40');
            });
        });
        ['dragleave', 'drop'].forEach(ev => {
            dz.addEventListener(ev, e => {
                e.preventDefault();
                e.stopPropagation();
                dz.classList.remove('ring-2', 'ring-cyan-400/30', 'border-cyan-400/40');
            });
        });

        // drop handler
        dz.addEventListener('drop', e => {
            const f = e.dataTransfer.files?.[0];
            if (!f) return;
            if (!/\.(csv|xlsx)$/i.test(f.name)) {
                alert('Format harus .csv atau .xlsx');
                return;
            }
            input.files = e.dataTransfer.files;
            nameEl.textContent = f.name;
            badge.classList.remove('hidden');
            submit.disabled = false;
        });

        // choose handler
        input.addEventListener('change', e => {
            const f = e.target.files?.[0];
            if (!f) {
                submit.disabled = true;
                badge.classList.add('hidden');
                return;
            }
            nameEl.textContent = f.name;
            badge.classList.remove('hidden');
            submit.disabled = false;
        });

        // download template (client-side)
        document.getElementById('download-template')?.addEventListener('click', () => {
            const csv = [
                'suhu_c,warna,bau,rasa',
                '25.0,tidak berwarna,tidak berbau,tawar',
                '30.5,berwarna,berbau,asam'
            ].join('\n');
            const blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'template_import_svm.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });
    })();
</script>
@endpush