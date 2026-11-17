// menu para celular
const btnMenu = document.getElementById("btnMenu");
const menu = document.getElementById("menu");

btnMenu.addEventListener("click", function () {
    menu.classList.toggle("abierto");
});

// validar login
const formLogin = document.getElementById("formLogin");

if (formLogin) {
    formLogin.addEventListener("submit", function (evento) {
        const correo = document.getElementById("correo").value.trim();
        const clave = document.getElementById("clave").value;
        const mensaje = document.getElementById("mensajeLogin");

        if (correo === "" || clave === "") {
            evento.preventDefault();
            mensaje.textContent = "Completa tu correo y contraseña.";
        } else if (clave.length < 6) {
            evento.preventDefault();
            mensaje.textContent = "La contraseña debe tener al menos 6 caracteres.";
        }
    });
}

// registro
const formRegistro = document.getElementById("formRegistro");

if (formRegistro) {
    const tipo = document.getElementById("tipo");
    const campoDocumento = document.getElementById("campoDocumento");
    const campoProcedencia = document.getElementById("campoProcedencia");

    function mostrarCampos() {
        const esEstudiante = tipo.value === "estudiante";
        campoDocumento.classList.toggle("oculto", esEstudiante);
        campoProcedencia.classList.toggle("oculto", !esEstudiante);
    }

    tipo.addEventListener("change", mostrarCampos);
    mostrarCampos();

    formRegistro.addEventListener("submit", function (evento) {
        const nombre = document.getElementById("nombre").value.trim();
        const dni = document.getElementById("dni").value;
        // si entro con Google o Microsoft no hay campos de contraseña
        const conClave = document.getElementById("clave") !== null;
        const clave = conClave ? document.getElementById("clave").value : "";
        const clave2 = conClave ? document.getElementById("clave2").value : "";
        const documento = document.getElementById("documento");
        const mensaje = document.getElementById("mensajeRegistro");
        let error = "";

        if (nombre.length < 3) {
            error = "Escribe tu nombre completo.";
        } else if (!/^[0-9]{8}$/.test(dni)) {
            error = "El DNI debe tener 8 números.";
        } else if (conClave && (clave.length < 8 || !/[a-zA-Z]/.test(clave) || !/[0-9]/.test(clave))) {
            error = "La contraseña debe tener al menos 8 caracteres, con letras y números.";
        } else if (conClave && clave !== clave2) {
            error = "Las contraseñas no coinciden.";
        } else if (tipo.value !== "estudiante" && documento.files.length === 0) {
            error = "Sube la foto de tu DNI o RUC.";
        }

        if (error !== "") {
            evento.preventDefault();
            mensaje.textContent = error;
        }
    });
}

// vista previa de la foto
const inputFoto = document.getElementById("foto");
const vistaPrevia = document.getElementById("vistaPrevia");

if (inputFoto && vistaPrevia) {
    inputFoto.addEventListener("change", function () {
        if (inputFoto.files.length > 0) {
            vistaPrevia.src = URL.createObjectURL(inputFoto.files[0]);
            vistaPrevia.classList.remove("oculto");
        }
    });
}

// confirmar antes de eliminar
const formsEliminar = document.querySelectorAll(".form-eliminar");

for (let i = 0; i < formsEliminar.length; i++) {
    formsEliminar[i].addEventListener("submit", function (evento) {
        if (!confirm("¿Seguro que quieres eliminarlo? No se puede deshacer.")) {
            evento.preventDefault();
        }
    });
}

// asistente virtual
const btnAsistente = document.getElementById("btnAsistente");
const asistente = document.getElementById("asistente");

if (btnAsistente && asistente) {
    const lista = document.getElementById("asistenteMensajes");
    const formAsistente = document.getElementById("asistenteForm");
    const inputAsistente = document.getElementById("asistenteTexto");
    const token = document.querySelector('meta[name="csrf-token"]').content;

    btnAsistente.addEventListener("click", function () {
        asistente.classList.toggle("oculto");
        inputAsistente.focus();
    });

    document.getElementById("cerrarAsistente").addEventListener("click", function () {
        asistente.classList.add("oculto");
    });

    function burbuja(texto, clase) {
        const div = document.createElement("div");
        div.className = "burbuja " + clase;
        div.textContent = texto;
        lista.appendChild(div);
        lista.scrollTop = lista.scrollHeight;
        return div;
    }

    function preguntar(texto) {
        burbuja(texto, "usuario");
        const espera = burbuja("Escribiendo...", "bot");

        fetch(asistente.dataset.url, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ mensaje: texto })
        })
            .then(r => r.json())
            .then(datos => {
                espera.textContent = datos.texto;

                datos.enlaces.forEach(function (e) {
                    const a = document.createElement("a");
                    a.href = e.url;
                    a.textContent = e.texto;
                    espera.appendChild(a);
                });

                if (datos.sugerencias.length) {
                    const cont = document.createElement("div");
                    cont.className = "sugerencias";
                    datos.sugerencias.forEach(function (s) {
                        const b = document.createElement("button");
                        b.type = "button";
                        b.textContent = s;
                        cont.appendChild(b);
                    });
                    lista.appendChild(cont);
                }
                lista.scrollTop = lista.scrollHeight;
            })
            .catch(() => {
                espera.textContent = "Ups, no pude responder. Intenta otra vez.";
            });
    }

    formAsistente.addEventListener("submit", function (evento) {
        evento.preventDefault();
        const texto = inputAsistente.value.trim();
        if (texto !== "") {
            preguntar(texto);
            inputAsistente.value = "";
        }
    });

    // botones de sugerencia
    lista.addEventListener("click", function (evento) {
        if (evento.target.matches(".sugerencias button")) {
            preguntar(evento.target.textContent);
        }
    });
}

// app instalable: registrar el service worker
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
        navigator.serviceWorker.register("/sw.js");
    });
}

// boton "Instalar la app" (Chrome en Android y computadora)
let pedidoInstalar = null;
const btnInstalar = document.getElementById("btnInstalar");

window.addEventListener("beforeinstallprompt", function (evento) {
    evento.preventDefault();
    pedidoInstalar = evento;
    if (btnInstalar) {
        btnInstalar.classList.remove("oculto");
    }
});

if (btnInstalar) {
    btnInstalar.addEventListener("click", function () {
        if (pedidoInstalar) {
            pedidoInstalar.prompt();
            pedidoInstalar = null;
            btnInstalar.classList.add("oculto");
        }
    });
}

// campana y mensajes en vivo: cada 10 segundos pregunta si hay algo nuevo
const avisoNuevo = document.getElementById("avisoNuevo");

if (avisoNuevo) {
    let antes = null;

    function ponerGlobo(id, cantidad) {
        const icono = document.getElementById(id);
        if (!icono) {
            return;
        }
        let globo = icono.querySelector(".globo");
        if (cantidad > 0) {
            if (!globo) {
                globo = document.createElement("span");
                globo.className = "globo";
                icono.appendChild(globo);
            }
            globo.textContent = cantidad;
        } else if (globo) {
            globo.remove();
        }
    }

    function revisarEstado() {
        fetch(avisoNuevo.dataset.estado, { headers: { "Accept": "application/json" } })
            .then(r => r.json())
            .then(datos => {
                ponerGlobo("iconoAvisos", datos.notificaciones);
                ponerGlobo("iconoChats", datos.mensajes);

                // en el celular el menu esta escondido, asi que el boton muestra un punto rojo
                if (btnMenu) {
                    btnMenu.classList.toggle("con-aviso", datos.notificaciones + datos.mensajes > 0);
                }

                // si llego una notificacion nueva, se muestra un aviso abajo
                if (antes !== null && datos.notificaciones > antes && datos.ultima) {
                    avisoNuevo.querySelector("span").textContent = datos.ultima;
                    avisoNuevo.classList.remove("oculto");
                    setTimeout(() => avisoNuevo.classList.add("oculto"), 8000);
                }
                antes = datos.notificaciones;
            })
            .catch(() => {});
    }

    revisarEstado();
    setInterval(revisarEstado, 10000);
}

// sombra del menu al bajar
const cabecera = document.getElementById("cabecera");
if (cabecera) {
    const sombra = () => cabecera.classList.toggle("con-sombra", window.scrollY > 10);
    window.addEventListener("scroll", sombra, { passive: true });
    sombra();
}

// los bloques .revelar aparecen cuando entran en pantalla
const revelables = document.querySelectorAll(".revelar");
if ("IntersectionObserver" in window) {
    const observador = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add("visto");
                observador.unobserve(e.target);
            }
        });
    }, { threshold: 0.15 });
    revelables.forEach(el => observador.observe(el));
} else {
    revelables.forEach(el => el.classList.add("visto"));
}

// numeros que cuentan desde cero (si la persona no pidio menos movimiento)
const sinMovimiento = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
document.querySelectorAll(sinMovimiento ? ".nada" : ".contador").forEach(function (el) {
    const hasta = parseInt(el.dataset.hasta, 10) || 0;
    const obs = new IntersectionObserver(function (entradas) {
        if (!entradas[0].isIntersecting) return;
        obs.disconnect();
        const inicio = performance.now();
        function paso(ahora) {
            const avance = Math.min((ahora - inicio) / 1200, 1);
            el.textContent = Math.round(hasta * (1 - Math.pow(1 - avance, 3)));
            if (avance < 1) requestAnimationFrame(paso);
        }
        requestAnimationFrame(paso);
    });
    obs.observe(el);
});

// flechas de los carriles de tarjetas
document.querySelectorAll(".flecha").forEach(function (boton) {
    boton.addEventListener("click", function () {
        const carril = document.querySelector(boton.dataset.carril);
        const item = carril.querySelector(".carril-item");
        carril.scrollBy({ left: (item.offsetWidth + 22) * boton.dataset.dir, behavior: "smooth" });
    });
});

// boton para volver arriba
document.querySelectorAll(".subir").forEach(function (a) {
    a.addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});

// galeria del detalle: el modal abre en la foto que se toco y las miniaturas siguen al carrusel
const modalFotos = document.getElementById("fotoGrande");
const carruselFotos = document.getElementById("carruselFotos");
if (modalFotos && carruselFotos && window.bootstrap) {
    const carrusel = bootstrap.Carousel.getOrCreateInstance(carruselFotos, { interval: false, ride: false });
    const miniaturas = modalFotos.querySelectorAll(".miniaturas button");
    modalFotos.addEventListener("show.bs.modal", function (e) {
        const n = e.relatedTarget ? parseInt(e.relatedTarget.dataset.foto || "0", 10) : 0;
        carrusel.to(n);
    });
    carruselFotos.addEventListener("slide.bs.carousel", function (e) {
        miniaturas.forEach((b, i) => b.classList.toggle("active", i === e.to));
    });
}

// compartir una publicacion (en el celular abre el menu de compartir)
const btnCompartir = document.getElementById("btnCompartir");
if (btnCompartir) {
    btnCompartir.addEventListener("click", async function () {
        const datos = { title: btnCompartir.dataset.titulo, url: btnCompartir.dataset.url };
        if (navigator.share) {
            try { await navigator.share(datos); } catch (e) { /* lo cerro */ }
        } else if (navigator.clipboard) {
            await navigator.clipboard.writeText(datos.url);
            btnCompartir.innerHTML = '<i class="bi bi-check2"></i> Enlace copiado';
        }
    });
}
