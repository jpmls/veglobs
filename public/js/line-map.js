document.addEventListener("DOMContentLoaded", async () => {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    const map = L.map("map", {
        zoomControl: false,
        attributionControl: true,
    }).setView([48.8566, 2.3522], 13);

    window.journeyMap = map;

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Fond de carte clair style RATP
    L.tileLayer("https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", {
        attribution: '© OpenStreetMap © CARTO',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    const searchInput = document.getElementById("stopSearch");
    const resultsBox  = document.getElementById("stopResults");
    const title       = document.getElementById("lineTitle");
    const filterBtns  = document.querySelectorAll(".type-filter");
    const stopInfo    = document.getElementById("stopInfo");

    let currentMarkers = [];
    let currentType    = "all";

    /* ─── Couleurs officielles RATP ─────────────── */
    const LINE_COLORS = {
        // Métro
        "1":"#ffbe00","2":"#003ca6","3":"#837902","3b":"#6ec4e8",
        "4":"#be418d","5":"#ff7e2e","6":"#6eca97","7":"#fa9aba",
        "7b":"#6eca97","8":"#e19bdf","9":"#b6bd00","10":"#c9910d",
        "11":"#704b1c","12":"#007852","13":"#6ec4e8","14":"#62259d",
        // RER
        "A":"#d1302f","B":"#427dbd","C":"#fcd946","D":"#5e9620","E":"#bd76a1",
        // Tram
        "T1":"#0089cb","T2":"#c04191","T3a":"#ee7623","T3b":"#73bd44",
        "T4":"#d5992d","T5":"#7d4f9e","T6":"#e2231a","T7":"#00a79d",
        "T8":"#d50057","T9":"#005baa","T10":"#f9a01b","T11":"#00b8a9",
        "T12":"#7ab51d","T13":"#f57f29",
    };

    const MODE_COLORS = {
        metro:"#003ca6", rer:"#d1302f", tram:"#f59e0b",
        bus:"#2563eb", rail:"#7c3aed"
    };

    function getLineColor(line, mode, hexColor) {
        // Priorité : couleur de la BDD → couleurs RATP → couleur par mode
        if (hexColor) return '#' + hexColor.replace('#','');
        if (line && LINE_COLORS[line]) return LINE_COLORS[line];
        if (line && LINE_COLORS[line?.toUpperCase()]) return LINE_COLORS[line.toUpperCase()];
        return MODE_COLORS[mode] || "#6b7280";
    }

    function isLight(hex) {
        if (!hex || hex.length < 6) return false;
        const h = hex.replace('#','');
        const r = parseInt(h.slice(0,2),16);
        const g = parseInt(h.slice(2,4),16);
        const b = parseInt(h.slice(4,6),16);
        return (r*299+g*587+b*114)/1000 > 155;
    }

    /* ─── Coordonnées ────────────────────────────── */
    proj4.defs("EPSG:2154","+proj=lcc +lat_1=49 +lat_2=44 +lat_0=46.5 +lon_0=3 +x_0=700000 +y_0=6600000 +ellps=GRS80 +units=m +no_defs");

    function getLatLng(stop) {
        if (stop.lat && stop.lon) return [parseFloat(stop.lat), parseFloat(stop.lon)];
        if (stop.stop_lat && stop.stop_lon) {
            const a = parseFloat(stop.stop_lat), b = parseFloat(stop.stop_lon);
            if (!isNaN(a) && !isNaN(b)) {
                if (a>40&&a<55&&b>-10&&b<15) return [a,b];
                if (b>40&&b<55&&a>-10&&a<15) return [b,a];
            }
        }
        if (stop.x_epsg2154 && stop.y_epsg2154) {
            const c = proj4("EPSG:2154","EPSG:4326",[parseFloat(stop.x_epsg2154),parseFloat(stop.y_epsg2154)]);
            return [c[1],c[0]];
        }
        return null;
    }

    /* ─── Icône pastille style RATP ─────────────── */
    function makeLineIcon(line, mode, size, hexColor) {
        const color = getLineColor(line, mode, hexColor);
        const textColor = isLight(color) ? '#1a1a1a' : '#ffffff';
        const label = line || (mode === 'metro' ? 'M' : mode === 'rer' ? 'R' : mode?.slice(0,1)?.toUpperCase() || '?');
        const isShort = label.length <= 2;
        const w = isShort ? (size || 28) : Math.max(32, label.length * 9);
        const h = size || 28;

        return L.divIcon({
            className: '',
            html: `<div style="
                background:${color};
                color:${textColor};
                width:${w}px;
                height:${h}px;
                border-radius:${h/2}px;
                display:flex;
                align-items:center;
                justify-content:center;
                font-family:'Arial Rounded MT Bold','Arial Black','Arial',sans-serif;
                font-weight:900;
                font-size:${isShort ? Math.floor(h*0.5) : Math.floor(h*0.42)}px;
                box-shadow:0 2px 6px rgba(0,0,0,0.25);
                border:2px solid rgba(255,255,255,0.95);
                white-space:nowrap;
                padding:0 ${isShort?0:4}px;
                cursor:pointer;
                letter-spacing:-0.3px;
            ">${label}</div>`,
            iconSize: [w, h],
            iconAnchor: [w/2, h/2],
            popupAnchor: [0, -h/2],
        });
    }

    /* ─── Icône multi-lignes (station avec plusieurs lignes) ── */
    function makeMultiIcon(lines, mode) {
        // Affiche jusqu'à 3 pastilles côte à côte
        const shown = lines.slice(0, 3);
        const pills = shown.map(l => {
            const color = getLineColor(l, mode);
            const textColor = isLight(color) ? '#1a1a1a' : '#ffffff';
            return `<div style="
                background:${color};color:${textColor};
                min-width:22px;height:22px;border-radius:11px;
                display:inline-flex;align-items:center;justify-content:center;
                font-family:'Arial Rounded MT Bold','Arial Black',sans-serif;
                font-weight:900;font-size:9px;
                border:1.5px solid rgba(255,255,255,0.9);
                padding:0 3px;white-space:nowrap;
            ">${l}</div>`;
        }).join('');

        const extra = lines.length > 3 ? `<div style="font-size:9px;color:#6b7280;font-weight:700;">+${lines.length-3}</div>` : '';

        return L.divIcon({
            className: '',
            html: `<div style="display:flex;align-items:center;gap:2px;background:rgba(255,255,255,0.95);border-radius:14px;padding:3px 5px;box-shadow:0 2px 8px rgba(0,0,0,0.2);border:1px solid rgba(0,0,0,0.08);">${pills}${extra}</div>`,
            iconSize: [shown.length * 26, 28],
            iconAnchor: [shown.length * 13, 14],
        });
    }

    /* ─── Info panneau gauche ────────────────────── */
    function showStopInfo(stop, lines) {
        if (!stopInfo) return;
        const mode = stop.stop_type || 'bus';

        const pills = (lines || [stop.line || stop.route_short_name]).filter(Boolean).map(l => {
            const color = getLineColor(l, mode);
            const textColor = isLight(color) ? '#1a1a1a' : '#fff';
            return `<span style="display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:14px;background:${color};color:${textColor};font-family:'Arial Rounded MT Bold','Arial Black',sans-serif;font-weight:900;font-size:11px;padding:0 6px;box-shadow:0 1px 4px rgba(0,0,0,.2);">${l}</span>`;
        }).join('');

        stopInfo.innerHTML = `
            <div style="margin-bottom:10px;">
                <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:6px;">${stop.name || 'Arrêt sans nom'}</div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:8px;">${mode}${stop.town ? ' · ' + stop.town : ''}</div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;">${pills}</div>
            </div>
        `;
    }

    /* ─── Regrouper les stops par position proche ── */
    function groupByPosition(stops, threshold = 0.0003) {
        const groups = [];
        const used = new Set();

        stops.forEach((stop, i) => {
            if (used.has(i)) return;
            const latlng = getLatLng(stop);
            if (!latlng) return;

            const group = { stops: [stop], latlng, lines: [] };
            const line = stop.line || stop.route_short_name;
            if (line) group.lines.push(line);

            stops.forEach((other, j) => {
                if (i === j || used.has(j)) return;
                const ol = getLatLng(other);
                if (!ol) return;
                const dist = Math.abs(latlng[0]-ol[0]) + Math.abs(latlng[1]-ol[1]);
                if (dist < threshold) {
                    group.stops.push(other);
                    const ol2 = other.line || other.route_short_name;
                    if (ol2 && !group.lines.includes(ol2)) group.lines.push(ol2);
                    used.add(j);
                }
            });

            used.add(i);
            groups.push(group);
        });

        return groups;
    }

    /* ─── Rendu des stops ────────────────────────── */
    function clearMarkers() {
        currentMarkers.forEach(m => map.removeLayer(m));
        currentMarkers = [];
    }

    function renderStops(stops, label) {
        clearMarkers();

        if (!stops?.length) {
            if (title) title.textContent = "Aucun arrêt trouvé";
            return;
        }
        if (title) title.textContent = label;

        const mode = stops[0]?.stop_type || currentType || 'bus';

        // Regrouper les arrêts proches
        const groups = groupByPosition(stops);
        const bounds = [];

        groups.forEach(group => {
            const { latlng, lines, stops: groupStops } = group;
            bounds.push(latlng);

            // Récupérer la couleur du premier stop du groupe
            const firstStop = groupStops[0];
            const lineColor = firstStop?.line_color || null;

            let icon;
            if (lines.length === 0) {
                icon = makeLineIcon(null, mode, 22);
            } else if (lines.length === 1) {
                icon = makeLineIcon(lines[0], mode, 28, lineColor);
            } else {
                icon = makeMultiIcon(lines, mode, groupStops);
            }

            const marker = L.marker(latlng, { icon });

            marker.on('click', () => {
                showStopInfo(groupStops[0], lines);
                map.setView(latlng, Math.max(map.getZoom(), 16));
            });

            marker.addTo(map);
            currentMarkers.push(marker);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 14 });
        }
    }

    /* ─── Chargement par type ────────────────────── */
    async function loadStopsByType(type) {
        if (title) title.textContent = "Chargement…";
        clearMarkers();
        currentType = type;

        // Style actif sur le bouton
        filterBtns.forEach(b => {
            const isActive = b.dataset.type === type;
            b.style.background = isActive ? '#111827' : '#fff';
            b.style.color = isActive ? '#fff' : '#6b7280';
            b.style.borderColor = isActive ? '#111827' : '#e5e7eb';
        });

        try {
            const res  = await fetch(`/api/stops/by-type?type=${type}`);
            const data = await res.json();
            const label = type === "all" ? "Tous les arrêts" : `Arrêts ${type}`;
            // Construire une map ligne→couleur depuis les données API
            if (data.lines) {
                data.lines.forEach(l => {
                    if (l.short_name && l.color_hex) {
                        LINE_COLORS[l.short_name] = '#' + l.color_hex.replace('#','');
                    }
                });
            }
            renderStops(data.stops, label);
        } catch(e) {
            console.error("Erreur stops:", e);
        }
    }

    /* ─── Recherche ──────────────────────────────── */
    function renderSearchResults(stops) {
        resultsBox.innerHTML = '';
        if (!stops?.length) {
            resultsBox.innerHTML = '<div class="line-result-empty">Aucun résultat</div>';
            resultsBox.classList.add('visible');
            return;
        }
        stops.forEach(stop => {
            const mode  = stop.stop_type || 'bus';
            const line  = stop.line || stop.route_short_name || null;
            const color = getLineColor(line, mode, hexColor);
            const item  = document.createElement('div');
            item.className = 'line-result-item';
            item.innerHTML = `
                <div class="line-result-row">
                    <span style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:22px;border-radius:11px;background:${color};color:${isLight(color)?'#1a1a1a':'#fff'};font-family:'Arial Rounded MT Bold',sans-serif;font-weight:900;font-size:10px;padding:0 5px;flex-shrink:0;">${line||mode.slice(0,2).toUpperCase()}</span>
                    <div>
                        <div class="line-result-name">${stop.name}</div>
                        <div class="line-result-mode">${mode}${stop.town?' · '+stop.town:''}</div>
                    </div>
                </div>`;
            item.addEventListener('click', () => {
                if (searchInput) searchInput.value = stop.name;
                resultsBox.classList.remove('visible');
                resultsBox.innerHTML = '';
                renderStops([stop], stop.name);
                const ll = getLatLng(stop);
                if (ll) map.setView(ll, 16);
                showStopInfo(stop, [stop.line||stop.route_short_name].filter(Boolean));
            });
            resultsBox.appendChild(item);
        });
        resultsBox.classList.add('visible');
    }

    let searchTimer = null;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchInput.value.trim();
        if (q.length < 2) { resultsBox.classList.remove('visible'); return; }
        searchTimer = setTimeout(async () => {
            try {
                const res   = await fetch(`/api/stops/search?q=${encodeURIComponent(q)}&type=${currentType}`);
                const stops = await res.json();
                renderSearchResults(stops);
            } catch(e) { console.error(e); }
        }, 250);
    });

    document.addEventListener('click', e => {
        if (!searchInput?.contains(e.target) && !resultsBox?.contains(e.target)) {
            resultsBox?.classList.remove('visible');
        }
    });

    /* ─── Filtres ────────────────────────────────── */
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            resultsBox?.classList.remove('visible');
            loadStopsByType(btn.dataset.type);
        });
    });

    // Carte vide au départ
    if (title) title.textContent = "Sélectionnez un type de transport";
});