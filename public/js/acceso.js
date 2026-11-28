// Pantallas de cuenta: login, registro, recuperar contraseña, confirmar correo y dos pasos.
// Cada parte se activa solo si la página tiene el elemento que la usa.
(function () {
    'use strict';

    // 1. ojito para ver la contraseña
    document.querySelectorAll('input[type="password"]').forEach(function (campo) {
        if (campo.closest('.campo-clave') === null) {
            var envoltura = document.createElement('div');
            envoltura.className = 'campo-clave';
            campo.parentNode.insertBefore(envoltura, campo);
            envoltura.appendChild(campo);
        }
        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'ver-clave';
        boton.setAttribute('aria-label', 'Mostrar contraseña');
        boton.innerHTML = '<i class="bi bi-eye"></i>';
        boton.addEventListener('click', function () {
            var ver = campo.type === 'password';
            campo.type = ver ? 'text' : 'password';
            boton.innerHTML = ver ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            boton.setAttribute('aria-label', ver ? 'Ocultar contraseña' : 'Mostrar contraseña');
            campo.focus();
        });
        campo.parentNode.appendChild(boton);
    });

    // 2. "¿Quisiste decir gmail.com?" cuando el dominio tiene un error de tipeo
    var dominios = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'icloud.com', 'live.com', 'unsaac.edu.pe', 'uandina.edu.pe'];
    function distancia(a, b) {
        var fila = [];
        for (var j = 0; j <= b.length; j++) fila[j] = j;
        for (var i = 1; i <= a.length; i++) {
            var previo = fila[0];
            fila[0] = i;
            for (var k = 1; k <= b.length; k++) {
                var temporal = fila[k];
                fila[k] = Math.min(fila[k] + 1, fila[k - 1] + 1, previo + (a[i - 1] === b[k - 1] ? 0 : 1));
                previo = temporal;
            }
        }
        return fila[b.length];
    }
    document.querySelectorAll('[data-sugerir-correo]').forEach(function (campo) {
        var aviso = document.createElement('p');
        aviso.className = 'sugerencia-correo';
        aviso.hidden = true;
        (campo.closest('.campo-con-icono') || campo).insertAdjacentElement('afterend', aviso);

        function revisar() {
            var valor = campo.value.trim().toLowerCase();
            var partes = valor.split('@');
            aviso.hidden = true;
            if (partes.length !== 2 || !partes[1] || dominios.indexOf(partes[1]) !== -1) return;
            var mejor = null, menor = 3;
            dominios.forEach(function (d) {
                var dist = distancia(partes[1], d);
                if (dist < menor) { menor = dist; mejor = d; }
            });
            if (!mejor) return;
            var propuesta = partes[0] + '@' + mejor;
            aviso.innerHTML = '¿Quisiste decir <button type="button" class="btn-enlace"></button>?';
            aviso.querySelector('button').textContent = propuesta;
            aviso.querySelector('button').addEventListener('click', function () {
                campo.value = propuesta;
                aviso.hidden = true;
                campo.focus();
            });
            aviso.hidden = false;
        }
        campo.addEventListener('blur', revisar);
        campo.addEventListener('input', function () { if (!aviso.hidden) revisar(); });
    });

    // 3. fuerza de la contraseña y lista de requisitos
    var nueva = document.querySelector('[data-fuerza]');
    if (nueva) {
        var repetir = document.querySelector('[data-repetir]');
        var barras = document.querySelectorAll('.fuerza span');
        var reglas = {
            largo: function (v) { return v.length >= 8; },
            letra: function (v) { return /[a-záéíóúñ]/i.test(v); },
            numero: function (v) { return /\d/.test(v); },
            igual: function (v) { return v.length > 0 && repetir && repetir.value === v; },
        };
        var colores = ['#a65a4f', '#b58a4a', '#879484', '#465548'];
        function actualizar() {
            var v = nueva.value;
            Object.keys(reglas).forEach(function (nombre) {
                var item = document.querySelector('[data-regla="' + nombre + '"]');
                if (!item) return;
                var ok = reglas[nombre](v);
                item.classList.toggle('cumple', ok);
                item.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            });
            var puntos = 0;
            if (v.length >= 8) puntos++;
            if (/[a-z]/i.test(v) && /\d/.test(v)) puntos++;
            if (v.length >= 12) puntos++;
            if (/[^a-z0-9]/i.test(v) || (/[a-z]/.test(v) && /[A-Z]/.test(v))) puntos++;
            if (!v) puntos = 0;
            barras.forEach(function (barra, i) {
                barra.style.background = i < puntos ? colores[puntos - 1] : '';
            });
        }
        nueva.addEventListener('input', actualizar);
        if (repetir) repetir.addEventListener('input', actualizar);
    }

    // 4. código en 6 casillas (pegar, borrar y saltar solos); el campo real sigue existiendo
    document.querySelectorAll('[data-codigo]').forEach(function (caja) {
        var real = caja.querySelector('input');
        var formulario = caja.closest('form');
        var casillas = [];
        real.classList.add('codigo-real');
        real.setAttribute('tabindex', '-1');
        real.setAttribute('aria-hidden', 'true');

        var fila = document.createElement('div');
        fila.className = 'codigo-fila';
        for (var i = 0; i < 6; i++) {
            var c = document.createElement('input');
            c.type = 'text';
            c.inputMode = 'numeric';
            c.maxLength = 1;
            c.className = 'codigo-casilla';
            c.setAttribute('aria-label', 'Número ' + (i + 1) + ' de 6');
            if (i === 0) c.autocomplete = 'one-time-code';
            casillas.push(c);
            fila.appendChild(c);
            if (i === 2) {
                var guion = document.createElement('span');
                guion.className = 'codigo-guion';
                guion.textContent = '–';
                fila.appendChild(guion);
            }
        }
        caja.appendChild(fila);

        function juntar() {
            real.value = casillas.map(function (c) { return c.value; }).join('');
            casillas.forEach(function (c) { c.classList.toggle('llena', c.value !== ''); });
            if (real.value.length === 6 && formulario && !formulario.dataset.enviado) {
                formulario.dataset.enviado = '1';
                setTimeout(function () { formulario.requestSubmit ? formulario.requestSubmit() : formulario.submit(); }, 180);
            }
        }
        function repartir(texto, desde) {
            var numeros = texto.replace(/\D/g, '').split('');
            for (var j = desde; j < 6 && numeros.length; j++) casillas[j].value = numeros.shift();
            casillas[Math.min(desde + texto.replace(/\D/g, '').length, 5)].focus();
            juntar();
        }
        casillas.forEach(function (c, i) {
            c.addEventListener('input', function () {
                if (c.value.length > 1 || /\D/.test(c.value)) { var t = c.value; c.value = ''; repartir(t, i); return; }
                if (c.value && i < 5) casillas[i + 1].focus();
                juntar();
            });
            c.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !c.value && i > 0) { casillas[i - 1].value = ''; casillas[i - 1].focus(); juntar(); e.preventDefault(); }
                if (e.key === 'ArrowLeft' && i > 0) casillas[i - 1].focus();
                if (e.key === 'ArrowRight' && i < 5) casillas[i + 1].focus();
            });
            c.addEventListener('paste', function (e) {
                e.preventDefault();
                repartir((e.clipboardData || window.clipboardData).getData('text'), i);
            });
            c.addEventListener('focus', function () { c.select(); });
        });
        if (real.value) repartir(real.value, 0);
        if (real.hasAttribute('autofocus') || document.activeElement === real || caja.closest('.acceso')) casillas[0].focus();

        // dos pasos: el código de respaldo tiene letras, así que se cambia al campo normal
        var cambiar = document.querySelector('[data-usar-respaldo]');
        if (cambiar && caja.hasAttribute('data-respaldo')) {
            cambiar.addEventListener('click', function () {
                fila.remove();
                real.classList.remove('codigo-real');
                real.removeAttribute('aria-hidden');
                real.removeAttribute('tabindex');
                real.inputMode = 'text';
                real.placeholder = 'abcd-1234';
                real.value = '';
                real.focus();
                cambiar.closest('p').hidden = true;
            });
        }
    });

    // 5. botón con cuenta regresiva antes de poder reenviar
    document.querySelectorAll('[data-espera]').forEach(function (boton) {
        var clave = 'tampu-espera-' + location.pathname;
        var texto = boton.textContent;
        var hasta = 0;
        try { hasta = parseInt(sessionStorage.getItem(clave) || '0', 10); } catch (e) {}
        if (!hasta || hasta < Date.now()) {
            hasta = Date.now() + parseInt(boton.dataset.espera, 10) * 1000;
            try { sessionStorage.setItem(clave, String(hasta)); } catch (e) {}
        }
        function tic() {
            var faltan = Math.ceil((hasta - Date.now()) / 1000);
            if (faltan > 0) {
                boton.disabled = true;
                boton.textContent = texto + ' (' + faltan + ' s)';
                setTimeout(tic, 1000);
            } else {
                boton.disabled = false;
                boton.textContent = texto;
            }
        }
        tic();
        boton.closest('form').addEventListener('submit', function () {
            try { sessionStorage.removeItem(clave); } catch (e) {}
        });
    });

    // 6. el botón muestra que está trabajando y no se envía dos veces
    document.querySelectorAll('[data-cargando]').forEach(function (boton) {
        var formulario = boton.closest('form');
        if (!formulario) return;
        formulario.addEventListener('submit', function (e) {
            if (formulario.dataset.enviando) { e.preventDefault(); return; }
            if (formulario.checkValidity && !formulario.noValidate && !formulario.checkValidity()) return;
            formulario.dataset.enviando = '1';
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ' + boton.dataset.cargando;
        });
    });
})();
