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
        const clave = document.getElementById("clave").value;
        const clave2 = document.getElementById("clave2").value;
        const documento = document.getElementById("documento");
        const mensaje = document.getElementById("mensajeRegistro");
        let error = "";

        if (nombre.length < 3) {
            error = "Escribe tu nombre completo.";
        } else if (!/^[0-9]{8}$/.test(dni)) {
            error = "El DNI debe tener 8 números.";
        } else if (clave.length < 6) {
            error = "La contraseña debe tener al menos 6 caracteres.";
        } else if (clave !== clave2) {
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
