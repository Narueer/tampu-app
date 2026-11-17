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
const botonesInstalar = document.querySelectorAll("[data-instalar]");

window.addEventListener("beforeinstallprompt", function (evento) {
    evento.preventDefault();
    pedidoInstalar = evento;
    botonesInstalar.forEach(b => b.classList.remove("oculto"));
});

botonesInstalar.forEach(function (boton) {
    boton.addEventListener("click", function () {
        if (pedidoInstalar) {
            pedidoInstalar.prompt();
            pedidoInstalar = null;
            botonesInstalar.forEach(b => b.classList.add("oculto"));
        }
    });
});

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

// titulo de la portada: cada palabra entra por separado
document.querySelectorAll(".portada-texto h1").forEach(function (titulo) {
    let n = 0;
    function partir(nodo) {
        Array.from(nodo.childNodes).forEach(function (hijo) {
            if (hijo.nodeType === 3) {
                const trozos = hijo.textContent.split(/(\s+)/);
                const grupo = document.createDocumentFragment();
                trozos.forEach(function (t) {
                    if (!t.trim()) {
                        grupo.appendChild(document.createTextNode(t));
                        return;
                    }
                    const caja = document.createElement("span");
                    caja.className = "palabra";
                    const palabra = document.createElement("span");
                    palabra.textContent = t;
                    palabra.style.setProperty("--i", n++);
                    caja.appendChild(palabra);
                    grupo.appendChild(caja);
                });
                hijo.replaceWith(grupo);
            } else if (hijo.nodeType === 1 && hijo.tagName !== "BR") {
                partir(hijo);
            }
        });
    }
    partir(titulo);
    titulo.classList.add("partido");
});

// barra de progreso arriba
const progreso = document.getElementById("progreso");
if (progreso) {
    const medir = function () {
        const total = document.documentElement.scrollHeight - window.innerHeight;
        progreso.style.transform = "scaleX(" + (total > 0 ? window.scrollY / total : 0) + ")";
    };
    window.addEventListener("scroll", medir, { passive: true });
    medir();
}

// tarjetas que se inclinan un poco con el mouse
if (window.matchMedia("(hover: hover) and (pointer: fine)").matches && !sinMovimiento) {
    document.querySelectorAll(".tarjeta, .tarjeta-uni, .pasos .paso, .tarjeta-trabajo, .barrio").forEach(function (el) {
        el.addEventListener("pointermove", function (e) {
            const r = el.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - 0.5;
            const y = (e.clientY - r.top) / r.height - 0.5;
            el.style.transform = "perspective(900px) rotateX(" + (-y * 5) + "deg) rotateY(" + (x * 5) + "deg) translateY(-6px)";
            el.style.setProperty("--luz-x", (x + 0.5) * 100 + "%");
            el.style.setProperty("--luz-y", (y + 0.5) * 100 + "%");
        });
        el.addEventListener("pointerleave", function () {
            el.style.transform = "";
        });
    });
}

// listado: ver en cuadricula o en lista (se recuerda en este navegador)
const botonesVista = document.querySelectorAll("[data-vista]");
if (botonesVista.length) {
    const lista = document.querySelector(".tarjetas");
    const poner = function (vista) {
        lista.classList.toggle("vista-lista", vista === "lista");
        botonesVista.forEach(b => b.classList.toggle("activo", b.dataset.vista === vista));
        try { localStorage.setItem("tampu-vista", vista); } catch (e) {}
    };
    botonesVista.forEach(b => b.addEventListener("click", () => poner(b.dataset.vista)));
    let guardada = "cuadricula";
    try { guardada = localStorage.getItem("tampu-vista") || "cuadricula"; } catch (e) {}
    poner(guardada);
}

// detalle: barra fija con el precio cuando la caja de reserva sale de la pantalla
const barraReserva = document.getElementById("barraReserva");
const cajaReserva = document.querySelector("[data-caja-reserva]");
if (barraReserva && cajaReserva && "IntersectionObserver" in window) {
    new IntersectionObserver(function (entradas) {
        const fuera = !entradas[0].isIntersecting && entradas[0].boundingClientRect.top < 0;
        barraReserva.classList.toggle("visible", fuera);
        document.body.classList.toggle("con-barra", fuera);
    }).observe(cajaReserva);
}
