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
    const compacto = puntos.filter(p => p.tipo !== "uni").length > 12;

    puntos.forEach(function (p) {
        let texto = "<b>" + p.titulo + "</b>";
        if (compacto && !p.tipo) {
            // muchos cuartos: punto pequeño con el ícono de su tipo; el precio aparece al pasar el mouse
            if (p.grupo) texto += "<br>" + p.grupo;
            texto += "<br>S/ " + p.precio + " al mes";
            if (p.url) texto += '<br><a href="' + p.url + '">Ver detalle</a>';
            const punto = L.divIcon({ className: "marca-punto tipo-" + (p.clase || "cuarto"), html: '<i class="bi bi-' + (p.icono || "house-fill") + '"></i>', iconSize: [22, 22], iconAnchor: [11, 11] });
            L.marker([p.lat, p.lng], { icon: punto }).addTo(mapa).bindPopup(texto)
                .bindTooltip("S/ " + p.precio, { direction: "top", offset: [0, -10] });
            grupo.push([p.lat, p.lng]);
            return;
        }
        if (compacto && p.tipo === "trabajo") {
            // muchas ofertas: punto con maletín; el pago aparece al pasar el mouse
            texto += "<br>" + p.empresa + "<br>" + p.pago;
            if (p.url) texto += '<br><a href="' + p.url + '">Ver detalle</a>';
            const punto = L.divIcon({ className: "marca-punto marca-punto-trabajo", html: '<i class="bi bi-briefcase-fill"></i>', iconSize: [22, 22], iconAnchor: [11, 11] });
            L.marker([p.lat, p.lng], { icon: punto }).addTo(mapa).bindPopup(texto).bindTooltip(p.pago, { direction: "top", offset: [0, -10] });
            grupo.push([p.lat, p.lng]);
            return;
        }
        let icono;
        if (p.tipo === "uni") {
            // universidad o instituto: ícono y color según su tipo; la foto de la sede va en el globito
            icono = L.divIcon({ className: "marca-uni marca-" + (p.clase || "universidad"), html: '<i class="bi bi-' + (p.icono || "mortarboard-fill") + '"></i>', iconSize: [34, 34], iconAnchor: [17, 17] });
            if (p.foto) texto = '<img src="' + p.foto + '" alt="" class="foto-globito">' + texto;
            texto += "<br>" + (p.grupo || "Universidad");
        } else if (p.tipo === "trabajo") {
            icono = L.divIcon({ className: "marca-precio marca-trabajo", html: '<i class="bi bi-briefcase-fill"></i> ' + p.pago, iconSize: null });
            texto += "<br>" + p.empresa + "<br>" + p.pago;
        } else {
            // habitacion: ícono de su tipo y el precio en el mismo marcador
            icono = L.divIcon({ className: "marca-precio tipo-" + (p.clase || "cuarto"), html: (p.icono ? '<i class="bi bi-' + p.icono + '"></i> ' : "") + "S/ " + p.precio, iconSize: null });
            if (p.grupo) texto += "<br>" + p.grupo;
            texto += "<br>S/ " + p.precio + " al mes";
        }
        if (p.url) {
            texto += '<br><a href="' + p.url + '">Ver detalle</a>';
        }
        L.marker([p.lat, p.lng], { icon: icono }).addTo(mapa).bindPopup(texto);
        grupo.push([p.lat, p.lng]);
    });

    agregarLeyenda(mapa, puntos, compacto);

    if (grupo.length > 1) {
        mapa.fitBounds(grupo, { padding: [30, 30] });
    } else if (grupo.length === 1) {
        mapa.setView(grupo[0], 16);
    }
}

// leyenda: se arma con los tipos de marcador que de verdad hay en el mapa,
// así un tipo nuevo de lugar aparece solo, sin tocar este archivo
function agregarLeyenda(mapa, puntos, compacto) {
    const filas = [];
    const vistos = new Set();
    function fila(clave, muestra, texto) {
        if (vistos.has(clave)) return;
        vistos.add(clave);
        filas.push('<li>' + muestra + '<span>' + texto + '</span></li>');
    }
    puntos.forEach(function (p) {
        if (p.tipo === "trabajo") {
            fila("trabajo", '<b class="ley-trabajo"><i class="bi bi-briefcase-fill"></i></b>', compacto ? "Trabajo (pasa el mouse para ver el pago)" : "Trabajo");
        } else if (p.tipo === "uni") {
            fila("uni-" + (p.clase || "universidad"), '<b class="ley-uni marca-' + (p.clase || "universidad") + '"><i class="bi bi-' + (p.icono || "mortarboard-fill") + '"></i></b>', p.grupo || "Universidad");
        } else {
            // un renglón por tipo de cuarto (individual, compartido, minidepa, roomies, casa…)
            fila("cuarto-" + (p.clase || "cuarto"), '<b class="ley-cuarto tipo-' + (p.clase || "cuarto") + '"><i class="bi bi-' + (p.icono || "house-fill") + '"></i></b>', p.grupo || "Cuarto");
        }
    });
    if (filas.length < 2 && !compacto) return;   // con un solo tipo y precios visibles no hace falta
    // los cuartos primero, después trabajos y lugares de estudio
    filas.unshift('<li class="ley-nota">' + (compacto ? "Pasa el mouse o toca un punto para ver el precio" : "Los cuartos muestran su precio al mes") + "</li>");

    const Leyenda = L.Control.extend({
        options: { position: "bottomleft" },
        onAdd: function () {
            const div = L.DomUtil.create("div", "leyenda-mapa");
            div.innerHTML = '<button type="button" class="leyenda-boton" aria-expanded="true"><i class="bi bi-info-circle"></i> Leyenda</button><ul>' + filas.join("") + "</ul>";
            L.DomEvent.disableClickPropagation(div);
            L.DomEvent.disableScrollPropagation(div);
            const boton = div.querySelector(".leyenda-boton");
            if (window.innerWidth < 768 || mapa.getContainer().offsetHeight < 320) {   // celular o mapa chico: cerrada
                div.classList.add("cerrada");
                boton.setAttribute("aria-expanded", "false");
            }
            boton.addEventListener("click", function () {
                const cerrada = div.classList.toggle("cerrada");
                boton.setAttribute("aria-expanded", cerrada ? "false" : "true");
            });
            return div;
        }
    });
    new Leyenda().addTo(mapa);
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
