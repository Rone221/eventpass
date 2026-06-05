<x-site-layout title="Scanner de billets">
    <div class="mb-6">
        <h1 class="font-display font-extrabold text-3xl text-ink-900 flex items-center gap-2"><x-icon name="qr-code" class="w-7 h-7 text-brand-500"/> Scanner de billets</h1>
        <p class="text-ink-400 mt-1 text-sm">
            Cette page utilise la même API sécurisée (Sanctum) que l'application mobile :
            <code class="bg-ink-100 text-ink-700 px-1.5 py-0.5 rounded text-xs">POST /api/v1/tickets/validate</code>
        </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="card-pad">
            <div id="reader" class="rounded-xl overflow-hidden bg-ink-50 mb-4"></div>
            <button id="btn-camera" class="btn-outline btn-sm w-full"><x-icon name="camera" class="w-4 h-4"/> Activer la caméra</button>

            <div class="mt-5 pt-5 border-t border-ink-100">
                <label class="label">Saisie manuelle du code</label>
                <div class="flex gap-2">
                    <input id="manual-input" type="text" placeholder="EVP.12.xxxxx.signature" class="flex-1">
                    <button id="btn-manual" class="btn-brand btn-sm">Valider</button>
                </div>
                <p class="text-xs text-ink-400 mt-2 flex items-center gap-1.5"><x-icon name="bulb" class="w-4 h-4"/> Copiez la charge utile du QR depuis le PDF du billet pour tester sans caméra.</p>
            </div>
        </div>

        <div>
            <div id="result" class="card border-dashed p-10 text-center text-ink-300">
                <x-icon name="qr-code" class="w-10 h-10 mx-auto mb-2 text-ink-200"/>
                En attente d'un scan…
            </div>
            <div id="history" class="mt-4 space-y-2"></div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const API_TOKEN = @json($apiToken);
        const VALIDATE_URL = @json(route('api.tickets.validate'));
        const resultBox = document.getElementById('result');
        const historyBox = document.getElementById('history');
        let busy = false;

        async function validateToken(token) {
            if (!token || busy) return;
            busy = true;
            resultBox.className = 'card p-10 text-center text-ink-500';
            resultBox.textContent = 'Vérification…';
            try {
                const res = await fetch(VALIDATE_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': 'Bearer ' + API_TOKEN },
                    body: JSON.stringify({ token }),
                });
                render(res.status, await res.json());
            } catch (e) {
                resultBox.className = 'card p-10 text-center text-brand-700 bg-brand-50 border-brand-200';
                resultBox.textContent = 'Erreur réseau.';
            } finally { setTimeout(() => busy = false, 800); }
        }

        function svgIcon(kind, size) {
            const s = size || 24;
            const wrap = (inner) => `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" style="display:inline-block;vertical-align:middle">${inner}</svg>`;
            if (kind === 'ok')   return wrap('<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>');
            if (kind === 'warn') return wrap('<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>');
            return wrap('<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>');
        }

        function render(status, data) {
            const ok = data.status === 'valid';
            const warn = data.status === 'already_scanned';
            const palette = ok ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                : warn ? 'bg-amber-50 border-amber-200 text-amber-800'
                       : 'bg-brand-50 border-brand-200 text-brand-800';
            const kind = ok ? 'ok' : warn ? 'warn' : 'err';
            let details = '';
            if (data.ticket) {
                details = `<div class="mt-3 text-sm text-left inline-block">
                    <div><b>Porteur :</b> ${data.ticket.holder_name}</div>
                    <div><b>Catégorie :</b> ${data.ticket.type}</div>
                    <div><b>Événement :</b> ${data.ticket.event}</div></div>`;
                if (data.scanned_at) details += `<div class="mt-1 text-xs opacity-80">Déjà scanné le ${new Date(data.scanned_at).toLocaleString('fr-FR')}</div>`;
            }
            resultBox.className = 'card p-10 text-center border ' + palette;
            resultBox.innerHTML = `<div class="flex justify-center">${svgIcon(kind, 48)}</div><div class="mt-2 font-display font-bold text-lg">${data.message ?? data.status}</div>${details}`;

            const row = document.createElement('div');
            row.className = 'text-sm px-4 py-2.5 rounded-xl border flex items-center gap-2 ' + palette;
            row.innerHTML = `${svgIcon(kind, 18)}<span>${data.message ?? data.status}` + (data.ticket ? ` — ${data.ticket.holder_name}` : '') + `</span>`;
            historyBox.prepend(row);
        }

        document.getElementById('btn-manual').addEventListener('click', () => validateToken(document.getElementById('manual-input').value.trim()));
        document.getElementById('manual-input').addEventListener('keydown', (e) => { if (e.key === 'Enter') document.getElementById('btn-manual').click(); });
        document.getElementById('btn-camera').addEventListener('click', function () {
            const reader = new Html5Qrcode('reader');
            reader.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, (d) => validateToken(d), () => {})
                .then(() => this.textContent = 'Caméra active — visez un QR code')
                .catch(err => this.textContent = 'Caméra indisponible : ' + err);
        });
    </script>
</x-site-layout>
