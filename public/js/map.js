document.addEventListener("DOMContentLoaded", async () => {
    const mapElement = document.getElementById("map");
    if (!mapElement) return;

    const map = L.map("map").setView([48.8566, 2.3522], 11);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap"
    }).addTo(map);

    const select = document.getElementById("lineSelect");
    const title = document.getElementById("lineTitle");

    let markers = [];

    proj4.defs(
        "EPSG:2154",
        "+proj=lcc +lat_1=49 +lat_2=44 +lat_0=46.5 +lon_0=3 +x_0=700000 +y_0=6600000 +ellps=GRS80 +units=m +no_defs"
    );

    function getLineColor(mode) {
        if (mode === "bus") return "#2563eb";
        if (mode === "metro") return "#dc2626";
        if (mode === "rer") return "#16a34a";
        return "#6b7280";
    }

    

    function clearMap() {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
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

    async function loadLines() {
        try {
            const response = await fetch("/api/lines");
            const lines = await response.json();

            select.innerHTML = '<option value="">-- Choisir une ligne --</option>';

            lines.forEach(line => {
                const option = document.createElement("option");
                option.value = line.id;
                option.textContent = `${line.name} (${line.transport_mode})`;
                select.appendChild(option);
            });
        } catch (error) {
            console.error("Erreur chargement lignes :", error);
        }
    }

    async function loadLine(lineId) {
        try {
            const response = await fetch(`/api/lines/${lineId}`);
            const data = await response.json();

            clearMap();

            if (!data || !data.stops || data.stops.length === 0) {
                title.textContent = "Aucun arrêt trouvé";
                map.setView([48.8566, 2.3522], 11);
                return;
            }

            title.textContent = `${data.name} (${data.transport_mode})`;

            const bounds = [];
            const color = getLineColor(data.transport_mode);

            data.stops.forEach(stop => {
                const latlng = getStopLatLng(stop);
                if (!latlng) return;

                bounds.push(latlng);

                const marker = L.circleMarker(latlng, {
                    radius: 5,
                    color: color,
                    weight: 2,
                    fillColor: color,
                    fillOpacity: 0.7
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
        } catch (error) {
            console.error("Erreur chargement ligne :", error);
        }
    }

    select.addEventListener("change", () => {
        const lineId = select.value;

        if (!lineId) {
            clearMap();
            title.textContent = "";
            map.setView([48.8566, 2.3522], 11);
            return;
        }

        loadLine(lineId);
    });

    loadLines();
});