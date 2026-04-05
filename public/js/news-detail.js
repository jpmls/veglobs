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
                <div class="news-detail-actions">
                    <button class="btn btn-primary" id="btn-edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Modifier
                    </button>
                    <button class="btn btn-danger" id="btn-delete">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="3 6 5 6 21 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Supprimer
                    </button>
                </div>
                <div class="comment-form" style="margin-top:20px;">
                    <p style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:10px;">Répondre en tant qu'admin</p>
                    <textarea id="admin-response" placeholder="Votre réponse…"></textarea>
                    <button class="btn btn-primary" id="send-response">Envoyer</button>
                </div>`;
        } else if (isAuthenticated) {
            actionBlock = `
                <div class="comment-form" style="margin-top:20px;">
                    <p style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:10px;">Laisser un commentaire</p>
                    <textarea id="user-comment" placeholder="Votre commentaire…"></textarea>
                    <button class="btn btn-primary" id="send-comment">Envoyer</button>
                </div>`;
        } else {
            actionBlock = `
                <div style="margin-top:20px;padding:14px 16px;background:var(--blue-soft);border:1px solid var(--blue-mid);border-radius:var(--radius-md);font-size:13px;color:var(--blue);">
                    <a href="${loginUrl}" style="font-weight:600;color:var(--blue);">Connectez-vous</a> pour commenter cette actualité.
                </div>`;
        }

        /* Rendu HTML principal */
        container.innerHTML = `
<div class="news-card" data-type="${escapeHtml(type)}">
    <div class="news-meta">
        <span class="badge ${typeCls}">${escapeHtml(typeLabel)}</span>
        <span class="badge badge-line">${escapeHtml(netLabel)}</span>
        ${lineBadge}
        <span class="badge ${sourceCls}">${escapeHtml(srcLabel)}</span>
    </div>

    <h1 class="news-detail-title">${escapeHtml(news.title || content.slice(0, 80))}</h1>

    <div class="news-detail-info">
        <span>📅 ${escapeHtml(date)}</span>
        <span>👁 ${views} vue${views > 1 ? 's' : ''}</span>
        ${news.author?.firstName ? `<span>✍️ ${escapeHtml(news.author.firstName)} ${escapeHtml(news.author.lastName || '')}</span>` : ''}
    </div>

    <div class="news-detail-body">${escapeHtml(content)}</div>

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