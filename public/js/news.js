const newsList = document.getElementById('news-list');
const searchInput = document.getElementById('searchInput');
const lineFilter = document.getElementById('lineFilter');
const typeFilter = document.getElementById('typeFilter');

let allNews = [];

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

function getCommentCount(news) {
    if (Array.isArray(news.comments)) {
        return news.comments.length;
    }

    if (typeof news.commentsCount === 'number') {
        return news.commentsCount;
    }

    return 0;
}

function isRealNews(news) {
    const title = (news.title ?? '').toLowerCase().trim();
    const content = (news.content ?? '').toLowerCase().trim();

    if (title.includes('/test-api')) return false;
    if (content.includes('/test-api')) return false;
    if (title === 'news test site') return false;

    return true;
}

function renderNewsList(newsItems) {
    if (!newsList) return;

    if (!Array.isArray(newsItems) || newsItems.length === 0) {
        newsList.innerHTML = `
            <div class="news-empty">
                Aucune news trouvée.
            </div>
        `;
        return;
    }

    newsList.innerHTML = newsItems.map(news => {
        const commentCount = getCommentCount(news);

        return `
            <article class="news-card">
                <h2>${escapeHtml(news.title ?? 'Sans titre')}</h2>

                <div class="news-date">
                    ${escapeHtml(formatDate(news.publishedAt))}
                </div>

                <div class="news-card-bottom">
                    <a href="/news/${news.id}" class="news-link">Voir détail</a>
                    <div class="news-comments">${commentCount} commentaire(s)</div>
                </div>
            </article>
        `;
    }).join('');
}

function renderLineOptions(newsItems) {
    if (!lineFilter) return;

    const lines = [
        ...new Set(
            newsItems
                .map(news => `${news.network ?? ''} ${news.line ?? ''}`.trim())
                .filter(line => line !== '')
        )
    ];

    lineFilter.innerHTML = `
        <option value="">Toutes les lignes</option>
        ${lines.map(line => `
            <option value="${escapeHtml(line)}">${escapeHtml(line)}</option>
        `).join('')}
    `;
}

function applyFilters() {
    const searchValue = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const selectedLine = lineFilter ? lineFilter.value : '';
    const selectedType = typeFilter ? typeFilter.value.trim().toLowerCase() : '';

    const filteredNews = allNews.filter(news => {
        const title = (news.title ?? '').toLowerCase();
        const content = (news.content ?? '').toLowerCase();
        const line = `${news.network ?? ''} ${news.line ?? ''}`.trim();
        const type = (news.type ?? '').toLowerCase();

        const matchesSearch =
            searchValue === '' ||
            title.includes(searchValue) ||
            content.includes(searchValue);

        const matchesLine =
            selectedLine === '' ||
            line === selectedLine;

        const matchesType =
            selectedType === '' ||
            type === selectedType;

        return matchesSearch && matchesLine && matchesType;
    });

    renderNewsList(filteredNews);
}

async function loadNews() {
    if (!newsList) return;

    try {
        const response = await fetch('/api/news', {
            headers: {
                Accept: 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Erreur HTTP ' + response.status);
        }

        const json = await response.json();

        allNews = Array.isArray(json.data) ? json.data : [];
        allNews = allNews.filter(isRealNews);

        renderLineOptions(allNews);
        renderNewsList(allNews);
    } catch (error) {
        console.error(error);

        newsList.innerHTML = `
            <div class="news-error">
                Impossible de charger les news.
            </div>
        `;
    }
}

if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
}

if (lineFilter) {
    lineFilter.addEventListener('change', applyFilters);
}

if (typeFilter) {
    typeFilter.addEventListener('change', applyFilters);
}

loadNews();