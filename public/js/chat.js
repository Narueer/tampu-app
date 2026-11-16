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

    formChat.addEventListener("submit", function (evento) {
        evento.preventDefault();
        const texto = inputTexto.value.trim();
        if (texto === "") {
            return;
        }

        fetch(formChat.action, {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ texto: texto })
        })
            .then(r => r.json())
            .then(m => {
                agregar(m);
                inputTexto.value = "";
            });
    });

    function revisar() {
        fetch(caja.dataset.nuevos + "?desde=" + ultimoId(), { headers: { "Accept": "application/json" } })
            .then(r => r.json())
            .then(lista => lista.forEach(agregar));
    }

    caja.scrollTop = caja.scrollHeight;
    setInterval(revisar, 4000);
}
