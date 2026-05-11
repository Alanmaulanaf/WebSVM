// resources/js/prediksi-form.js
import { csrfToken } from "./utils/csrf";
import { toast } from './utils/toast';
// --- helpers ---------------------------------------------------------------
const nf = new Intl.NumberFormat("id-ID", { minimumFractionDigits: 1, maximumFractionDigits: 1 });
const tf = new Intl.NumberFormat("id-ID", { minimumFractionDigits: 0, maximumFractionDigits: 0 });

function clamp01(x) { return Math.max(0, Math.min(1, Number(x) || 0)); }
function kebab(s) { return String(s || "").toLowerCase(); }
function yesNo(b) { return b ? "Ya" : "Tidak"; }

// --- renderer --------------------------------------------------------------
function renderResult(container, payload) {
  const row = payload?.saved ?? null;     // baris tersimpan di DB (jika sukses)
  const ml = payload?.ml ?? null;        // respons mentah ML (fallback info)

  if (!row && !ml) {
    container.classList.add("hidden");
    container.innerHTML = "";
    return;
  }

  // sumber data prioritas: yang tersimpan di DB
  const label = kebab(row?.label_prediksi || ml?.label || "tidak");
  const ok = label === "ya";
  const prob = clamp01(row?.prob_ya ?? ml?.prob_ya ?? 0);
  const pct = clamp01(prob) * 100;
  const proxy = !!(row?.sesuai_permenkes_proxy ?? ml?.is_permenkes_proxy);
  const ver = row?.versi_model ?? ml?.model_version ?? "svm_rbf_v1";
  const id = row?.id ?? "—";
  const waktu = row?.diprediksi_pada ?? ""; // biar aja string mentah; tabel riwayat yg format

  // input untuk rekap kecil
  const inSuhu = row?.suhu_c ?? ml?.input?.suhu_c ?? "-";
  const inWarna = row?.warna ?? ml?.input?.warna ?? "-";
  const inBau = row?.bau ?? ml?.input?.bau ?? "-";
  const inRasa = row?.rasa ?? ml?.input?.rasa ?? "-";

  container.innerHTML = `
    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 shadow-xl backdrop-blur" aria-live="polite">
      <header class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
          <h2 class="text-lg font-semibold">Ringkasan Hasil Prediksi</h2>
          ${waktu ? `<p class="mt-0.5 text-xs text-slate-400">Waktu simpan: <span class="text-slate-300">${waktu}</span></p>` : ""}
        </div>
        <span class="rounded-full border px-3 py-1 text-xs
               ${ok
      ? "border-emerald-400/40 bg-emerald-500/15 text-emerald-300"
      : "border-rose-400/40 bg-rose-500/15 text-rose-300"}">
          Klasifikasi: <strong>${ok ? "Sesuai" : "Tidak Sesuai"}</strong>
        </span>
      </header>

      <div class="grid gap-3 md:grid-cols-2">
        <div class="rounded-xl border border-white/10 bg-slate-800/50 p-3">
          <div class="mb-1 text-xs text-slate-400">Probabilitas Kelas “Ya”</div>
          <div class="text-2xl font-bold">${nf.format(pct)}%</div>
          <div class="mt-2 h-1.5 w-40 rounded-full bg-slate-700">
            <div class="h-1.5 rounded-full bg-cyan-400/70" style="width:${tf.format(pct)}%"></div>
          </div>
        </div>

        <div class="rounded-xl border border-white/10 bg-slate-800/50 p-3">
          <div class="mb-1 text-xs text-slate-400">Kesesuaian Proxy Permenkes</div>
          <div class="${proxy ? "text-emerald-300" : "text-rose-300"}">${yesNo(proxy)}</div>
          <p class="mt-1 text-[11px] leading-snug text-slate-400">
            Proxy: warna “tidak berwarna”, bau “tidak berbau”, rasa “tawar/ tidak berasa”.
          </p>
        </div>
      </div>

      <div class="mt-4 grid gap-2 md:grid-cols-4">
        <div class="rounded-lg border border-white/10 bg-slate-800/40 p-2">
          <div class="text-xs text-slate-400">Suhu (°C)</div>
          <div class="font-medium">${inSuhu}</div>
        </div>
        <div class="rounded-lg border border-white/10 bg-slate-800/40 p-2">
          <div class="text-xs text-slate-400">Warna</div>
          <div class="font-medium">${inWarna}</div>
        </div>
        <div class="rounded-lg border border-white/10 bg-slate-800/40 p-2">
          <div class="text-xs text-slate-400">Bau</div>
          <div class="font-medium">${inBau}</div>
        </div>
        <div class="rounded-lg border border-white/10 bg-slate-800/40 p-2">
          <div class="text-xs text-slate-400">Rasa</div>
          <div class="font-medium">${inRasa}</div>
        </div>
      </div>

      <footer class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
        <div>ID Prediksi: <span class="text-slate-200">${id}</span></div>
        <div>Versi Model: <span class="text-slate-200">${ver}</span></div>
        <a href="${window.routes?.prediksiList ?? "/prediksi/riwayat"}"
           class="ml-auto inline-flex items-center rounded-lg border border-cyan-400/40 bg-cyan-500/15 px-3 py-2 text-cyan-200 hover:bg-cyan-500/25">
          Lihat Riwayat
        </a>
      </footer>
    </section>
  `;
  container.classList.remove("hidden");
}

// --- submit handler --------------------------------------------------------

async function handleSubmit(e) {
  e.preventDefault();

  const form = e.currentTarget;
  const endpoint = form.dataset.endpoint || form.action;
  const resultBox = document.getElementById('result-card');
  const out = document.getElementById('out'); // opsional debug
  const submitBtn = form.querySelector('button[type="submit"]');

  // helper aman (no-op kalau tidak ada)
  const safeSetLoading = (btn, on) => {
    try { if (typeof setLoading === 'function' && btn) setLoading(btn, on); } catch { }
  };
  const safeToast = (msg, type = 'info') => {
    try { if (typeof toast === 'function') toast(msg, type); } catch { }
  };

  const payload = Object.fromEntries(new FormData(form).entries());

  safeSetLoading(submitBtn, true);
  let res, text, data = null;

  try {
    res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
      },
      credentials: 'same-origin', // penting: kirim cookie sesi
      body: JSON.stringify(payload),
    });

    text = await res.text();
    try { data = JSON.parse(text); } catch { data = null; }

    if (!res.ok || !data || !data.saved) {
      const serverMsg = data?.error || data?.message || text || 'Gagal menyimpan.';
      safeToast(serverMsg, 'error');
      if (resultBox) { resultBox.classList.add('hidden'); resultBox.innerHTML = ''; }
      if (out) out.textContent = JSON.stringify({ status: res.status, raw: text }, null, 2);
      return;
    }

    // sukses
    safeToast(`Berhasil disimpan (#${data.saved.id}) — Label: ${String(data.saved.label_prediksi).toUpperCase()}`, 'success');
    if (typeof renderResult === 'function') renderResult(resultBox, data);
    if (out) out.textContent = JSON.stringify(data, null, 2);

    form.reset();
    form.querySelector('[name="suhu_c"]')?.focus();

  } catch (err) {
    safeToast('Jaringan/Server error: ' + err, 'error');
    if (resultBox) { resultBox.classList.add('hidden'); resultBox.innerHTML = ''; }
    if (out) out.textContent = JSON.stringify({ error: String(err) }, null, 2);
  } finally {
    safeSetLoading(submitBtn, false);
  }
}

// --- mount -----------------------------------------------------------------
export function mountPrediksiForm() {
  const form = document.getElementById("form-prediksi");
  if (!form) return;
  form.addEventListener("submit", handleSubmit);
}

document.addEventListener("DOMContentLoaded", mountPrediksiForm);
