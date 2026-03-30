document.addEventListener("DOMContentLoaded", async () => {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    const map = L.map("map").setView([48.8566, 2.3522], 11);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap"
    }).addTo(map);

    const searchInput = document.getElementById("stopSearch");
    const resultsBox = document.getElementById("stopResults");
    const title = document.getElementById("lineTitle");
    const filterButtons = document.querySelectorAll(".type-filter");
    const stopInfo = document.getElementById("stopInfo");

    let markers = [];
    let currentType = "all";
    let selectedMarker = null;

    proj4.defs(
        "EPSG:2154",
        "+proj=lcc +lat_1=49 +lat_2=44 +lat_0=46.5 +lon_0=3 +x_0=700000 +y_0=6600000 +ellps=GRS80 +units=m +no_defs"
    );

    function getLineColor(mode, line = null) {
        const ratpColors = {
            "1": "#ffcd00",
            "2": "#003ca6",
            "3": "#837902",
            "3b": "#6ec4e8",
            "4": "#be418d",
            "5": "#ff7e2e",
            "6": "#6eca97",
            "7": "#fa9aba",
            "7b": "#6eca97",
            "8": "#e19bdf",
            "9": "#b6bd00",
            "10": "#c9910d",
            "11": "#704b1c",
            "12": "#007852",
            "13": "#6ec4e8",
            "14": "#62259d",
            "A": "#d1302f",
            "B": "#427dbd",
            "C": "#fcd946",
            "D": "#5e9620",
            "E": "#bd76a1"
        };

        if (line && ratpColors[line]) {
            return ratpColors[line];
        }

        if (mode === "bus") return "#2563eb";
        if (mode === "metro") return "#003ca6";
        if (mode === "rer") return "#d1302f";
        if (mode === "tram") return "#ffcd00";
        if (mode === "rail") return "#7c3aed";

        return "#6b7280";
    }

    function clearMap() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        selectedMarker = null;
    }

    function setActiveFilter(type) {
        currentType = type;

        filterButtons.forEach(button => {
            button.classList.toggle("active", button.dataset.type === type);
        });
    }

    function hideResults() {
        resultsBox.innerHTML = "";
        resultsBox.classList.remove("visible");
    }

    function showResults() {
        resultsBox.classList.add("visible");
    }

    function getStopLatLng(stop) {
        if (stop.lat && stop.lon) {
            return [parseFloat(stop.lat), parseFloat(stop.lon)];
        }

        if (stop.stop_lat && stop.stop_lon) {
            const a = parseFloat(stop.stop_lat);
            const b = parseFloat(stop.stop_lon);

            if (!Number.isNaN(a) && !Number.isNaN(b)) {
                if (a > 40 && a < 55 && b > -10 && b < 15) {
                    return [a, b];
                }

                if (b > 40 && b < 55 && a > -10 && a < 15) {
                    return [b, a];
                }
            }
        }

        if (stop.x_epsg2154 && stop.y_epsg2154) {
            const coords = proj4("EPSG:2154", "EPSG:4326", [
                parseFloat(stop.x_epsg2154),
                parseFloat(stop.y_epsg2154)
            ]);

            return [coords[1], coords[0]];
        }

        return null;
    }

    function updateStopInfo(stop) {
        if (!stopInfo) return;

        stopInfo.innerHTML = `
            <div class="stop-info-title">${stop.name ?? "Arrêt sans nom"}</div>
            <div class="stop-info-content">
                <strong>Type :</strong> ${stop.stop_type ?? "inconnu"}<br>
                <strong>Ville :</strong> ${stop.town ?? "inconnue"}<br>
                ${stop.stop_id ? `<strong>ID :</strong> ${stop.stop_id}` : ""}
            </div>
        `;
    }

    function selectMarker(marker, stop) {
        if (selectedMarker) {
            selectedMarker.setStyle({
                radius: 6,
                weight: 2,
                fillOpacity: 0.75
            });
        }

        selectedMarker = marker;

        marker.setStyle({
            radius: 9,
            weight: 3,
            fillOpacity: 1
        });

        updateStopInfo(stop);
    }

    function renderStops(stops, label, color) {
        clearMap();

        if (!stops || stops.length === 0) {
            title.textContent = "Aucun arrêt trouvé";
            map.setView([48.8566, 2.3522], 11);

            if (stopInfo) {
                stopInfo.innerHTML = `
                    <div class="stop-info-title">Aucun arrêt sélectionné</div>
                    <div class="stop-info-content">Aucun résultat à afficher.</div>
                `;
            }
            return;
        }

        title.textContent = label;

        const bounds = [];

        stops.forEach(stop => {
            const latlng = getStopLatLng(stop);
            if (!latlng) return;

            bounds.push(latlng);

            const mode = stop.stop_type ?? "inconnu";
            const line = stop.line ?? stop.route_short_name ?? null;
            const markerColor = getLineColor(mode, line);

            const marker = L.circleMarker(latlng, {
                radius: 6,
                color: markerColor,
                weight: 2,
                fillColor: markerColor,
                fillOpacity: 0.75
            }).addTo(map);

            marker.on("click", () => {
                selectMarker(marker, stop);
            });

            marker.on("mouseover", () => {
                marker.setStyle({ radius: 8 });
            });

            marker.on("mouseout", () => {
                if (marker !== selectedMarker) {
                    marker.setStyle({ radius: 6 });
                }
            });

            markers.push(marker);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30] });
        } else {
            map.setView([48.8566, 2.3522], 11);
        }
    }

    async function loadStopsByType(type) {
        try {
            const response = await fetch(`/api/stops/by-type?type=${type}`);
            const data = await response.json();

            const label = type === "all" ? "Tous les arrêts" : `Type : ${type}`;
            renderStops(data.stops, label, getLineColor(type));
            setActiveFilter(type);
        } catch (error) {
            console.error("Erreur chargement stops par type :", error);
        }
    }

    function renderStopResults(stops) {
        resultsBox.innerHTML = "";

        if (stops.length === 0) {
            resultsBox.innerHTML = '<div class="line-result-empty">Aucun arrêt trouvé</div>';
            showResults();
            return;
        }

        stops.forEach(stop => {
            const item = document.createElement("div");
            item.className = "line-result-item";

            const mode = stop.stop_type ?? "inconnu";
            const line = stop.line ?? stop.route_short_name ?? null;
            const color = getLineColor(mode, line);

            item.innerHTML = `
                <div class="line-result-row">
                    <span class="transport-pill" style="background:${color}">
                        ${line ?? mode.toUpperCase()}
                    </span>
                    <div>
                        <div class="line-result-name">${stop.name}</div>
                        <div class="line-result-mode">
                            ${mode}${stop.town ? " · " + stop.town : ""}
                        </div>
                    </div>
                </div>
            `;

            item.addEventListener("click", () => {
                searchInput.value = stop.name;
                hideResults();

                renderStops([stop], stop.name, color);

                const latlng = getStopLatLng(stop);
                if (latlng) {
                    map.setView(latlng, 15);
                }

                updateStopInfo(stop);
            });

            resultsBox.appendChild(item);
        });

        showResults();
    }

    async function searchStops(query) {
        const value = query.trim();

        if (value.length < 2) {
            hideResults();
            return;
        }

        try {
            const response = await fetch(`/api/stops/search?q=${encodeURIComponent(value)}&type=${encodeURIComponent(currentType)}`);
            const stops = await response.json();
            renderStopResults(stops);
        } catch (error) {
            console.error("Erreur recherche arrêt :", error);
        }
    }

    searchInput.addEventListener("input", () => {
        searchStops(searchInput.value);
    });

    document.addEventListener("click", (event) => {
        const clickedInside =
            event.target === searchInput ||
            resultsBox.contains(event.target);

        if (!clickedInside) {
            hideResults();
        }
    });

    filterButtons.forEach(button => {
        button.addEventListener("click", () => {
            const type = button.dataset.type;
            searchInput.value = "";
            hideResults();
            loadStopsByType(type);
        });
    });

    await loadStopsByType("all");
});