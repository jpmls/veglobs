/**
 * VeGlobs — journey-planner.js
 * Calcul d'itinéraire via l'API IDFM Navitia
 */
document.addEventListener('DOMContentLoaded', () => {

    const fromInput   = document.getElementById('fromInput');
    const fromResults = document.getElementById('fromResults');
    const fromId      = document.getElementById('fromId');

    const toInput     = document.getElementById('toInput');
    const toResults   = document.getElementById('toResults');
    const toId        = document.getElementById('toId');

    const swapBtn     = document.getElementById('swapBtn');
    const computeBtn  = document.getElementById('computeBtn');
    const resultsDiv  = document.getElementById('journeyResults');

    if (!fromInput || !toInput) return;

    /* ─── Autocomplete ─────────────────────────── */
    let searchTimeout = null;

    function setupAutocomplete(input, resultsBox, hiddenId) {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const q = input.value.trim();
            if (q.length < 2) { hideBox(resultsBox); return; }
            searchTimeout = setTimeout(() => searchPlaces(q, resultsBox, input, hiddenId), 300);
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsBox.contains(e.target)) {
                hideBox(resultsBox);
            }
        });
    }

    async function searchPlaces(q, resultsBox, input, hiddenId) {
        try {
            const res  = await fetch(`/api/journey/places?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            renderPlaceResults(data, resultsBox, input, hiddenId);
        } catch (e) {
            console.error(e);
        }
    }

    function renderPlaceResults(places, resultsBox, input, hiddenId) {
        resultsBox.innerHTML = '';

        if (!places.length) {
            resultsBox.innerHTML = '<div class="line-result-empty">Aucun résultat</div>';
            showBox(resultsBox);
            return;
        }

        places.forEach(place => {
            const item = document.createElement('div');
            item.className = 'line-result-item';

            const icon = place.type === 'stop_area' ? '🚉' : '📍';
            item.innerHTML = `
                <div class="line-result-row">
                    <span style="font-size:18px;">${icon}</span>
                    <div>
                        <div class="line-result-name">${escapeHtml(place.name)}</div>
                        <div class="line-result-mode">${place.type === 'stop_area' ? 'Arrêt' : 'Adresse'}</div>
                    </div>
                </div>`;

            item.addEventListener('click', () => {
                input.value    = place.name;
                hiddenId.value = place.id;
                hideBox(resultsBox);

                // Centrer la carte sur le lieu
                if (place.lat && place.lon && window.journeyMap) {
                    window.journeyMap.setView([place.lat, place.lon], 15);
                    L.marker([place.lat, place.lon]).addTo(window.journeyMap)
                        .bindPopup(place.name).openPopup();
                }
            });

            resultsBox.appendChild(item);
        });

        showBox(resultsBox);
    }

    function showBox(box) { box.classList.add('visible'); }
    function hideBox(box) { box.classList.remove('visible'); box.innerHTML = ''; }

    setupAutocomplete(fromInput, fromResults, fromId);
    setupAutocomplete(toInput,   toResults,   toId);

    /* ─── Swap ─────────────────────────────────── */
    swapBtn?.addEventListener('click', () => {
        const tmpTxt = fromInput.value;
        const tmpId  = fromId.value;
        fromInput.value = toInput.value;
        fromId.value    = toId.value;
        toInput.value   = tmpTxt;
        toId.value      = tmpId;
    });

    /* ─── Calcul itinéraire ─────────────────────── */
    computeBtn?.addEventListener('click', computeJourney);

    async function computeJourney() {
        const from = fromId.value || fromInput.value.trim();
        const to   = toId.value   || toInput.value.trim();

        if (!from || !to) {
            alert('Veuillez renseigner le départ et l\'arrivée.');
            return;
        }

        computeBtn.textContent = 'Calcul en cours…';
        computeBtn.disabled    = true;
        resultsDiv.style.display = 'block';
        resultsDiv.innerHTML = '<div class="journey-loading">⏳ Recherche des itinéraires…</div>';

        try {
            const res  = await fetch(`/api/journey/compute?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
            const data = await res.json();

            if (data.error) {
                resultsDiv.innerHTML = `<div class="journey-error">❌ ${escapeHtml(data.error)}</div>`;
                return;
            }

            if (!data.length) {
                resultsDiv.innerHTML = '<div class="journey-error">Aucun itinéraire trouvé.</div>';
                return;
            }

            renderJourneys(data);
            // Attendre que la map soit prête puis tracer
            const tryDraw = () => {
                if (window.journeyMap) {
                    drawJourneyOnMap(data[0]);
                } else {
                    setTimeout(tryDraw, 200);
                }
            };
            tryDraw();

        } catch (e) {
            console.error(e);
            resultsDiv.innerHTML = '<div class="journey-error">Erreur lors du calcul.</div>';
        } finally {
            computeBtn.textContent = 'Calculer l\'itinéraire';
            computeBtn.disabled    = false;
        }
    }

    /* ─── Rendu des itinéraires ─────────────────── */
    function renderJourneys(journeys) {
        resultsDiv.innerHTML = '';

        journeys.forEach((journey, i) => {
            const duration   = formatDuration(journey.duration);
            const departure  = formatTime(journey.departure);
            const arrival    = formatTime(journey.arrival);
            const transfers  = journey.nb_transfers;
            const co2        = journey.co2_emission ? `${Math.round(journey.co2_emission)}g CO₂` : '';

            const sectionsHtml = journey.sections
                .filter(s => s.type !== 'waiting')
                .map(s => renderSection(s))
                .join('<span class="section-arrow">›</span>');

            const card = document.createElement('div');
            card.className = 'journey-option' + (i === 0 ? ' journey-option--best' : '');

            card.innerHTML = `
                <div class="journey-option-header">
                    <div class="journey-option-time">
                        <span class="journey-depart">${departure}</span>
                        <span class="journey-arrow">→</span>
                        <span class="journey-arrive">${arrival}</span>
                    </div>
                    <div class="journey-option-meta">
                        <span class="journey-duration">⏱ ${duration}</span>
                        <span class="journey-transfers">${transfers} correspondance${transfers > 1 ? 's' : ''}</span>
                        ${co2 ? `<span class="journey-co2">🌿 ${co2}</span>` : ''}
                    </div>
                </div>
                <div class="journey-sections">${sectionsHtml}</div>
                <div class="journey-detail" style="display:none;">
                    ${renderDetailedSections(journey.sections)}
                </div>
                <button class="journey-detail-btn" type="button">Voir le détail ▾</button>
            `;

            // Toggle détail
            card.querySelector('.journey-detail-btn').addEventListener('click', function() {
                const detail = card.querySelector('.journey-detail');
                const open   = detail.style.display !== 'none';
                detail.style.display = open ? 'none' : 'block';
                this.textContent     = open ? 'Voir le détail ▾' : 'Masquer ▴';
                if (!open) drawJourneyOnMap(journey);
            });

            resultsDiv.appendChild(card);
        });
    }

    function renderSection(section) {
        if (section.type === 'public_transport') {
            const line = section.lines[0];
            if (line) {
                return `<span class="section-pill" style="background:${line.color};color:#fff;" title="${escapeHtml(line.network)}">${escapeHtml(line.code || line.label)}</span>`;
            }
        }
        if (section.type === 'street_network' || section.mode === 'walking') {
            return `<span class="section-walk">🚶 ${formatDuration(section.duration)}</span>`;
        }
        if (section.type === 'transfer') {
            return `<span class="section-walk">🔄</span>`;
        }
        return '';
    }

    function renderDetailedSections(sections) {
        return sections.filter(s => s.type !== 'waiting').map(s => {
            const dep = formatTime(s.departure);
            const arr = formatTime(s.arrival);
            const dur = formatDuration(s.duration);

            if (s.type === 'public_transport') {
                const line = s.lines[0];
                const color = line?.color || '#888';
                return `
                    <div class="detail-section">
                        <div class="detail-section-line" style="border-left-color:${color};">
                            <div class="detail-section-header">
                                ${line ? `<span class="section-pill" style="background:${color};color:#fff;">${escapeHtml(line.code || line.label)}</span>` : ''}
                                <strong>${escapeHtml(s.from || '')}</strong>
                                <span class="detail-time">${dep}</span>
                            </div>
                            <div class="detail-section-body">
                                Direction ${escapeHtml(s.to || '')} · ${dur}
                            </div>
                            <div class="detail-section-footer">
                                <strong>${escapeHtml(s.to || '')}</strong>
                                <span class="detail-time">${arr}</span>
                            </div>
                        </div>
                    </div>`;
            }

            if (s.mode === 'walking' || s.type === 'street_network') {
                return `
                    <div class="detail-section detail-section--walk">
                        🚶 Marche · ${dur}
                    </div>`;
            }

            return '';
        }).join('');
    }

    /* ─── Tracé sur la carte ─────────────────────── */
    let routeLayers = [];

    function getMap() {
        return window.journeyMap || null;
    }

    function drawJourneyOnMap(journey) {
        const map = getMap();
        if (!map) {
            // Réessayer après 500ms si la carte n'est pas encore prête
            setTimeout(() => drawJourneyOnMap(journey), 500);
            return;
        }

        // Supprimer ancien tracé
        routeLayers.forEach(l => map.removeLayer(l));
        routeLayers = [];

        const bounds = [];

        // Marqueurs départ/arrivée
        const firstSection = journey.sections.find(s => s.from && s.departure);
        const lastSection  = [...journey.sections].reverse().find(s => s.to && s.arrival);

        journey.sections.forEach(section => {
            if (!section.geojson?.coordinates?.length) return;

            const color  = section.lines?.[0]?.color || '#0891b2';
            const isWalk = section.mode === 'walking' || section.type === 'street_network' || section.type === 'crow_fly';

            const coords = section.geojson.coordinates.map(c => [c[1], c[0]]);
            bounds.push(...coords);

            const poly = L.polyline(coords, {
                color:     isWalk ? '#9ca3af' : color,
                weight:    isWalk ? 3 : 6,
                opacity:   isWalk ? 0.5 : 0.9,
                dashArray: isWalk ? '6,10' : null,
                lineCap:   'round',
                lineJoin:  'round',
            }).addTo(map);



            routeLayers.push(poly);
        });

        // Marqueur départ (vert) — sans popup
        if (firstSection?.geojson?.coordinates?.[0]) {
            const c = firstSection.geojson.coordinates[0];
            const icon = L.divIcon({
                className: '',
                html: `<div style="width:14px;height:14px;border-radius:50%;background:#16a34a;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });
            const m = L.marker([c[1], c[0]], { icon }).addTo(map);
            routeLayers.push(m);
        }

        // Marqueur arrivée (rouge) — sans popup
        if (lastSection?.geojson?.coordinates) {
            const coords = lastSection.geojson.coordinates;
            const c = coords[coords.length - 1];
            const icon = L.divIcon({
                className: '',
                html: `<div style="width:14px;height:14px;border-radius:50%;background:#dc2626;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });
            const m = L.marker([c[1], c[0]], { icon }).addTo(map);
            routeLayers.push(m);
        }

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    /* ─── Helpers ───────────────────────────────── */
    function formatDuration(seconds) {
        if (!seconds) return '0 min';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        return h > 0 ? `${h}h${m.toString().padStart(2, '0')}` : `${m} min`;
    }

    function formatTime(dateStr) {
        if (!dateStr) return '--:--';
        // Format Navitia : YYYYMMDDTHHmmss
        const h = dateStr.substring(9,  11);
        const m = dateStr.substring(11, 13);
        return `${h}:${m}`;
    }

    function escapeHtml(v) {
        return String(v ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Exposer la map pour le tracé
    window.addEventListener('mapReady', (e) => {
        window.journeyMap = e.detail;
    });
});