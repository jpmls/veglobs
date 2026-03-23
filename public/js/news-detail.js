const newsDetail = document.getElementById('news-detail');

if (newsDetail) {
    const newsId = newsDetail.dataset.newsId;
    const isAuthenticated = newsDetail.dataset.isAuthenticated === 'true';
    const loginUrl = newsDetail.dataset.loginUrl;

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return '';

        const date = new Date(dateString);

        if (isNaN(date.getTime())) {
            return dateString;
        }

        return date.toLocaleString('fr-FR');
    }

    function typeColor(type) {
        if (type === 'official') return '#2563eb';
        if (type === 'community') return '#f59e0b';
        return 'red';
    }

    function showCommentMessage(text, type) {
        const message = document.getElementById('comment-message');
        if (!message) return;

        message.textContent = text;
        message.className = `comment-message ${type}`;
    }

    function renderCommentForm(newsId) {
        if (!isAuthenticated) {
            return `
                <div class="comment-login-box">
                    <p style="margin-bottom:10px;">Tu dois être connecté pour ajouter un commentaire.</p>
                    <a href="${escapeHtml(loginUrl)}" class="btn-login-comment">
                        Se connecter
                    </a>
                </div>
            `;
        }

        return `
            <div class="comment-form-wrapper">
                <h2 style="margin-bottom:15px;">Ajouter un commentaire</h2>

                <div id="comment-message" class="comment-message"></div>

                <form id="comment-form" class="comment-form">
                    <textarea
                        id="comment-content"
                        class="comment-textarea"
                        placeholder="Votre commentaire..."
                        required
                    ></textarea>

                    <button type="submit" id="comment-submit" class="comment-submit">
                        Envoyer
                    </button>
                </form>
            </div>
        `;
    }

    function addCommentToUI(content) {
        const commentsList = document.getElementById('comments-list');
        if (!commentsList) return;

        const emptyMessage = commentsList.querySelector('.comment-empty');
        if (emptyMessage) {
            emptyMessage.remove();
        }

        const newComment = document.createElement('div');
        newComment.className = 'comment-item';

        newComment.innerHTML = `
            <p style="margin-bottom:8px;">${escapeHtml(content)}</p>
            <small style="color:#6b7280;">
                À l'instant — vous
            </small>
        `;

        commentsList.prepend(newComment);
    }

    function renderComments(comments) {
        if (!Array.isArray(comments) || comments.length === 0) {
            return '<p class="comment-empty">Aucun commentaire pour le moment.</p>';
        }

        return comments.map(comment => `
            <div class="comment-item">
                <p style="margin-bottom:8px;">${escapeHtml(comment.content)}</p>
                <small style="color:#6b7280;">
                    ${escapeHtml(formatDate(comment.createdAt))}
                    ${comment.author?.email ? ' — ' + escapeHtml(comment.author.email) : ''}
                </small>
            </div>
        `).join('');
    }

    function renderNews(news) {
        const comments = Array.isArray(news.comments) ? news.comments : [];
        const lineText = `${news.network ?? ''} ${news.line ?? ''}`.trim();

        newsDetail.innerHTML = `
            <div class="news-card">
                <div class="news-header">
                    <strong>${escapeHtml(lineText || 'Transport')}</strong>
                    <span class="news-type" style="color:${typeColor(news.type)};">
                        ${escapeHtml(news.type ?? 'info')}
                    </span>
                </div>

                <h1 style="margin-bottom:15px;">${escapeHtml(news.title ?? 'Sans titre')}</h1>

                <small class="news-meta">
                    Publié le ${escapeHtml(formatDate(news.publishedAt))}
                </small>

                <p class="news-content">
                    ${escapeHtml(news.content ?? '')}
                </p>

                <hr class="separator">

                <h2 style="margin-bottom:15px;">Commentaires</h2>

                <div id="comments-list" class="comments-list">
                    ${renderComments(comments)}
                </div>

                <hr class="separator">

                ${renderCommentForm(news.id)}
            </div>
        `;

        if (isAuthenticated) {
            bindCommentForm(news.id);
        }
    }

    async function safeReadJson(response) {
        try {
            return await response.json();
        } catch {
            return null;
        }
    }

    function bindCommentForm(newsId) {
        const form = document.getElementById('comment-form');
        const textarea = document.getElementById('comment-content');
        const submitButton = document.getElementById('comment-submit');

        if (!form || !textarea || !submitButton) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const content = textarea.value.trim();

            if (!content) {
                showCommentMessage('Le commentaire est vide.', 'error');
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Envoi...';
            showCommentMessage('', '');

            try {
                const response = await fetch('/api/news/' + newsId + '/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content
                    })
                });

                const data = await safeReadJson(response);

                if (response.status === 401) {
                    showCommentMessage('Tu dois être connecté pour commenter.', 'error');
                    return;
                }

                if (!response.ok) {
                    const apiMessage =
                        data?.detail ||
                        data?.message ||
                        data?.description ||
                        'Erreur lors de l’envoi du commentaire.';

                    showCommentMessage(apiMessage, 'error');
                    return;
                }

                textarea.value = '';
                showCommentMessage('✅ Commentaire ajouté !', 'success');
                addCommentToUI(content);
            } catch (error) {
                console.error(error);
                showCommentMessage('❌ Erreur réseau.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Envoyer';
            }
        });
    }

    function renderError() {
        newsDetail.innerHTML = `
            <div class="news-card news-error">
                Impossible de charger cette news.
            </div>
        `;
    }

    async function loadNewsDetail() {
        try {
            const response = await fetch('/api/news/' + newsId);

            if (!response.ok) {
                throw new Error('Erreur HTTP ' + response.status);
            }

            const news = await response.json();
            renderNews(news);
        } catch (error) {
            console.error(error);
            renderError();
        }
    }

    loadNewsDetail();
}