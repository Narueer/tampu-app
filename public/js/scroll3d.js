// "Arma tu cuarto": el scroll mueve la línea de tiempo de la animación de Blender (model-viewer)
// y la cámara; los textos cambian por etapas. Con "reducir movimiento" se muestra el cuarto ya armado.
(function () {
    const sec = document.getElementById("armarCuarto");
    if (!sec) return;
    const mv = sec.querySelector("model-viewer");
    const pasos = [...sec.querySelectorAll(".armar-paso")];
    const barras = [...sec.querySelectorAll(".armar-progreso button")];
    const numero = sec.querySelector(".armar-numero");
    const escena = sec.querySelector(".armar-escena");
    const reducir = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    // dónde empieza cada etapa (0 a 1 del recorrido) y hasta qué segundo de la animación llega
    const CORTES = [0, 0.16, 0.38, 0.6, 0.84];
    const ANIM_HASTA = 0.86;  // a partir de aquí el cuarto ya está completo
    let duracion = 0, objetivo = 0, actual = 0, raton = { x: 0, y: 0 }, listo = false, ultimoPaso = -1;

    const limitar = (n, a, b) => Math.min(b, Math.max(a, n));
    const suave = (t) => t * t * (3 - 2 * t);

    function progreso() {
        const r = sec.getBoundingClientRect();
        const recorrido = sec.offsetHeight - window.innerHeight;
        return recorrido > 0 ? limitar(-r.top / recorrido, 0, 1) : 1;
    }

    function pintar() {
        // el valor se acerca poco a poco al del scroll: el movimiento queda suave aunque la rueda vaya a saltos
        actual += (objetivo - actual) * 0.12;
        if (Math.abs(objetivo - actual) < 0.0005) actual = objetivo;
        const p = actual;

        if (listo && duracion) {
            // justo antes del final: en el último cuadro exacto la animación vuelve al inicio
            mv.currentTime = suave(limitar(p / ANIM_HASTA, 0, 1)) * (duracion - 0.02);
            const giro = -10 + p * 62 + raton.x * 8;           // de frente a vista de tres cuartos
            const alto = 74 - p * 14 + raton.y * 4;
            // la cámara empieza mirando la mochila de cerca y se aleja mientras el cuarto se arma
            const k = suave(limitar(p / 0.32, 0, 1));
            const dist = 58 + 70 * k - p * 14;
            mv.cameraTarget = `${(1.2 * (1 - k)).toFixed(3)}m ${(0.3 + 0.7 * k).toFixed(3)}m ${(0.55 * (1 - k)).toFixed(3)}m`;
            mv.cameraOrbit = `${giro}deg ${alto}deg ${dist}%`;
        }
        escena.style.setProperty("--luz-x", 62 + raton.x * 10 + "%");
        escena.style.setProperty("--luz-y", 45 + raton.y * 10 + "%");
        sec.style.setProperty("--sombra", (0.15 + 0.85 * limitar(p / ANIM_HASTA, 0, 1)).toFixed(3));

        let paso = 0;
        CORTES.forEach((c, i) => { if (p >= c - 0.001) paso = i; });
        if (paso !== ultimoPaso) {
            pasos.forEach((el, i) => el.classList.toggle("activo", i === paso));
            numero.textContent = String(paso + 1).padStart(2, "0");
            ultimoPaso = paso;
        }
        barras.forEach((b, i) => {
            const ini = CORTES[i], fin = CORTES[i + 1] ?? 1;
            b.style.setProperty("--lleno", limitar((p - ini) / (fin - ini), 0, 1).toFixed(3));
            b.setAttribute("aria-current", i === paso ? "step" : "false");
        });
        sec.classList.toggle("fin", p > 0.95);
        if (actual !== objetivo) requestAnimationFrame(pintar); else pidiendo = false;
    }
    let pidiendo = false;
    const pedir = () => { if (!pidiendo) { pidiendo = true; requestAnimationFrame(pintar); } };

    mv.addEventListener("load", () => {
        duracion = mv.duration || 0;
        // la animación no corre sola: se pausa y el scroll elige el segundo exacto
        mv.play(); mv.pause();
        listo = true;
        if (reducir) { mv.currentTime = duracion - 0.02; mv.cameraTarget = "0m 1m 0m"; mv.cameraOrbit = "40deg 62deg 118%"; return; }
        pedir();
    });

    if (reducir) {
        pasos.forEach((el) => el.classList.add("activo"));
        return;
    }
    window.addEventListener("scroll", () => { objetivo = progreso(); pedir(); }, { passive: true });
    window.addEventListener("resize", () => { objetivo = progreso(); pedir(); });
    if (window.matchMedia("(hover: hover)").matches) {
        escena.addEventListener("mousemove", (e) => {
            raton.x = e.clientX / window.innerWidth - 0.5;
            raton.y = e.clientY / window.innerHeight - 0.5;
            pedir();
        });
    }
    // las barritas llevan a cada etapa
    barras.forEach((b, i) => b.addEventListener("click", () => {
        const recorrido = sec.offsetHeight - window.innerHeight;
        window.scrollTo({ top: sec.offsetTop + recorrido * (CORTES[i] + 0.02), behavior: "smooth" });
    }));
    objetivo = actual = progreso();
    pedir();
})();
