// chat entre usuarios: envia con fetch y revisa cada 4 segundos
const caja = document.getElementById("chatMensajes");
const formChat = document.getElementById("chatForm");

if (caja && formChat) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const inputTexto = document.getElementById("chatTexto");

    function ultimoId() {
        const burbujas = caja.querySelectorAll(".burbuja");
        return burbujas.length ? burbujas[burbujas.length - 1].dataset.id : 0;
    }

    function agregar(m) {
        if (caja.querySelector('[data-id="' + m.id + '"]')) {
            return;
        }
        const div = document.createElement("div");
        div.className = "burbuja" + (m.mio ? " mia" : "");
        div.dataset.id = m.id;
        div.textContent = m.texto;
        const hora = document.createElement("small");
        hora.textContent = m.hora;
        div.appendChild(hora);
        caja.appendChild(div);
        caja.scrollTop = caja.scrollHeight;
    }

    function enviar(texto) {
        return fetch(formChat.action, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ texto: texto })
        })
            .then(r => r.json())
            .then(agregar);
    }

    formChat.addEventListener("submit", function (evento) {
        evento.preventDefault();
        const texto = inputTexto.value.trim();
        if (texto === "") {
            return;
        }
        enviar(texto).then(() => { inputTexto.value = ""; });
    });

    // mensajes rápidos: al tocarlos se envían; el saludo inicial desaparece después de usarlo
    const rapidos = document.getElementById("chatRapidos");
    if (rapidos) {
        // en computadora la rueda del mouse mueve la fila de lado
        rapidos.addEventListener("wheel", function (evento) {
            if (Math.abs(evento.deltaY) > Math.abs(evento.deltaX)) {
                rapidos.scrollLeft += evento.deltaY;
                evento.preventDefault();
            }
        }, { passive: false });
        rapidos.addEventListener("click", function (evento) {
            const chip = evento.target.closest(".chip-rapido");
            if (!chip || chip.disabled) {
                return;
            }
            chip.disabled = true;
            enviar(chip.dataset.texto).then(() => {
                if (chip === rapidos.firstElementChild && chip.dataset.texto.startsWith("Hola")) {
                    chip.remove();
                } else {
                    chip.disabled = false;
                    chip.classList.add("usado");
                }
            }).catch(() => { chip.disabled = false; });
        });
    }

    function revisar() {
        fetch(caja.dataset.nuevos + "?desde=" + ultimoId(), { headers: { "Accept": "application/json" } })
            .then(r => r.json())
            .then(lista => lista.forEach(agregar));
    }

    caja.scrollTop = caja.scrollHeight;
    setInterval(revisar, 4000);
}
