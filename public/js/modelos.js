// maquetas 3D (model-viewer): el script pesado solo se carga cuando la maqueta está por aparecer en pantalla
(function () {
    const maquetas = document.querySelectorAll("model-viewer");
    if (maquetas.length) {
        let cargado = false;
        const cargar = () => {
            if (cargado) return;
            cargado = true;
            const s = document.createElement("script");
            s.type = "module";
            s.src = "https://cdn.jsdelivr.net/npm/@google/model-viewer@4.0.0/dist/model-viewer.min.js";
            document.head.appendChild(s);
        };
        if ("IntersectionObserver" in window) {
            const obs = new IntersectionObserver((entradas) => {
                if (entradas.some((e) => e.isIntersecting)) { cargar(); obs.disconnect(); }
            }, { rootMargin: "400px" });
            maquetas.forEach((m) => obs.observe(m));
        } else {
            cargar();
        }

        // tocar (o pasar el mouse por) un número muestra su explicación abajo; la maqueta deja de girar mientras lees
        maquetas.forEach((mv) => {
            const info = mv.closest(".modelo-3d")?.querySelector(".modelo-info");
            const mostrar = (punto) => {
                mv.querySelectorAll(".punto-3d.abierto").forEach((p) => { if (p !== punto) p.classList.remove("abierto"); });
                if (!info) return;
                if (punto && punto.classList.contains("abierto")) {
                    info.innerHTML = '<span class="modelo-info-num">' + punto.querySelector(".punto-3d-num").textContent + "</span><div>" + punto.querySelector(".punto-3d-globo").innerHTML + "</div>";
                    info.hidden = false;
                    mv.autoRotate = false;
                } else {
                    info.hidden = true;
                    mv.autoRotate = true;
                }
            };
            mv.addEventListener("click", (e) => {
                const punto = e.target.closest(".punto-3d");
                if (punto) punto.classList.toggle("abierto");
                mostrar(punto);
            });
            mv.querySelectorAll(".punto-3d").forEach((p) => {
                p.addEventListener("mouseenter", () => { if (!mv.querySelector(".punto-3d.abierto")) { p.classList.add("abierto"); mostrar(p); p.dataset.porMouse = "1"; } });
                p.addEventListener("mouseleave", () => { if (p.dataset.porMouse) { delete p.dataset.porMouse; p.classList.remove("abierto"); mostrar(null); } });
            });
            mv.addEventListener("load", () => mv.closest(".modelo-3d")?.classList.add("listo"));
        });
    }

    // ilustraciones que se inclinan siguiendo el mouse (solo en computadoras y sin "reducir movimiento")
    const reducir = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (!reducir && window.matchMedia("(hover: hover)").matches) {
        document.querySelectorAll(".inclinable").forEach((el) => {
            const img = el.querySelector("img") || el;
            el.addEventListener("mousemove", (e) => {
                const r = el.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                img.style.transform = `perspective(700px) rotateY(${x * 10}deg) rotateX(${-y * 8}deg) translateY(-4px) scale(1.03)`;
            });
            el.addEventListener("mouseleave", () => { img.style.transform = ""; });
        });
    }
})();
