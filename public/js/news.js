document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.news-page');
    if (!page) return;

    const newsList     = document.getElementById('news-list');
    const searchInput  = document.getElementById('searchInput');
    const lineFilter   = document.getElementById('lineFilter');
    const typeFilter   = document.getElementById('typeFilter');
    const sourceFilter = document.getElementById('sourceFilter');
    const createNewsBtn = document.getElementById('create-news-btn');

    const isAdmin         = page.dataset.isAdmin === 'true';
    const isAuthenticated = page.dataset.isAuthenticated === 'true';
    const loginUrl        = page.dataset.loginUrl || '/login';

    let allNews = [];
    let searchTimeout = null;

    /* ─────────────────────────────────────────
       CHARGEMENT
    ───────────────────────────────────────── */
    async function loadNews() {
        try {
            showLoading();
            const params = new URLSearchParams();
            const q      = searchInput?.value?.trim() || '';
            const line   = lineFilter?.value   || '';
            const type   = typeFilter?.value   || '';
            const source = sourceFilter?.value || '';

            if (q)      params.append('q',      q);
            if (line)   params.append('line',   line);
            if (type)   params.append('type',   type);
            if (source) params.append('source', source);

            /* Lire le filtre réseau depuis l'URL (boutons navbar) */
            const networkParam = new URLSearchParams(window.location.search).get('network');
            if (networkParam && networkParam !== 'all') params.append('network', networkParam);

            // Ajouter le filtre network depuis l'URL si présent
            const urlNetwork = new URLSearchParams(window.location.search).get('network');
            if (urlNetwork && !params.has('network')) params.append('network', urlNetwork);

            const url = params.toString() ? `/api/news?${params}` : '/api/news';
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const payload = await res.json();
            allNews = Array.isArray(payload.data) ? payload.data : [];
            populateLineFilter(allNews);
            renderNews(allNews);
        } catch (err) {
            console.error(err);
            newsList.innerHTML = `<div class="news-empty"><strong>Erreur de chargement</strong>Impossible de récupérer les actualités.</div>`;
        }
    }

    function showLoading() {
        newsList.innerHTML = `<div class="news-empty">Chargement…</div>`;
    }

    /* ─────────────────────────────────────────
       FILTRE LIGNES
    ───────────────────────────────────────── */
    function populateLineFilter(items) {
        if (!lineFilter) return;
        const cur = lineFilter.value;
        const lines = [...new Set(items.map(i => i.line).filter(l => l?.trim()))]
            .sort((a, b) => a.localeCompare(b, 'fr', { numeric: true }));

        lineFilter.innerHTML = '<option value="">Toutes les lignes</option>';
        lines.forEach(l => {
            const o = document.createElement('option');
            o.value = l; o.textContent = l;
            lineFilter.appendChild(o);
        });
        if (lines.includes(cur)) lineFilter.value = cur;
    }

    /* ─────────────────────────────────────────
       RENDU
    ───────────────────────────────────────── */
    function renderNews(items) {
        if (!items?.length) {
            newsList.innerHTML = `<div class="news-empty"><strong>Aucune actualité</strong>Aucun résultat pour ces filtres.</div>`;
            return;
        }
        newsList.innerHTML = items.map(createNewsCard).join('');
        bindCardActions();
    }

    /* ─────────────────────────────────────────
       PASTILLE LIGNE (couleurs officielles RATP)
    ───────────────────────────────────────── */
    const LINE_MAP = {
        '1':{'cls':'line-m1','extra':'','label':'1'},'2':{'cls':'line-m2','extra':'','label':'2'},
        '3':{'cls':'line-m3','extra':'','label':'3'},'3b':{'cls':'line-m3b','extra':'','label':'3b'},
        '4':{'cls':'line-m4','extra':'','label':'4'},'5':{'cls':'line-m5','extra':'','label':'5'},
        '6':{'cls':'line-m6','extra':'','label':'6'},'7':{'cls':'line-m7','extra':'','label':'7'},
        '7b':{'cls':'line-m7b','extra':'','label':'7b'},'8':{'cls':'line-m8','extra':'','label':'8'},
        '9':{'cls':'line-m9','extra':'','label':'9'},'10':{'cls':'line-m10','extra':'','label':'10'},
        '11':{'cls':'line-m11','extra':'','label':'11'},'12':{'cls':'line-m12','extra':'','label':'12'},
        '13':{'cls':'line-m13','extra':'','label':'13'},'14':{'cls':'line-m14','extra':'','label':'14'},
        'rer a':{'cls':'line-rera','extra':'rer','label':'A'},'rera':{'cls':'line-rera','extra':'rer','label':'A'},
        'rer b':{'cls':'line-rerb','extra':'rer','label':'B'},'rerb':{'cls':'line-rerb','extra':'rer','label':'B'},
        'rer c':{'cls':'line-rerc','extra':'rer','label':'C'},'rerc':{'cls':'line-rerc','extra':'rer','label':'C'},
        'rer d':{'cls':'line-rerd','extra':'rer','label':'D'},'rerd':{'cls':'line-rerd','extra':'rer','label':'D'},
        'rer e':{'cls':'line-rere','extra':'rer','label':'E'},'rere':{'cls':'line-rere','extra':'rer','label':'E'},
        't1':{'cls':'line-t1','extra':'tram','label':'T1'},'t2':{'cls':'line-t2','extra':'tram','label':'T2'},
        't3a':{'cls':'line-t3a','extra':'tram','label':'T3a'},'t3b':{'cls':'line-t3b','extra':'tram','label':'T3b'},
        't4':{'cls':'line-t4','extra':'tram','label':'T4'},'t5':{'cls':'line-t5','extra':'tram','label':'T5'},
        't6':{'cls':'line-t6','extra':'tram','label':'T6'},'t7':{'cls':'line-t7','extra':'tram','label':'T7'},
        't8':{'cls':'line-t8','extra':'tram','label':'T8'},'t9':{'cls':'line-t9','extra':'tram','label':'T9'},
        't10':{'cls':'line-t10','extra':'tram','label':'T10'},'t11':{'cls':'line-t11','extra':'tram','label':'T11'},
        't12':{'cls':'line-t12','extra':'tram','label':'T12'},'t13':{'cls':'line-t13','extra':'tram','label':'T13'},
        'h':{'cls':'line-h','extra':'transilien','label':'H'},'j':{'cls':'line-j','extra':'transilien','label':'J'},
        'k':{'cls':'line-k','extra':'transilien','label':'K'},'l':{'cls':'line-l','extra':'transilien','label':'L'},
        'n':{'cls':'line-n','extra':'transilien','label':'N'},'p':{'cls':'line-p','extra':'transilien','label':'P'},
        'r':{'cls':'line-r','extra':'transilien','label':'R'},'u':{'cls':'line-u','extra':'transilien','label':'U'},
    };

    function renderLineBadge(raw) {
        // Pas de ligne → rien du tout
        if (!raw || raw.trim() === '') return '';

        const key  = raw.trim().toLowerCase();
        const line = LINE_MAP[key];

        // Ligne non reconnue → pastille masquée (pas de texte géant)
        if (!line) return `<span class="line-badge line-unknown"></span>`;

        const extra = line.extra ? ` ${line.extra}` : '';
        const inner = line.extra === 'transilien'
            ? `<span>${line.label}</span>` : line.label;

        return `<span class="line-badge ${line.cls}${extra}" title="Ligne ${escapeHtml(raw)}">${inner}</span>`;
    }

    /* ─────────────────────────────────────────
       CRÉATION D'UNE CARTE
    ───────────────────────────────────────── */
    const TYPE_CLS   = { perturbation:'badge-perturbation', travaux:'badge-travaux', incident:'badge-incident', info:'badge-info' };
    const SOURCE_CLS = { official:'badge-officielle', community:'badge-communaute' };

    function createNewsCard(item) {
        const type    = item.type   || '';
        const source  = item.source || '';

        // Si le titre est générique, on utilise le contenu comme titre
        const rawTitle   = item.title   || '';
        const rawContent = stripHtml(item.content || '');
        const GENERIC    = ['perturbation','travaux','incident','info','sans titre',''];
        const isGeneric  = GENERIC.includes(rawTitle.trim().toLowerCase());

        const title   = escapeHtml(isGeneric ? truncate(rawContent, 90) : rawTitle);
        const content = isGeneric ? '' : escapeHtml(truncate(rawContent, 120));
        const views   = Number.isInteger(item.views) ? item.views : 0;
        const date    = formatDate(item.publishedAt);
        const network = formatNetwork(item.network);

        const typeLbl   = formatType(type);
        const sourceLbl = formatSource(source);
        const typeCls   = TYPE_CLS[type]   || 'badge-info';
        const sourceCls = SOURCE_CLS[source] || 'badge-officielle';
        const lineBadge = renderLineBadge(item.line);

        return `
<article class="news-card" data-type="${escapeHtml(type)}" data-news-id="${item.id}">
  <div class="news-card-top">
    <div class="news-meta">
      <span class="badge ${typeCls}">${escapeHtml(typeLbl)}</span>
      <span class="badge badge-line">${escapeHtml(network)}</span>
      ${lineBadge}
      <span class="badge ${sourceCls}">${escapeHtml(sourceLbl)}</span>
    </div>
  </div>
  <h2 class="news-card-title">${title}</h2>
  <p class="news-card-desc">${content}</p>
  <div class="news-card-footer">
    <span class="news-card-time">${escapeHtml(date)} · ${views} vue${views > 1 ? 's' : ''}</span>
    <div class="news-card-actions">
      <a href="/news/${item.id}" class="news-link">Voir →</a>
      ${isAuthenticated && item.network ? `
        <button class="follow-line-btn" data-network="${item.network}" data-line="${item.line||''}"
          style="background:none;border:1px solid #e5e7eb;border-radius:8px;padding:3px 8px;font-size:11px;cursor:pointer;color:#6b7280;font-family:inherit;"
          title="Suivre cette ligne">⭐</button>
        <button class="report-btn" data-id="${item.id}" data-network="${item.network}" data-line="${item.line||''}"
          style="background:none;border:1px solid #fecaca;border-radius:8px;padding:3px 8px;font-size:11px;cursor:pointer;color:#dc2626;font-family:inherit;"
          title="Signaler un incident">⚠️</button>
      ` : ''}
      ${isAdmin ? `
        <button class="news-admin-btn edit-news-btn"   data-id="${item.id}" style="height:28px;padding:0 10px;font-size:11px;">Modifier</button>
        <button class="news-admin-btn danger delete-news-btn" data-id="${item.id}" style="height:28px;padding:0 10px;font-size:11px;">Supprimer</button>
      ` : ''}
    </div>
  </div>
</article>`;
    }

    /* ─────────────────────────────────────────
       ACTIONS ADMIN
    ───────────────────────────────────────── */
    function bindCardActions() {

        // ── Modale signalement ──
        // Créer la modale si elle n'existe pas
        if (!document.getElementById('report-modal')) {
            const modal = document.createElement('div');
            modal.id = 'report-modal';
            modal.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;';
            modal.innerHTML = `
                <div style="background:#fff;border-radius:16px;padding:24px;max-width:400px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.15);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0;">⚠️ Signaler un incident</h3>
                        <button id="close-report" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;line-height:1;">×</button>
                    </div>
                    <p id="report-context" style="font-size:13px;color:#6b7280;margin:0 0 14px;"></p>
                    <select id="report-type" style="width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;font-family:inherit;margin-bottom:10px;outline:none;background:#f9fafb;">
                        <option value="perturbation">Perturbation</option>
                        <option value="incident">Incident</option>
                        <option value="travaux">Travaux</option>
                        <option value="info">Information</option>
                    </select>
                    <textarea id="report-content" placeholder="Décrivez l'incident observé..." style="width:100%;min-height:80px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:13px;font-family:inherit;resize:vertical;outline:none;margin-bottom:12px;box-sizing:border-box;"></textarea>
                    <button id="submit-report" style="width:100%;height:40px;background:#dc2626;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">Envoyer le signalement</button>
                </div>`;
            document.body.appendChild(modal);

            document.getElementById('close-report').addEventListener('click', () => {
                modal.style.display = 'none';
            });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            document.getElementById('submit-report').addEventListener('click', async () => {
                const content = document.getElementById('report-content').value.trim();
                const type    = document.getElementById('report-type').value;
                const newsId  = modal.dataset.newsId;
                const network = modal.dataset.network;
                const line    = modal.dataset.line;

                if (!content) { alert('Veuillez décrire l\'incident.'); return; }

                const btn = document.getElementById('submit-report');
                btn.textContent = 'Envoi…';
                btn.disabled = true;

                try {
                    const res = await fetch('/api/news', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title: 'Signalement',
                            content,
                            type,
                            network,
                            line,
                            source: 'community'
                        })
                    });

                    if (res.ok || res.status === 201) {
                        modal.style.display = 'none';
                        document.getElementById('report-content').value = '';
                        // Feedback visuel
                        const feedback = document.createElement('div');
                        feedback.style.cssText = 'position:fixed;top:20px;right:20px;background:#16a34a;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);';
                        feedback.textContent = '✅ Signalement envoyé !';
                        document.body.appendChild(feedback);
                        setTimeout(() => feedback.remove(), 3000);
                    }
                } catch(e) {
                    console.error(e);
                } finally {
                    btn.textContent = 'Envoyer le signalement';
                    btn.disabled = false;
                }
            });
        }

        // Boutons signaler
        document.querySelectorAll('.report-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const modal = document.getElementById('report-modal');
                modal.dataset.newsId  = btn.dataset.id;
                modal.dataset.network = btn.dataset.network;
                modal.dataset.line    = btn.dataset.line;
                document.getElementById('report-context').textContent =
                    `Réseau : ${btn.dataset.network}${btn.dataset.line ? ' · Ligne ' + btn.dataset.line : ''}`;
                document.getElementById('report-content').value = '';
                modal.style.display = 'flex';
            });
        });

        // Boutons suivre ligne
        document.querySelectorAll('.follow-line-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const network = btn.dataset.network;
                const line    = btn.dataset.line;
                try {
                    const res = await fetch('/api/follow', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ network, line })
                    });
                    if (res.ok || res.status === 201) {
                        btn.textContent = '✅';
                        btn.style.borderColor = '#16a34a';
                        btn.style.color = '#16a34a';
                        setTimeout(() => { btn.textContent = '⭐'; btn.style.borderColor = '#e5e7eb'; btn.style.color = '#6b7280'; }, 2000);
                    }
                } catch(e) { console.error(e); }
            });
        });

        if (!isAdmin) return;

        document.querySelectorAll('.edit-news-btn').forEach(btn => {
            btn.addEventListener('click', async e => {
                const item = allNews.find(n => String(n.id) === e.currentTarget.dataset.id);
                if (item) await openEditPrompt(item);
            });
        });

        document.querySelectorAll('.delete-news-btn').forEach(btn => {
            btn.addEventListener('click', async e => {
                if (!confirm('Supprimer cette actualité ?')) return;
                try {
                    const res = await fetch(`/api/news/${e.currentTarget.dataset.id}`, {
                        method: 'DELETE', headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error();
                    await loadNews();
                } catch { alert('Impossible de supprimer.'); }
            });
        });
    }

    async function openEditPrompt(item) {
        const title   = prompt('Titre',   item.title   || ''); if (title   === null) return;
        const content = prompt('Contenu', item.content || ''); if (content === null) return;
        const network = prompt('Réseau (metro, rer, bus, tram)', item.network || 'metro'); if (network === null) return;
        const line    = prompt('Ligne',   item.line    || ''); if (line    === null) return;
        const type    = prompt('Type (perturbation, travaux, incident, info)', item.type || 'info'); if (type === null) return;
        const source  = prompt('Source (official, community)', item.source || 'official'); if (source === null) return;

        try {
            const res = await fetch(`/api/news/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title, content, network, line, type, source })
            });
            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Erreur.'); return; }
            await loadNews();
        } catch { alert('Impossible de modifier.'); }
    }

    async function openCreatePrompt() {
        if (!isAuthenticated) { location.href = loginUrl; return; }
        const title   = prompt('Titre'); if (!title?.trim()) return;
        const content = prompt('Contenu'); if (!content?.trim()) return;
        const network = prompt('Réseau (metro, rer, bus, tram)', 'metro'); if (network === null) return;
        const line    = prompt('Ligne', '1'); if (line === null) return;
        const type    = prompt('Type (perturbation, travaux, incident, info)', 'info'); if (type === null) return;
        const source  = prompt('Source (official, community)', 'official'); if (source === null) return;

        try {
            const res = await fetch('/api/news', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title:title.trim(), content:content.trim(), network:network.trim(), line:line.trim(), type:type.trim(), source:source.trim() })
            });
            const data = await res.json();
            if (!res.ok) { alert(data.message || 'Erreur.'); return; }
            await loadNews();
        } catch { alert('Impossible de créer.'); }
    }

    /* ─────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────── */
    function truncate(str, max) {
        return str.length <= max ? str : str.slice(0, max).trim() + '…';
    }

    function stripHtml(html) {
        return html
            .replace(/<[^>]*>/g, ' ')
            .replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<').replace(/&gt;/g, '>')
            .replace(/\s+/g, ' ').trim();
    }

    function formatDate(s) {
        if (!s) return 'Date inconnue';
        const d = new Date(s);
        return isNaN(d) ? 'Date inconnue'
            : new Intl.DateTimeFormat('fr-FR', { dateStyle:'medium', timeStyle:'short' }).format(d);
    }

    function formatNetwork(v) {
        return { metro:'Métro', rer:'RER', bus:'Bus', tram:'Tram' }[v] || v || 'Réseau';
    }

    function formatType(v) {
        return { perturbation:'Perturbation', travaux:'Travaux', incident:'Incident', info:'Info' }[v] || v || 'Type';
    }

    function formatSource(v) {
        return { official:'Officielle', community:'Communauté' }[v] || v || 'Source';
    }

    function escapeHtml(v) {
        return String(v)
            .replaceAll('&','&amp;').replaceAll('<','&lt;')
            .replaceAll('>','&gt;').replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    }

    /* ─────────────────────────────────────────
       ÉVÉNEMENTS
    ───────────────────────────────────────── */
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadNews, 300);
    });
    lineFilter?.addEventListener('change',   loadNews);
    typeFilter?.addEventListener('change',   loadNews);
    sourceFilter?.addEventListener('change', loadNews);
    if (createNewsBtn && isAdmin) createNewsBtn.addEventListener('click', openCreatePrompt);

    loadNews();
});