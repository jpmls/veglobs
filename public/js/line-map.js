document.addEventListener("DOMContentLoaded", async () => {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    const map = L.map("map").setView([48.8566, 2.3522], 11);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap"
    }).addTo(map);

    const searchInput = document.getElementById("stopSearch");
    const resultsBox = document.getElementById("stopResults");
    const title = document.getElementById("lineTitle");
    const filterButtons = document.querySelectorAll(".type-filter");

    let markers = [];
    let currentType = "all";

    proj4.defs(
        "EPSG:2154",
        "+proj=lcc +lat_1=49 +lat_2=44 +lat_0=46.5 +lon_0=3 +x_0=700000 +y_0=6600000 +ellps=GRS80 +units=m +no_defs"
    );

    function getLineColor(mode) {
        if (mode === "bus") return "#2563eb";
        if (mode === "metro") return "#dc2626";
        if (mode === "rer") return "#16a34a";
        if (mode === "tram") return "#f59e0b";
        if (mode === "rail") return "#7c3aed";
        return "#6b7280";
    }

    function clearMap() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
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

        if (stop.x_epsg2154 && stop.y_epsg2154) {
            const coords = proj4("EPSG:2154", "EPSG:4326", [
                parseFloat(stop.x_epsg2154),
                parseFloat(stop.y_epsg2154)
            ]);

            return [coords[1], coords[0]];
        }

        return null;
    }

    function renderStops(stops, label, color) {
        clearMap();

        if (!stops || stops.length === 0) {
            title.textContent = "Aucun arrêt trouvé";
            map.setView([48.8566, 2.3522], 11);
            return;
        }

        title.textContent = label;

        const bounds = [];

        stops.forEach(stop => {
            const latlng = getStopLatLng(stop);
            if (!latlng) return;

            bounds.push(latlng);

            const marker = L.circleMarker(latlng, {
                radius: 6,
                color: color,
                weight: 2,
                fillColor: color,
                fillOpacity: 0.75
            })
                .addTo(map)
                .bindPopup(`
                    <strong>${stop.name ?? "Arrêt sans nom"}</strong><br>
                    Type : ${stop.stop_type ?? "inconnu"}<br>
                    Ville : ${stop.town ?? "inconnue"}
                `);

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
            item.innerHTML = `
                <div class="line-result-name">${stop.name}</div>
                <div class="line-result-mode">${stop.stop_type ?? "inconnu"}${stop.town ? " · " + stop.town : ""}</div>
            `;

            item.addEventListener("click", () => {
                searchInput.value = stop.name;
                hideResults();

                const color = getLineColor(stop.stop_type);
                renderStops([stop], stop.name, color);
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