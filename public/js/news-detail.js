document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('news-detail');
    if (!container) return;

    const newsId          = container.dataset.newsId;
    const isAuthenticated = container.dataset.isAuthenticated === 'true';
    const isAdmin         = container.dataset.isAdmin === 'true';
    const loginUrl        = container.dataset.loginUrl;

    /* ─── Helpers ─────────────────────────────── */
    function escapeHtml(v) {
        return String(v ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function stripHtml(html) {
        return html
            .replace(/<[^>]*>/g, ' ')
            .replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<').replace(/&gt;/g, '>')
            .replace(/\s+/g, ' ').trim();
    }

    function formatDate(s) {
        if (!s) return '';
        const d = new Date(s);
        return isNaN(d) ? '' : new Intl.DateTimeFormat('fr-FR', {
            dateStyle: 'long', timeStyle: 'short'
        }).format(d);
    }

    /* ─── Pastille de ligne ─────────────────────── */
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
        if (!raw || raw.trim() === '' || raw === 'inconnue') return '';
        const key  = raw.trim().toLowerCase();
        const line = LINE_MAP[key];
        if (!line) return '';
        const extra = line.extra ? ` ${line.extra}` : '';
        const inner = line.extra === 'transilien' ? `<span>${line.label}</span>` : line.label;
        return `<span class="line-badge ${line.cls}${extra}" title="Ligne ${escapeHtml(raw)}">${inner}</span>`;
    }

    /* ─── Type → classe badge ─────────────────── */
    const TYPE_CLS = {
        perturbation: 'badge-perturbation',
        travaux:      'badge-travaux',
        incident:     'badge-incident',
        info:         'badge-info',
    };
    const SOURCE_CLS = {
        official:  'badge-officielle',
        community: 'badge-communaute',
    };
    const TYPE_LABELS   = { perturbation:'Perturbation', travaux:'Travaux', incident:'Incident', info:'Info' };
    const SOURCE_LABELS = { official:'Officielle', community:'Communauté' };
    const NETWORK_LABELS = { metro:'Métro', rer:'RER', bus:'Bus', tram:'Tram', transilien:'Transilien' };

    /* ─── Chargement de la news ───────────────── */
    try {
        const res = await fetch(`/api/news/${newsId}`);
        if (!res.ok) throw new Error('Erreur HTTP ' + res.status);
        const news = await res.json();

        const type       = news.type   || '';
        const source     = news.source || '';
        const network    = news.network || '';
        const typeCls    = TYPE_CLS[type]     || 'badge-info';
        const sourceCls  = SOURCE_CLS[source] || 'badge-officielle';
        const typeLabel  = TYPE_LABELS[type]     || type;
        const srcLabel   = SOURCE_LABELS[source] || source;
        const netLabel   = NETWORK_LABELS[network] || network;
        const lineBadge  = renderLineBadge(news.line);
        const date       = formatDate(news.publishedAt);
        const views      = news.views || 0;
        const content    = stripHtml(news.content || '');

        /* Bloc actions selon rôle */
        let actionBlock = '';
        if (isAdmin) {
            actionBlock = `
                <div style="display:flex;gap:10px;flex-wrap:wrap;padding-top:16px;border-top:1px solid #f0f2f5;margin-top:4px;">
                    <button id="btn-delete" style="display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;border:1px solid #fecaca;background:#fef2f2;color:#dc2626;cursor:pointer;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Supprimer
                    </button>
                </div>
                <div style="margin-top:16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:16px;">
                    <p style="font-size:13px;font-weight:600;color:#111827;margin:0 0 10px;">Répondre en tant qu'admin</p>
                    <textarea id="admin-response" placeholder="Votre réponse…" style="width:100%;min-height:80px;padding:10px 12px;border:1px solid #e2e5ea;border-radius:8px;font-size:13px;font-family:inherit;color:#111827;background:#fff;resize:vertical;outline:none;margin-bottom:10px;"></textarea>
                    <button id="send-response" style="display:inline-flex;align-items:center;height:36px;padding:0 16px;border-radius:10px;font-size:13px;font-weight:600;font-family:inherit;border:none;background:#0891b2;color:#fff;cursor:pointer;">Envoyer</button>
                </div>`;
        } else if (isAuthenticated) {
            actionBlock = `
                <div style="margin-top:20px;padding:14px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;font-size:13px;color:#2563eb;display:flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#2563eb" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="#2563eb" stroke-width="2" stroke-linecap="round"/></svg>
                    Vous consultez cette actualité en tant qu'utilisateur connecté.
                </div>`;
        } else {
            actionBlock = `
                <div style="margin-top:20px;padding:14px 16px;background:var(--blue-soft);border:1px solid var(--blue-mid);border-radius:var(--radius-md);font-size:13px;color:var(--blue);">
                    <a href="${loginUrl}" style="font-weight:600;color:var(--blue);">Connectez-vous</a> pour commenter cette actualité.
                </div>`;
        }

        /* Couleurs par type */
        const TYPE_COLORS = {
            perturbation: { bg:'#fef2f2', color:'#dc2626', border:'#fecaca', bar:'#dc2626' },
            travaux:      { bg:'#fffbeb', color:'#d97706', border:'#fde68a', bar:'#d97706' },
            incident:     { bg:'#fff7ed', color:'#ea580c', border:'#fed7aa', bar:'#ea580c' },
            info:         { bg:'#eff6ff', color:'#2563eb', border:'#bfdbfe', bar:'#2563eb' },
        };
        const SOURCE_COLORS = {
            official:  { bg:'#f3f4f6', color:'#6b7280', border:'#e5e7eb' },
            community: { bg:'#f0fdf4', color:'#16a34a', border:'#bbf7d0' },
        };
        const tc = TYPE_COLORS[type]   || { bg:'#eff6ff', color:'#2563eb', border:'#bfdbfe', bar:'#2563eb' };
        const sc = SOURCE_COLORS[source] || { bg:'#f3f4f6', color:'#6b7280', border:'#e5e7eb' };

        /* Rendu HTML principal */
        container.innerHTML = `
<div style="background:#fff;border:1px solid #e2e5ea;border-radius:16px;padding:24px 28px;box-shadow:0 1px 4px rgba(0,0,0,.08);position:relative;overflow:hidden;margin-bottom:20px;">
    <div style="position:absolute;top:0;left:0;right:0;height:4px;background:${tc.bar};border-radius:16px 16px 0 0;"></div>

    <div style="display:flex;flex-wrap:wrap;gap:6px;margin:12px 0 16px;align-items:center;">
        <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:${tc.bg};color:${tc.color};border:1px solid ${tc.border};">${escapeHtml(typeLabel)}</span>
        <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;">${escapeHtml(netLabel)}</span>
        ${lineBadge}
        <span style="padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:${sc.bg};color:${sc.color};border:1px solid ${sc.border};">${escapeHtml(srcLabel)}</span>
    </div>

    <h1 style="font-size:20px;font-weight:700;color:#111827;margin:0 0 12px;line-height:1.4;">${escapeHtml(news.title || content.slice(0, 80))}</h1>

    <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:13px;color:#9ca3af;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #f0f2f5;">
        <span>📅 ${escapeHtml(date)}</span>
        <span>👁 ${views} vue${views > 1 ? 's' : ''}</span>
        ${news.author?.firstName ? `<span>✍️ ${escapeHtml(news.author.firstName)} ${escapeHtml(news.author.lastName || '')}</span>` : ''}
    </div>

    <div style="font-size:15px;color:#4b5563;line-height:1.75;margin-bottom:20px;">${escapeHtml(content)}</div>

    ${actionBlock}
</div>

${news.comments?.length > 0 ? `
<div class="comments-section" style="margin-top:20px;">
    <h2 class="comments-title">${news.comments.length} commentaire${news.comments.length > 1 ? 's' : ''}</h2>
    <div class="comments-list">
        ${news.comments.map(c => {
            const initials = ((c.author?.firstName?.[0] || '') + (c.author?.lastName?.[0] || '')).toUpperCase() || '?';
            const name = [c.author?.firstName, c.author?.lastName].filter(Boolean).join(' ') || 'Utilisateur';
            const cDate = formatDate(c.createdAt);
            return `
<div class="comment-card" data-comment-id="${c.id}">
    <div class="comment-header">
        <div class="comment-author">
            <div class="comment-avatar">${escapeHtml(initials)}</div>
            <div>
                <div class="comment-name">${escapeHtml(name)}</div>
                <div class="comment-time">${escapeHtml(cDate)}</div>
            </div>
        </div>
    </div>
    <p class="comment-body">${escapeHtml(c.content || '')}</p>
    ${isAdmin ? `
    <div class="comment-actions">
        <button class="comment-btn delete" data-comment-id="${c.id}">Supprimer</button>
    </div>` : ''}
</div>`;
        }).join('')}
    </div>
</div>` : ''}
`;

        /* ─── Événements ─────────────────────────── */

        // Supprimer la news (admin)
        document.getElementById('btn-delete')?.addEventListener('click', async () => {
            if (!confirm('Supprimer cette actualité ?')) return;
            try {
                const r = await fetch(`/api/news/${newsId}`, { method: 'DELETE' });
                if (!r.ok) throw new Error();
                window.location.href = '/news';
            } catch { alert('Erreur lors de la suppression.'); }
        });

        // Réponse admin
        document.getElementById('send-response')?.addEventListener('click', async () => {
            const content = document.getElementById('admin-response')?.value?.trim();
            if (!content) return;
            try {
                const r = await fetch(`/api/news/${newsId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content })
                });
                if (!r.ok) throw new Error();
                document.getElementById('admin-response').value = '';
                window.location.reload();
            } catch { alert('Erreur lors de l\'envoi.'); }
        });

        // Commentaire utilisateur
        document.getElementById('send-comment')?.addEventListener('click', async () => {
            const content = document.getElementById('user-comment')?.value?.trim();
            if (!content) return;
            try {
                const r = await fetch(`/api/news/${newsId}/comments`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content })
                });
                if (!r.ok) throw new Error();
                document.getElementById('user-comment').value = '';
                window.location.reload();
            } catch { alert('Erreur lors de l\'envoi.'); }
        });

        // Supprimer commentaire (admin)
        document.querySelectorAll('.comment-btn.delete').forEach(btn => {
            btn.addEventListener('click', async () => {
                const cId = btn.dataset.commentId;
                if (!confirm('Supprimer ce commentaire ?')) return;
                try {
                    const r = await fetch(`/api/comments/${cId}`, { method: 'DELETE' });
                    if (!r.ok) throw new Error();
                    btn.closest('.comment-card')?.remove();
                } catch { alert('Erreur lors de la suppression.'); }
            });
        });

    } catch (error) {
        console.error(error);
        container.innerHTML = `
<div class="news-card">
    <p style="color:var(--text-muted);text-align:center;padding:32px 0;">
        Erreur lors du chargement de l'actualité.
    </p>
</div>`;
    }
});