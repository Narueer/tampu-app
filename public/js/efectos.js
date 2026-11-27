// Efectos de TAMPU: los títulos de sección suben palabra por palabra y el pallay se "teje" debajo
// cuando la sección entra en pantalla. Con "reducir movimiento" todo aparece quieto.
(function () {
    const reducir = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const titulos = document.querySelectorAll(".encabezado-seccion h2, h2.titulo-grande");
    if (!titulos.length || !("IntersectionObserver" in window)) return;

    titulos.forEach((h) => {
        if (reducir) { h.classList.add("tejido-listo"); return; }
        let i = 0;
        const recorrer = (nodo) => {
            [...nodo.childNodes].forEach((n) => {
                if (n.nodeType === 3) {
                    const frag = document.createDocumentFragment();
                    n.nodeValue.split(/(\s+)/).forEach((trozo) => {
                        if (!trozo) return;
                        if (/^\s+$/.test(trozo)) { frag.appendChild(document.createTextNode(trozo)); return; }
                        const p = document.createElement("span"); p.className = "palabra";
                        const s = document.createElement("span"); s.textContent = trozo; s.style.setProperty("--i", i++);
                        p.appendChild(s); frag.appendChild(p);
                    });
                    n.replaceWith(frag);
                } else if (n.nodeType === 1 && n.tagName !== "BR") {
                    recorrer(n);
                }
            });
        };
        recorrer(h);
        h.classList.add("subir-palabras");
    });

    const obs = new IntersectionObserver((entradas) => {
        entradas.forEach((e) => {
            if (!e.isIntersecting) return;
            e.target.classList.add("listo", "tejido-listo");
            obs.unobserve(e.target);
        });
    }, { threshold: 0.3, rootMargin: "0px 0px -8% 0px" });
    titulos.forEach((h) => obs.observe(h));
})();
