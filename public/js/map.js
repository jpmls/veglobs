const map = L.map('map').setView([48.8566, 2.3522], 10);

map.setMinZoom(9);
map.setMaxZoom(18);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap & CartoDB'
}).addTo(map);

const startInput = document.getElementById('start-input');
const endInput = document.getElementById('end-input');
const resetBtn = document.getElementById('reset-btn');

let startStop = null;
let endStop = null;
let startMarker = null;
let endMarker = null;
let routeLine = null;

const markers = L.markerClusterGroup({
    showCoverageOnHover: false,
    spiderfyOnMaxZoom: true,
    maxClusterRadius: 40
});

fetch('/api/stops')
    .then(response => response.json())
    .then(stops => {
        if (!Array.isArray(stops)) {
            console.error('Format API invalide :', stops);
            return;
        }

        stops.forEach(stop => {
            if (typeof stop.lat !== 'number' || typeof stop.lng !== 'number') {
                return;
            }

            const coords = [stop.lat, stop.lng];

            const marker = L.circleMarker(coords, {
                radius: 5,
                color: '#2563eb',
                fillColor: '#2563eb',
                fillOpacity: 1,
                weight: 1
            });

            marker.bindPopup(`
                <strong>${escapeHtml(stop.name)}</strong><br>
                <span>Clique pour sélectionner</span>
            `);

            marker.on('click', () => selectStop(stop, coords));

            markers.addLayer(marker);
        });

        map.addLayer(markers);
    })
    .catch(error => {
        console.error('Erreur chargement des arrêts :', error);
    });

function selectStop(stop, coords) {
    if (!startStop) {
        startStop = {
            ...stop,
            lat: coords[0],
            lng: coords[1]
        };

        startInput.value = stop.name;

        if (startMarker) {
            map.removeLayer(startMarker);
        }

        startMarker = L.circleMarker(coords, {
            radius: 8,
            color: '#16a34a',
            fillColor: '#16a34a',
            fillOpacity: 1,
            weight: 2
        }).addTo(map);

        return;
    }

    if (!endStop) {
        endStop = {
            ...stop,
            lat: coords[0],
            lng: coords[1]
        };

        endInput.value = stop.name;

        if (endMarker) {
            map.removeLayer(endMarker);
        }

        endMarker = L.circleMarker(coords, {
            radius: 8,
            color: '#dc2626',
            fillColor: '#dc2626',
            fillOpacity: 1,
            weight: 2
        }).addTo(map);

        loadRoute();

        return;
    }

    resetSelection();

    startStop = {
        ...stop,
        lat: coords[0],
        lng: coords[1]
    };

    startInput.value = stop.name;

    startMarker = L.circleMarker(coords, {
        radius: 8,
        color: '#16a34a',
        fillColor: '#16a34a',
        fillOpacity: 1,
        weight: 2
    }).addTo(map);
}

function loadRoute() {
    if (!startStop || !endStop) {
        return;
    }

    if (!startStop.id || !endStop.id) {
        console.error('Les stations n’ont pas d’id :', startStop, endStop);
        return;
    }

    fetch(`/api/route?from=${encodeURIComponent(startStop.id)}&to=${encodeURIComponent(endStop.id)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Route:', data);

            if (data.error) {
                alert(data.error);
                return;
            }

            if (!Array.isArray(data) || data.length === 0) {
                alert('Aucun trajet trouvé');
                return;
            }

            drawRouteLine(data);
        })
        .catch(error => {
            console.error('Erreur chargement trajet :', error);
        });
}

function drawRouteLine(routeStops) {
    if (routeLine) {
        map.removeLayer(routeLine);
    }

    const latlngs = routeStops
        .filter(stop => typeof stop.lat === 'number' && typeof stop.lng === 'number')
        .map(stop => [stop.lat, stop.lng]);

    if (latlngs.length < 2) {
        console.error('Pas assez de points pour tracer le trajet :', routeStops);
        return;
    }

    routeLine = L.polyline(latlngs, {
        color: '#1d4ed8',
        weight: 4,
        opacity: 0.9
    }).addTo(map);

    map.fitBounds(routeLine.getBounds(), {
        padding: [40, 40]
    });
}

function resetSelection() {
    startStop = null;
    endStop = null;

    startInput.value = '';
    endInput.value = '';

    if (startMarker) {
        map.removeLayer(startMarker);
        startMarker = null;
    }

    if (endMarker) {
        map.removeLayer(endMarker);
        endMarker = null;
    }

    if (routeLine) {
        map.removeLayer(routeLine);
        routeLine = null;
    }
}

if (resetBtn) {
    resetBtn.addEventListener('click', resetSelection);
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}