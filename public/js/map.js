// =====================
// PROJECTIONS
// =====================

proj4.defs(
    "EPSG:2154",
    "+proj=lcc +lat_1=49 +lat_2=44 +lat_0=46.5 " +
    "+lon_0=3 +x_0=700000 +y_0=6600000 " +
    "+ellps=GRS80 +units=m +no_defs"
);

const WGS84 = "EPSG:4326";

// =====================
// INIT MAP
// =====================

const map = L.map('map').setView([48.8566, 2.3522], 12);

L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap & CartoDB'
}).addTo(map);

// =====================
// VARIABLES
// =====================

let stops = [];
let startPoint = null;
let endPoint = null;

let startMarker = null;
let endMarker = null;

// =====================
// CUSTOM MARKER
// =====================

function getMarkerIcon(color) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:16px;
            height:16px;
            background:${color};
            border-radius:50%;
            border:2px solid white;
            box-shadow:0 0 6px rgba(0,0,0,0.3);
        "></div>`
    });
}

// =====================
// CONVERSION GPS (CORRECTE)
// =====================

function convertToLatLng(x, y) {
    const [lng, lat] = proj4("EPSG:2154", WGS84, [x, y]);
    return [lat, lng];
}

// =====================
// FETCH STOPS
// =====================

async function loadStops() {
    try {
        const response = await fetch('/api/stops');
        const data = await response.json();

        stops = data;
        displayStops();
    } catch (error) {
        console.error(error);
    }
}

function drawLine() {

    if (!startPoint || !endPoint) return;

    const startCoords = convertToLatLng(
        startPoint.x_epsg2154,
        startPoint.y_epsg2154
    );

    const endCoords = convertToLatLng(
        endPoint.x_epsg2154,
        endPoint.y_epsg2154
    );

    // Supprimer ancienne ligne
    if (routeLine) {
        map.removeLayer(routeLine);
    }

    routeLine = L.polyline([startCoords, endCoords], {
        color: '#007bff',
        weight: 5,
        opacity: 0.8
    }).addTo(map);

    // zoom automatique
    map.fitBounds(routeLine.getBounds());
}

// =====================
// DISPLAY STOPS
// =====================

function displayStops() {
    stops.forEach(stop => {

        const coords = convertToLatLng(stop.x_epsg2154, stop.y_epsg2154);

        const marker = L.marker(coords, {
            icon: getMarkerIcon('#666')
        }).addTo(map);

        marker.bindPopup(stop.name);

        marker.on('click', () => handleStopClick(stop, coords));
    });
}

// =====================
// CLICK HANDLER
// =====================

function handleStopClick(stop, coords) {

    if (startPoint && endPoint) {
        resetSelection();
    }

    if (!startPoint) {
        startPoint = stop;
        document.getElementById('start-input').value = stop.name;

        startMarker = L.marker(coords, {
            icon: getMarkerIcon('green')
        }).addTo(map);

    } else if (!endPoint) {
        endPoint = stop;
        document.getElementById('end-input').value = stop.name;

        endMarker = L.marker(coords, {
            icon: getMarkerIcon('red')
        }).addTo(map);

        drawLine(); // 👈 ici
    }

    console.log("Départ:", startPoint);
    console.log("Arrivée:", endPoint);
}
// =====================
// RESET
// =====================

function resetSelection() {
    startPoint = null;
    endPoint = null;

    document.getElementById('start-input').value = '';
    document.getElementById('end-input').value = '';

    if (startMarker) map.removeLayer(startMarker);
    if (endMarker) map.removeLayer(endMarker);
    if (routeLine) map.removeLayer(routeLine);
}

// =====================
// INIT
// =====================

loadStops();