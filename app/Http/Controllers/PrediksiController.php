<?php

namespace App\Http\Controllers;

use App\Models\Prediksi;
use App\Services\MlClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PrediksiController extends Controller
{
    /**
     * Tampilkan form input prediksi.
     */
    public function form()
    {
        return view('prediksi.form');
    }

    /**
     * Proses prediksi via Flask lalu simpan 1 baris (input + output).
     * Dipakai oleh form (POST /prediksi) ATAU endpoint API (POST /api/prediksi).
     */
    public function predictAndSave(Request $r, MlClientService $ml)
    {
        // 1) Validasi input
        $data = $r->validate([
            'suhu_c' => ['required', 'numeric', 'between:-10,100'],
            'warna'  => ['required', Rule::in(['tidak berwarna', 'berwarna'])],
            'bau'    => ['required', Rule::in(['tidak berbau', 'berbau'])],
            'rasa'   => ['required', Rule::in(['tawar', 'tidak berasa', 'asam', 'manis'])],
        ]);

        // 2) Panggil Flask (proses dulu)
        try {
            $res = $ml->predict([
                'suhu_c' => round($data['suhu_c'], 1),
                'warna'  => $data['warna'],
                'bau'    => $data['bau'],
                'rasa'   => $data['rasa'],
            ]);
            // ekspektasi response Flask:
            // ['label' => 'ya'|'tidak', 'prob_ya' => 0.xx, 'is_permenkes_proxy' => true|false, 'model_version' => 'svm_rbf_v1']
        } catch (\Throwable $e) {
            // kalau Flask down / timeout
            if ($r->expectsJson()) {
                return response()->json(['error' => 'ML service tidak tersedia: ' . $e->getMessage()], 502);
            }
            return back()->withErrors(['ml' => 'ML service tidak tersedia.'])->withInput();
        }

        // 3) Simpan sekali (input + hasil)
        $row = DB::transaction(function () use ($r, $data, $res) {
            return Prediksi::create([
                'user_id'                => optional($r->user())->id,
                'suhu_c'                 => round($data['suhu_c'], 1),
                'warna'                  => $data['warna'],
                'bau'                    => $data['bau'],
                'rasa'                   => $data['rasa'],
                'label_prediksi'         => $res['label'] ?? 'tidak',
                'prob_ya'                => (float)($res['prob_ya'] ?? 0),
                'sesuai_permenkes_proxy' => (bool)($res['is_permenkes_proxy'] ?? false),
                'versi_model'            => $res['model_version'] ?? 'svm_rbf_v1',
                'sumber'                 => 'manual',
                'diprediksi_pada'        => now(),
            ]);
        });

        // 4) Response: JSON untuk fetch/AJAX, atau redirect dengan flash untuk form biasa
        if ($r->expectsJson()) {
            return response()->json(['saved' => $row, 'ml' => $res], 201);
        }

        return redirect()
            ->route('prediksi.form')
            ->with('ok', 'Prediksi tersimpan: ' . $row->label_prediksi . ' (Prob(ya) ' . number_format($row->prob_ya, 3) . ')');
    }

    public function riwayat(Request $r)
    {
        // hanya kontrol jumlah per halaman
        $per = (int) $r->input('per', 10);
        if (!in_array($per, [10, 25, 50, 100], true)) $per = 10;

        $rows = \App\Models\Prediksi::orderByDesc('diprediksi_pada')
            ->paginate($per)
            ->appends(['per' => $per]); // biar pilihan 'per' nempel saat paging

        return view('prediksi.riwayat', compact('rows'));
    }



    public function destroy($id)
    {
        $row = \App\Models\Prediksi::findOrFail($id);
        $row->delete();

        return redirect()->route('prediksi.riwayat')->with('ok', "Baris #{$id} dihapus.");
    }

    public function bulkDestroy(\Illuminate\Http\Request $r)
    {
        $ids = $r->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return back()->withErrors(['bulk' => 'Tidak ada data yang dipilih.']);
        }
        \App\Models\Prediksi::whereIn('id', $ids)->delete();

        return redirect()->route('prediksi.riwayat')->with('ok', count($ids) . ' data dihapus.');
    }

    public function importForm()
    {
        return view('prediksi.import');
    }

    public function importProcess(Request $r, \App\Services\MlClientService $ml)
    {
        $r->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], 
        ]);
        /** @var UploadedFile $e */
        $file = $r->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        $rows = [];
        if ($ext === 'csv' || $ext === 'txt') {
            $fh = fopen($file->getRealPath(), 'r');
            if (!$fh) return back()->withErrors(['file' => 'Gagal membaca file CSV.']);
            $header = null;
            while (($cols = fgetcsv($fh)) !== false) {
                if ($header === null) {
                    $header = array_map(fn($h) => Str::of($h)->trim()->lower()->toString(), $cols);
                    continue;
                }
                $rows[] = array_combine($header, $cols);
            }
            fclose($fh);
        } else {
            try {
                $spread = IOFactory::load($file->getRealPath());
                $sheet  = $spread->getActiveSheet()->toArray(null, true, true, true);
                $header = null;
                foreach ($sheet as $line) {
                    $line = array_values($line);
                    if ($header === null) {
                        $header = array_map(fn($h) => Str::of($h)->trim()->lower()->toString(), $line);
                        continue;
                    }
                    $rows[] = array_combine($header, $line);
                }
            } catch (\Throwable $e) {
                return back()->withErrors(['file' => 'Gagal membaca XLSX: ' . $e->getMessage()]);
            }
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'Tidak ada baris yang terbaca. Pastikan ada header: suhu_c, warna, bau, rasa.']);
        }
        if (count($rows) > 200) {
            return back()->withErrors(['file' => 'Maksimal 200 baris per import.']);
        }

        $needs = ['suhu_c', 'warna', 'bau', 'rasa'];
        foreach ($needs as $col) {
            if (!array_key_exists($col, $rows[0])) {
                return back()->withErrors(['file' => "Kolom wajib '{$col}' tidak ditemukan pada header."]);
            }
        }

        $okSetWarna = ['tidak berwarna', 'berwarna'];
        $okSetBau   = ['tidak berbau', 'berbau'];
        $okSetRasa  = ['tawar', 'tidak berasa', 'asam', 'manis'];

        $clean = [];
        $errors = [];
        foreach ($rows as $i => $row) {
            $line = $i + 2; 
            $suhu  = (float) Str::of($row['suhu_c'] ?? '')->replace(',', '.')->toString();
            $warna = Str::of($row['warna'] ?? '')->lower()->trim()->toString();
            $bau   = Str::of($row['bau']   ?? '')->lower()->trim()->toString();
            $rasa  = Str::of($row['rasa']  ?? '')->lower()->trim()->toString();

            if (!in_array($warna, $okSetWarna, true)) $errors[] = "Baris {$line}: warna tidak valid.";
            if (!in_array($bau,   $okSetBau,   true)) $errors[] = "Baris {$line}: bau tidak valid.";
            if (!in_array($rasa,  $okSetRasa,  true)) $errors[] = "Baris {$line}: rasa tidak valid.";
            if (!is_numeric($suhu))               $errors[] = "Baris {$line}: suhu_c harus numerik.";

            $clean[] = [
                'suhu_c' => round($suhu, 1),
                'warna'  => $warna,
                'bau'    => $bau,
                'rasa'   => $rasa,
            ];
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        if ($r->boolean('dedup', true)) {
            $counter = [];
            $filtered = [];
            foreach ($clean as $row) {
                $key = "{$row['suhu_c']}|{$row['warna']}|{$row['bau']}|{$row['rasa']}";
                $counter[$key] = ($counter[$key] ?? 0) + 1;
                if ($counter[$key] <= 2) $filtered[] = $row;
            }
            $clean = $filtered;
        }

        if ($r->boolean('validate_only')) {
            return back()->with('ok', 'Validasi OK. Baris siap diimport: ' . count($clean));
        }

        $saved = 0;
        $failed = 0;
        foreach ($clean as $row) {
            try {
                $res = $ml->predict($row); // call Flask
                \App\Models\Prediksi::create([
                    'user_id'                => optional($r->user())->id,
                    'suhu_c'                 => $row['suhu_c'],
                    'warna'                  => $row['warna'],
                    'bau'                    => $row['bau'],
                    'rasa'                   => $row['rasa'],
                    'label_prediksi'         => $res['label'] ?? 'tidak',
                    'prob_ya'                => (float)($res['prob_ya'] ?? 0),
                    'sesuai_permenkes_proxy' => (bool)($res['is_permenkes_proxy'] ?? false),
                    'versi_model'            => $res['model_version'] ?? 'svm_rbf_v1',
                    'sumber'                 => 'import',          
                    'diprediksi_pada'        => now(),
                ]);
                $saved++;
            } catch (\Throwable $e) {
                $failed++;
          
            }
        }

        return redirect()
            ->route('prediksi.riwayat')
            ->with('ok', "Import selesai. Sukses: {$saved}, Gagal: {$failed}");
    }
    public function exportCsv(Request $r)
    {
        $q = Prediksi::query()->orderByDesc('diprediksi_pada');
        $rows = $q->get();

        $fn = 'prediksi_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fn\""
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'waktu', 'suhu_c', 'warna', 'bau', 'rasa', 'label', 'prob_ya', 'proxy', 'model', 'sumber', 'user']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    optional($r->diprediksi_pada)->format('Y-m-d H:i:s'),
                    $r->suhu_c,
                    $r->warna,
                    $r->bau,
                    $r->rasa,
                    $r->label_prediksi,
                    $r->prob_ya,
                    $r->sesuai_permenkes_proxy ? 1 : 0,
                    $r->versi_model,
                    $r->sumber,
                    optional($r->user)->nama
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }
}
