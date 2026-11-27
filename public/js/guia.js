// Guía con Tampu: resalta cada parte de la página, la explica y (si el usuario quiere) la lee en voz alta.
// También maneja el botón de música andina. Todo es opcional y se recuerda en el navegador.
(function () {
    const guardar = (k, v) => { try { localStorage.setItem(k, v); } catch (e) {} };
    const leer = (k) => { try { return localStorage.getItem(k); } catch (e) { return null; } };

    /* ---------------- música ---------------- */
    const btnMusica = document.getElementById("btnMusica");
    let audio = null;
    function musica(prender) {
        if (!btnMusica) return;
        if (prender) {
            if (!audio) {
                audio = new Audio(btnMusica.dataset.src);
                audio.loop = true;
                audio.volume = 0;
            }
            // retoma donde se quedó (en otra página) para que no empiece siempre igual
            const pos = parseFloat(leer("musica.pos") || "0");
            if (pos && !audio.currentTime) audio.currentTime = pos;
            audio.play().then(() => {
                // sube el volumen de a poco
                let v = 0;
                const subir = setInterval(() => { v = Math.min(0.35, v + 0.02); audio.volume = v; if (v >= 0.35) clearInterval(subir); }, 80);
            }).catch(() => {});
        } else if (audio) {
            audio.pause();
        }
        btnMusica.classList.toggle("sonando", prender);
        btnMusica.setAttribute("aria-pressed", prender ? "true" : "false");
        guardar("musica", prender ? "1" : "0");
    }
    if (btnMusica) {
        btnMusica.addEventListener("click", () => musica(!btnMusica.classList.contains("sonando")));
        // los navegadores no dejan sonar música sin que la persona toque algo: si la tenía prendida,
        // vuelve a sonar con su primer clic en la página
        if (leer("musica") === "1") {
            btnMusica.classList.add("pendiente");
            const alPrimerToque = (e) => {
                if (e.target.closest("#btnMusica")) return;
                btnMusica.classList.remove("pendiente");
                musica(true);
            };
            document.addEventListener("pointerdown", alPrimerToque, { once: true });
        }
        window.addEventListener("pagehide", () => { if (audio) guardar("musica.pos", String(audio.currentTime % (audio.duration || 1e9))); });
    }

    /* ---------------- guía ---------------- */
    const datosEl = document.getElementById("datosGuia");
    if (!datosEl) return;
    const datos = JSON.parse(datosEl.textContent);
    const btnGuia = document.getElementById("btnGuia");
    const reducir = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // voz: se elige la voz más natural que tenga el dispositivo. Las voces "neuronales" (Edge: Camila de Perú,
    // Chrome: Google español, Android: voces de Google) suenan casi humanas; las de escritorio antiguas, a robot.
    const puedeHablar = "speechSynthesis" in window;
    let voz = null;
    function puntaje(v) {
        if (!/^es/i.test(v.lang)) return -1;
        let p = 0;
        if (/natural|neural|online/i.test(v.name)) p += 50;            // voces neuronales (Edge, Windows 11)
        if (/google/i.test(v.name)) p += 35;                            // Chrome y Android
        if (/camila|alex|dalia|jorge|elvira|paloma|ximena|salome|lucia|monica|paulina/i.test(v.name)) p += 10;
        if (/es[-_]PE/i.test(v.lang)) p += 12; else if (/es[-_](419|MX|US|CO)/i.test(v.lang)) p += 8; else if (/es[-_](AR|CL)/i.test(v.lang)) p += 5;
        if (/sabina|helena|pablo|raul|laura|desktop|espeak/i.test(v.name)) p -= 30;   // voces robóticas
        if (v.localService === false) p += 5;                            // las de la nube suelen ser mejores
        return p;
    }
    function elegirVoz() {
        const voces = speechSynthesis.getVoices().filter((v) => puntaje(v) >= 0).sort((a, b) => puntaje(b) - puntaje(a));
        const guardada = leer("guia.vozNombre");
        voz = voces.find((v) => v.name === guardada) || voces[0] || null;
    }
    if (puedeHablar) { elegirVoz(); speechSynthesis.onvoiceschanged = elegirVoz; }
    let conVoz = leer("guia.voz") !== "0";
    // se lee frase por frase, con una pausa corta entre ellas: suena menos a robot que un texto largo de corrido
    function hablar(texto) {
        if (!puedeHablar) return;
        speechSynthesis.cancel();
        if (!conVoz) return;
        const natural = voz && /natural|neural|online|google/i.test(voz.name);
        const frases = texto.replace(/\s+/g, " ").match(/[^.!?¿¡:]+[.!?:]?/g) || [texto];
        frases.forEach((f, i) => {
            const t = f.trim(); if (!t) return;
            const u = new SpeechSynthesisUtterance(t);
            u.lang = voz ? voz.lang : "es-PE";
            if (voz) u.voice = voz;
            u.rate = natural ? 1.0 : 0.95;
            u.pitch = 1.0;
            if (i === 0) u.onstart = () => tarjeta?.classList.add("hablando");
            if (i === frases.length - 1) u.onend = () => tarjeta?.classList.remove("hablando");
            speechSynthesis.speak(u);
        });
    }
    window.TampuVoz = { voces: () => puedeHablar ? speechSynthesis.getVoices().filter((v) => puntaje(v) >= 0).sort((a, b) => puntaje(b) - puntaje(a)) : [], actual: () => voz,
        usar: (nombre) => { guardar("guia.vozNombre", nombre); elegirVoz(); } };

    let capa = null, foco = null, tarjeta = null, indice = 0, pasos = [];
    const visible = (el) => el && el.getClientRects().length && getComputedStyle(el).visibility !== "hidden";

    function armar() {
        capa = document.createElement("div");
        capa.className = "guia-capa";
        foco = document.createElement("div");
        foco.className = "guia-foco";
        tarjeta = document.createElement("div");
        tarjeta.className = "guia-tarjeta";
        tarjeta.setAttribute("role", "dialog");
        tarjeta.setAttribute("aria-live", "polite");
        tarjeta.innerHTML =
            '<div class="guia-cabeza"><img src="' + datos.avatar + '" alt="" class="guia-avatar"><span class="guia-onda"><i></i><i></i><i></i></span>' +
            '<span class="guia-paso"></span><button type="button" class="guia-cambiar-voz" aria-label="Probar otra voz" title="Probar otra voz"><i class="bi bi-person-video2"></i></button><button type="button" class="guia-voz" aria-label="Voz"></button><button type="button" class="guia-cerrar" aria-label="Cerrar la guía">&times;</button></div>' +
            '<h3 class="guia-titulo"></h3><p class="guia-texto"></p>' +
            '<div class="guia-pie"><span class="guia-puntos"></span><button type="button" class="btn btn-borde btn-chico guia-atras">Atrás</button><button type="button" class="btn btn-chico guia-sig">Siguiente</button></div>';
        document.body.append(capa, foco, tarjeta);
        capa.addEventListener("click", cerrar);
        tarjeta.querySelector(".guia-cerrar").addEventListener("click", cerrar);
        tarjeta.querySelector(".guia-atras").addEventListener("click", () => ir(indice - 1));
        tarjeta.querySelector(".guia-sig").addEventListener("click", () => (indice >= pasos.length - 1 ? cerrar() : ir(indice + 1)));
        const bVoz = tarjeta.querySelector(".guia-voz");
        const pintarVoz = () => { bVoz.innerHTML = conVoz ? '<i class="bi bi-volume-up"></i>' : '<i class="bi bi-volume-mute"></i>'; bVoz.title = conVoz ? "Silenciar la voz" : "Activar la voz"; };
        pintarVoz();
        if (!puedeHablar) bVoz.hidden = true;
        // cambiar de voz: pasa a la siguiente voz en español del dispositivo y la prueba
        const bCambiar = tarjeta.querySelector(".guia-cambiar-voz");
        const listaVoces = () => window.TampuVoz.voces();
        if (!puedeHablar || listaVoces().length < 2) bCambiar.hidden = true;
        bCambiar.addEventListener("click", () => {
            const vs = listaVoces(); if (!vs.length) return;
            const sig = vs[(vs.findIndex((v) => v === voz) + 1) % vs.length];
            window.TampuVoz.usar(sig.name);
            conVoz = true; guardar("guia.voz", "1"); pintarVoz();
            bCambiar.title = "Voz: " + sig.name.replace(/Microsoft |Online |\(Natural\)|- Spanish.*$/g, "").trim() + " · toca para probar otra";
            hablar("¡Hola! Así sueno yo. " + pasos[indice].titulo + ".");
        });
        bVoz.addEventListener("click", () => { conVoz = !conVoz; guardar("guia.voz", conVoz ? "1" : "0"); pintarVoz(); conVoz ? hablar(pasos[indice].texto) : speechSynthesis.cancel(); });
        document.addEventListener("keydown", teclas);
        window.addEventListener("resize", ubicar);
        window.addEventListener("scroll", ubicar, { passive: true });
    }

    function teclas(e) {
        if (!tarjeta) return;
        if (e.key === "Escape") cerrar();
        if (e.key === "ArrowRight") tarjeta.querySelector(".guia-sig").click();
        if (e.key === "ArrowLeft" && indice > 0) ir(indice - 1);
    }

    function ubicar() {
        if (!tarjeta) return;
        const p = pasos[indice];
        const el = p.el;
        const margen = 8;
        if (!el) {
            foco.style.cssText = "opacity:0";
            capa.classList.add("oscura");
            tarjeta.classList.add("centrada");
            tarjeta.style.left = tarjeta.style.top = "";
            return;
        }
        capa.classList.remove("oscura");
        tarjeta.classList.remove("centrada");
        const r = el.getBoundingClientRect();
        const alto = Math.min(r.height, window.innerHeight - 40);
        foco.style.cssText = `opacity:1;left:${r.left - margen}px;top:${r.top - margen}px;width:${r.width + margen * 2}px;height:${alto + margen * 2}px`;
        // la tarjeta va debajo del elemento si cabe; si no, arriba; en celular, abajo de la pantalla
        const t = tarjeta.getBoundingClientRect();
        if (window.innerWidth < 640) { tarjeta.style.left = ""; tarjeta.style.top = ""; tarjeta.classList.add("abajo"); return; }
        tarjeta.classList.remove("abajo");
        let top = r.top + alto + margen + 14;
        if (top + t.height > window.innerHeight - 12) top = r.top - t.height - margen - 14;
        if (top < 12) top = Math.max(12, window.innerHeight - t.height - 16);
        let left = Math.min(Math.max(12, r.left), window.innerWidth - t.width - 12);
        tarjeta.style.left = left + "px";
        tarjeta.style.top = top + "px";
    }

    function ir(i) {
        indice = Math.max(0, Math.min(i, pasos.length - 1));
        const p = pasos[indice];
        tarjeta.querySelector(".guia-paso").textContent = "Paso " + (indice + 1) + " de " + pasos.length;
        tarjeta.querySelector(".guia-titulo").textContent = p.titulo;
        tarjeta.querySelector(".guia-texto").textContent = p.texto;
        tarjeta.querySelector(".guia-puntos").innerHTML = pasos.map((_, k) => '<i class="' + (k === indice ? "activo" : "") + '"></i>').join("");
        tarjeta.querySelector(".guia-atras").hidden = indice === 0;
        tarjeta.querySelector(".guia-sig").textContent = indice === pasos.length - 1 ? "¡Listo!" : "Siguiente";
        tarjeta.classList.remove("entra"); void tarjeta.offsetWidth; tarjeta.classList.add("entra");
        if (p.el) {
            const r = p.el.getBoundingClientRect();
            const fuera = r.top < 70 || r.bottom > window.innerHeight - 200;
            if (fuera) {
                p.el.scrollIntoView({ behavior: reducir ? "auto" : "smooth", block: r.height > window.innerHeight * 0.6 ? "start" : "center" });
            }
        }
        ubicar();
        setTimeout(ubicar, 450);
        setTimeout(ubicar, 900);
        hablar(p.titulo + ". " + p.texto);
        tarjeta.querySelector(".guia-sig").focus({ preventScroll: true });
    }

    function abrir() {
        pasos = datos.pasos
            .map(([sel, titulo, texto]) => ({ el: sel ? document.querySelector(sel) : null, sel, titulo, texto }))
            .filter((p) => !p.sel || visible(p.el));
        if (!pasos.length) return;
        if (!tarjeta) armar();
        document.body.classList.add("guia-activa");
        ir(0);
    }

    function cerrar() {
        if (puedeHablar) speechSynthesis.cancel();
        guardar(datos.clave, "visto");
        [capa, foco, tarjeta].forEach((n) => n && n.remove());
        capa = foco = tarjeta = null;
        document.body.classList.remove("guia-activa");
        document.removeEventListener("keydown", teclas);
        window.removeEventListener("resize", ubicar);
        window.removeEventListener("scroll", ubicar);
        btnGuia?.focus({ preventScroll: true });
    }

    btnGuia?.addEventListener("click", abrir);
    window.TampuGuia = { abrir, cerrar };

    if (leer(datos.clave) !== "visto") {
        if (datos.automatica) {
            setTimeout(abrir, 900);
        } else if (btnGuia) {
            // en las páginas públicas solo se invita, sin tapar nada
            btnGuia.classList.add("invita");
            const globo = document.createElement("span");
            globo.className = "guia-invitacion";
            globo.textContent = "¿Primera vez? Te muestro cómo funciona";
            btnGuia.appendChild(globo);
            setTimeout(() => btnGuia.classList.remove("invita"), 12000);
        }
    }
})();
