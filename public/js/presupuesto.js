// "Mi presupuesto": la cuenta, dónde se guarda y los avisos "entra en tu presupuesto" en las tarjetas de cuartos.
// Con sesión se guarda en la cuenta (window.TAMPU_PRESU lo trae el servidor); sin sesión, en este navegador.
(function () {
    const CLAVE = "tampu.presupuesto";
    const SEMANAS = 4.3;

    // cuánto es cada gasto al mes según cada cuánto se paga
    const FRECUENCIAS = {
        dia: { texto: "al día", mes: () => 30 },
        clase: { texto: "por día de clase", mes: (p) => (p.dias || 5) * SEMANAS },
        semana: { texto: "a la semana", mes: () => SEMANAS },
        mes: { texto: "al mes", mes: () => 1 },
    };

    function porDefecto() {
        return {
            ingreso: 1000, periodo: "mes", cuarto: null, ahorro: 30, dias: 5,
            items: [
                { nombre: "Almuerzo (menú)", monto: 8, cada: "dia", icono: "egg-fried" },
                { nombre: "Desayuno y cena", monto: 7, cada: "dia", icono: "cup-hot" },
                { nombre: "Pasajes (ida y vuelta)", monto: 2, cada: "clase", icono: "bus-front" },
                { nombre: "Luz, agua e internet", monto: 45, cada: "mes", icono: "lightning-charge" },
                { nombre: "Celular", monto: 30, cada: "mes", icono: "phone" },
                { nombre: "Fotocopias y útiles", monto: 25, cada: "mes", icono: "journal-text" },
                { nombre: "Salidas y gustitos", monto: 40, cada: "mes", icono: "emoji-smile" },
            ],
        };
    }

    function leer() {
        if (window.TAMPU_PRESU && Array.isArray(window.TAMPU_PRESU.items)) return window.TAMPU_PRESU;
        try {
            const d = JSON.parse(localStorage.getItem(CLAVE) || "null");
            if (d && Array.isArray(d.items)) return d;
        } catch (e) { /* sin almacenamiento: no pasa nada */ }
        return null;
    }

    function guardarLocal(p) {
        try { localStorage.setItem(CLAVE, JSON.stringify(p)); } catch (e) { /* modo privado */ }
    }

    // números del mes: gastos (sin cuarto), tope para el cuarto y lo que queda
    function calcular(p) {
        const ingresoMes = (+p.ingreso || 0) * (p.periodo === "semana" ? SEMANAS : 1);
        const filas = p.items.map((it) => {
            const f = FRECUENCIAS[it.cada] || FRECUENCIAS.mes;
            const mes = (+it.monto || 0) * f.mes(p);
            return { ...it, mes, semana: mes / SEMANAS };
        });
        const gastos = filas.reduce((s, f) => s + f.mes, 0);
        const ahorro = +p.ahorro || 0;
        const tope = Math.max(0, Math.floor((ingresoMes - gastos - ahorro) / 10) * 10);
        const cuarto = p.cuarto ? +p.cuarto : null;
        const queda = ingresoMes - gastos - ahorro - (cuarto ?? 0);
        return { ingresoMes, filas, gastos, ahorro, tope, cuarto, queda };
    }

    const soles = (n) => "S/ " + Math.round(n).toLocaleString("es-PE");

    // avisos en las tarjetas y en el detalle de un cuarto
    function marcarCuartos() {
        const p = leer();
        if (!p) return;
        const { tope } = calcular(p);
        if (!tope) return;
        document.querySelectorAll("[data-precio-cuarto]").forEach((el) => {
            if (el.querySelector(".etiqueta-presu")) return;
            const precio = +el.dataset.precioCuarto;
            const entra = precio <= tope;
            const chip = document.createElement("span");
            chip.className = "etiqueta etiqueta-presu " + (entra ? "entra" : "pasa");
            chip.title = "Según tu presupuesto puedes pagar hasta " + soles(tope) + " de cuarto";
            chip.innerHTML = entra
                ? '<i class="bi bi-wallet2"></i> Entra en tu presupuesto'
                : '<i class="bi bi-wallet2"></i> Te pasas por ' + soles(precio - tope);
            (el.querySelector("[data-presu-aqui]") || el).prepend(chip);
        });
        // aviso arriba de la lista de cuartos
        const aviso = document.getElementById("presuAviso");
        if (aviso) {
            const url = new URL(location.href);
            const activo = url.searchParams.get("precio_max") === String(tope);
            url.searchParams.set("precio_max", tope);
            url.searchParams.delete("page");
            aviso.innerHTML = '<i class="bi bi-wallet2"></i><span>Según <a href="' + aviso.dataset.calc + '">tu presupuesto</a> puedes pagar hasta <b>' + soles(tope) + "</b> de cuarto.</span>" +
                (activo ? '<span class="presu-activo"><i class="bi bi-check2"></i> Mostrando solo esos</span>' : '<a class="btn btn-borde btn-sm" href="' + url + '">Ver solo los que entran</a>');
            aviso.hidden = false;
        }
    }

    window.TampuPresu = { CLAVE, FRECUENCIAS, SEMANAS, porDefecto, leer, guardarLocal, calcular, soles, marcarCuartos };
    if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", marcarCuartos);
    else marcarCuartos();
})();
