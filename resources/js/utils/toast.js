export function toast(message, type = "success", ms = 3500) {
  const root = document.getElementById("toast-root");
  if (!root) return alert(message);

  const base = document.createElement("div");
  base.className =
    "pointer-events-auto rounded-xl border px-4 py-3 shadow-lg ring-1 ring-black/5 max-w-sm text-sm " +
    (type === "success"
      ? "border-emerald-400/40 bg-emerald-500/15 text-emerald-100"
      : type === "error"
      ? "border-rose-400/40 bg-rose-500/15 text-rose-100"
      : "border-slate-400/40 bg-slate-700/60 text-slate-100");

  base.innerHTML = `
    <div class="flex items-start gap-3">
      <div class="text-lg">${type === "success" ? "✅" : type === "error" ? "⚠️" : "ℹ️"}</div>
      <div class="grow leading-5">${message}</div>
      <button class="ml-2 rounded-lg px-2 text-slate-300/80 hover:text-white">✕</button>
    </div>
  `;

  const closeBtn = base.querySelector("button");
  const remove = () => {
    base.style.opacity = "0";
    base.style.transform = "translateY(6px)";
    setTimeout(() => base.remove(), 200);
  };
  closeBtn.addEventListener("click", remove);

  base.style.opacity = "0";
  base.style.transform = "translateY(6px)";
  base.style.transition = "opacity .2s, transform .2s";

  root.appendChild(base);
  requestAnimationFrame(() => {
    base.style.opacity = "1";
    base.style.transform = "translateY(0)";
  });

  if (ms > 0) setTimeout(remove, ms);
}
