document.addEventListener("DOMContentLoaded", function() {
    // Initialisation de la carte Leaflet
    const map = L.map('map').setView([48.8566, 2.3522], 12);  // Centré sur Paris

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let selectedDeparture = null;
    let selectedDestination = null;

    // Fonction pour afficher ou cacher les résultats de recherche
    function showResults(box) {
        box.style.display = 'block';
    }

    function hideResults(box) {
        box.style.display = 'none';
    }

    // Recherche d'arrêts en fonction du texte saisi
    async function searchStops(query, box, target) {
        if (query.length < 2) return;

        const res = await fetch(`/api/stops/search?q=${query}`);
        const data = await res.json();

        box.innerHTML = "";
        data.forEach(stop => {
            const div = document.createElement("div");
            div.className = "search-result-item";
            div.innerText = stop.name;

            div.onclick = () => {
                if (target === "start") {
                    selectedDeparture = stop;
                    document.getElementById("startInput").value = stop.name;
                } else {
                    selectedDestination = stop;
                    document.getElementById("endInput").value = stop.name;
                }
                hideResults(box);
                if (selectedDeparture && selectedDestination) chercherTrajet();
            };

            box.appendChild(div);
        });
        showResults(box);
    }

    // Recherche de trajet entre le départ et la destination
    async function chercherTrajet() {
        if (!selectedDeparture || !selectedDestination) return;

        const res = await fetch(`/api/journeys?from=${selectedDeparture.stop_id}&to=${selectedDestination.stop_id}`);
        const data = await res.json();

        if (data.success && data.journeys.length > 0) {
            const journey = data.journeys[0];
            document.getElementById("resultBox").innerHTML = `
                Départ : ${selectedDeparture.name} à ${journey.departure_time}<br>
                Arrivée : ${selectedDestination.name} à ${journey.arrival_time}
            `;
            await afficherStops(journey.trip_id);
        } else {
            document.getElementById("resultBox").innerText = "Aucun trajet trouvé.";
        }
    }

    // Fonction pour afficher les arrêts d'un trajet
    async function afficherStops(tripId) {
        const res = await fetch(`/api/trips/${tripId}/stops`);
        const data = await res.json();

        if (data.success && data.stops) {
            const latLngs = [];
            data.stops.forEach(stop => {
                const latlng = getLatLng(stop);
                if (latlng) {
                    latLngs.push(latlng);
                    const marker = L.circleMarker(latlng, { color: 'blue' }).addTo(map);
                    marker.bindPopup(`<b>${stop.name}</b><br>Arrivée: ${stop.arrival_time || 'N/A'}<br>Départ: ${stop.departure_time || 'N/A'}`);
                }
            });

            if (latLngs.length > 1) {
                const polyline = L.polyline(latLngs, { color: 'blue' }).addTo(map);
                map.fitBounds(polyline.getBounds());
            }
        }
    }

    // Fonction pour convertir les coordonnées EPSG:2154 en WGS84
    function getLatLng(stop) {
        if (stop.lat && stop.lon) {
            return [parseFloat(stop.lat), parseFloat(stop.lon)];
        }

        if (stop.x_epsg2154 && stop.y_epsg2154) {
            const coords = proj4('EPSG:2154', 'EPSG:4326', [parseFloat(stop.x_epsg2154), parseFloat(stop.y_epsg2154)]);
            return [coords[1], coords[0]];  // Inverser: [lat, lon] pour Leaflet
        }

        return null; // Pas de coordonnées disponibles
    }

    // Gestion des événements sur les inputs de recherche
    document.getElementById("startInput").addEventListener("input", (e) => searchStops(e.target.value, document.getElementById("startResults"), "start"));
    document.getElementById("endInput").addEventListener("input", (e) => searchStops(e.target.value, document.getElementById("endResults"), "end"));
});