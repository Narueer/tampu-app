// mapas con Leaflet y OpenStreetMap
const CUSCO = [-13.5226, -71.9673];

function crearMapa(id, zoom) {
    const mapa = L.map(id).setView(CUSCO, zoom);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19,
        attribution: "&copy; OpenStreetMap"
    }).addTo(mapa);
    return mapa;
}

// mapa con las habitaciones
const divMapa = document.getElementById("mapa");

if (divMapa) {
    const puntos = JSON.parse(divMapa.dataset.puntos);
    const mapa = crearMapa("mapa", 13);
    const grupo = [];

    // con muchos cuartos el precio va en un globito al pasar el mouse, para que no se amontonen
    const compacto = puntos.filter(p => !p.tipo).length > 12;

    puntos.forEach(function (p) {
        let texto = "<b>" + p.titulo + "</b>";
        if (compacto && !p.tipo) {
            texto += "<br>S/ " + p.precio + " al mes";
            if (p.url) texto += '<br><a href="' + p.url + '">Ver detalle</a>';
            L.circleMarker([p.lat, p.lng], { radius: 8, color: "#fff", weight: 2, fillColor: "#b4532a", fillOpacity: 1 })
                .addTo(mapa).bindPopup(texto).bindTooltip("S/ " + p.precio, { direction: "top", offset: [0, -6] });
            grupo.push([p.lat, p.lng]);
            return;
        }
        let icono;
        if (p.tipo === "uni") {
            // universidad
            icono = L.divIcon({ className: "marca-uni", html: '<i class="bi bi-mortarboard-fill"></i>', iconSize: [34, 34], iconAnchor: [17, 17] });
            texto += "<br>Universidad";
        } else if (p.tipo === "trabajo") {
            icono = L.divIcon({ className: "marca-precio marca-trabajo", html: '<i class="bi bi-briefcase-fill"></i> ' + p.pago, iconSize: null });
            texto += "<br>" + p.empresa + "<br>" + p.pago;
        } else {
            // habitacion: el precio se ve en el mismo marcador
            icono = L.divIcon({ className: "marca-precio", html: "S/ " + p.precio, iconSize: null });
            texto += "<br>S/ " + p.precio + " al mes";
        }
        if (p.url) {
            texto += '<br><a href="' + p.url + '">Ver detalle</a>';
        }
        L.marker([p.lat, p.lng], { icon: icono }).addTo(mapa).bindPopup(texto);
        grupo.push([p.lat, p.lng]);
    });

    if (grupo.length > 1) {
        mapa.fitBounds(grupo, { padding: [30, 30] });
    } else if (grupo.length === 1) {
        mapa.setView(grupo[0], 16);
    }
}

// el arrendador marca la ubicacion con un clic
const divElegir = document.getElementById("mapaElegir");

if (divElegir) {
    const inputLat = document.getElementById("lat");
    const inputLng = document.getElementById("lng");
    const mapa = crearMapa("mapaElegir", 14);
    let marcador = null;

    function poner(lat, lng) {
        if (marcador) {
            marcador.setLatLng([lat, lng]);
        } else {
            marcador = L.marker([lat, lng]).addTo(mapa);
        }
        inputLat.value = lat.toFixed(7);
        inputLng.value = lng.toFixed(7);
    }

    if (inputLat.value) {
        poner(parseFloat(inputLat.value), parseFloat(inputLng.value));
        mapa.setView([inputLat.value, inputLng.value], 16);
    }

    mapa.on("click", function (e) {
        poner(e.latlng.lat, e.latlng.lng);
    });
}
