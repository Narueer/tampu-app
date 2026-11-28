// "Mi planificador" del estudiante: calendario con sus clases, turnos y visitas, más lo que él mismo anota.
(function () {
    'use strict';
    var P = window.TAMPU_PLAN;
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var div = document.getElementById('calendario');
    var enCelular = window.innerWidth < 760;

    function pedir(url, metodo, datos) {
        return fetch(url, {
            method: metodo,
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: datos ? JSON.stringify(datos) : undefined,
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (j) {
                if (!r.ok) throw j;
                return j;
            });
        });
    }

    function aviso(texto) {
        var t = document.createElement('div');
        t.className = 'plan-toast';
        t.setAttribute('role', 'status');
        t.textContent = texto;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('fuera'); }, 2200);
        setTimeout(function () { t.remove(); }, 2700);
    }

    function hhmm(fecha) {
        return String(fecha.getHours()).padStart(2, '0') + ':' + String(fecha.getMinutes()).padStart(2, '0');
    }
    function ymd(fecha) {
        return fecha.getFullYear() + '-' + String(fecha.getMonth() + 1).padStart(2, '0') + '-' + String(fecha.getDate()).padStart(2, '0');
    }

    // ---------------------------------------------------------------- calendario
    var calendario = new FullCalendar.Calendar(div, {
        locale: 'es',
        initialView: enCelular ? 'listWeek' : 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: enCelular ? 'listWeek,dayGridMonth' : 'timeGridWeek,dayGridMonth,listMonth',
        },
        buttonText: { listMonth: 'Lista', listWeek: 'Lista' },
        slotMinTime: '07:00:00',
        slotMaxTime: '23:00:00',
        allDayText: 'Día',
        nowIndicator: true,
        height: 'auto',
        selectable: true,
        selectMirror: true,
        editable: false,
        eventDurationEditable: true,
        longPressDelay: 350,
        dayMaxEvents: 3,
        events: div.dataset.eventos,
        eventDidMount: function (info) {
            var a = info.event.extendedProps.actividad;
            if (a && a.nota) info.el.title = a.nota;
        },
        eventContent: function (arg) {
            var a = arg.event.extendedProps.actividad;
            var titulo = arg.event.title.replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
            if (arg.event.display === 'background') return { html: '<span class="ev-libre-texto">' + titulo + '</span>' };
            var hora = arg.timeText ? '<span class="ev-hora">' + arg.timeText + '</span>' : '';
            var icono = a ? arg.event.extendedProps.icono : ({ 'ev-clases': 'bi-mortarboard-fill', 'ev-trabajo': 'bi-briefcase-fill', 'ev-visita': 'bi-house-door-fill' })[arg.event.classNames[0]];
            var check = a && a.hecha && a.repetir === 'no' ? '<i class="bi bi-check-circle-fill"></i> ' : '';
            // en la lista los eventos no tienen fondo de color: el punto ya lo da FullCalendar
            return { html: '<div class="ev-dentro">' + hora + '<span class="ev-nombre">' + check + (icono ? '<i class="bi ' + icono + '"></i> ' : '') + titulo + '</span></div>' };
        },
        select: function (info) {
            abrir(null, {
                fecha: ymd(info.start),
                hora_inicio: info.allDay ? '' : hhmm(info.start),
                hora_fin: info.allDay ? '' : hhmm(info.end),
            });
            calendario.unselect();
        },
        eventClick: function (info) {
            var a = info.event.extendedProps.actividad;
            if (!a) return;          // clases, turnos y visitas siguen su enlace normal
            info.jsEvent.preventDefault();
            abrir(a);
        },
        eventDrop: mover,
        eventResize: mover,
        loading: function (cargando) { div.classList.toggle('cargando', cargando); },
    });
    calendario.render();

    function mover(info) {
        var a = info.event.extendedProps.actividad;
        var e = info.event;
        pedir(P.rutas.base + '/' + a.id + '/mover', 'PATCH', {
            fecha: ymd(e.start),
            hora_inicio: e.allDay ? null : hhmm(e.start),
            hora_fin: e.allDay || !e.end ? null : hhmm(e.end),
        }).then(function () {
            aviso('Movido al ' + e.start.toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric' }));
            refrescar();
        }).catch(function () { info.revert(); aviso('No se pudo mover.'); });
    }

    // ---------------------------------------------------------------- filtros
    var filtros = document.getElementById('planFiltros');
    function aplicarFiltros() {
        filtros.querySelectorAll('input').forEach(function (c) {
            div.classList.toggle('sin-' + c.value, !c.checked);
        });
        try { localStorage.setItem('tampu-plan-filtros', JSON.stringify([].map.call(filtros.querySelectorAll('input:not(:checked)'), function (c) { return c.value; }))); } catch (e) {}
    }
    try {
        (JSON.parse(localStorage.getItem('tampu-plan-filtros') || '[]')).forEach(function (v) {
            var c = filtros.querySelector('input[value="' + v + '"]');
            if (c) c.checked = false;
        });
    } catch (e) {}
    filtros.addEventListener('change', aplicarFiltros);
    aplicarFiltros();

    // ---------------------------------------------------------------- lo que viene
    var lista = document.getElementById('planLista');
    var resumen = document.getElementById('planResumen');
    function refrescar() {
        calendario.refetchEvents();
        fetch(P.rutas.proximas, { headers: { Accept: 'application/json' } }).then(function (r) { return r.json(); }).then(function (d) {
            resumen.hidden = false;
            avisarHoy(d.lista);
            resumen.querySelector('[data-r="examenes"]').textContent = d.resumen.examenes;
            resumen.querySelector('[data-r="entregas"]').textContent = d.resumen.entregas;
            resumen.querySelector('[data-r="pagos"]').textContent = d.resumen.pagos;
            lista.innerHTML = '';
            if (!d.lista.length) {
                lista.innerHTML = '<li class="plan-vacio"><i class="bi bi-cup-hot"></i><span>Nada anotado para estas dos semanas. Toca <b>Examen</b>, <b>Entrega</b> o <b>Pago</b> para empezar.</span></li>';
                return;
            }
            var dia = null;
            d.lista.forEach(function (x) {
                if (x.cuando !== dia) {
                    dia = x.cuando;
                    var cab = document.createElement('li');
                    cab.className = 'plan-dia' + (x.cuando === 'Hoy' ? ' hoy' : '');
                    cab.textContent = x.cuando;
                    lista.appendChild(cab);
                }
                var li = document.createElement('li');
                li.className = 'plan-item' + (x.hecha ? ' hecha' : '') + (x.urgente && !x.hecha ? ' urgente' : '');
                li.style.setProperty('--c', x.color);
                li.innerHTML = (x.repite ? '<span class="plan-check repite" title="Se repite"><i class="bi bi-arrow-repeat"></i></span>'
                        : '<button type="button" class="plan-check" aria-label="Marcar como hecho"><i class="bi ' + (x.hecha ? 'bi-check-circle-fill' : 'bi-circle') + '"></i></button>') +
                    '<button type="button" class="plan-abrir"><i class="bi ' + x.icono + '"></i><span></span><small></small></button>';
                li.querySelector('.plan-abrir span').textContent = x.titulo;
                li.querySelector('.plan-abrir small').textContent = [x.hora, x.monto].filter(Boolean).join(' · ');
                var check = li.querySelector('button.plan-check');
                if (check) check.addEventListener('click', function () {
                    pedir(P.rutas.base + '/' + x.id + '/hecha', 'POST').then(function (r) {
                        aviso(r.hecha ? '¡Bien! Marcado como hecho.' : 'Desmarcado.');
                        refrescar();
                    });
                });
                li.querySelector('.plan-abrir').addEventListener('click', function () {
                    var ev = calendario.getEvents().find(function (e) { return e.extendedProps.actividad && e.extendedProps.actividad.id === x.id; });
                    if (ev) abrir(ev.extendedProps.actividad);
                    else calendario.gotoDate(x.fecha);
                });
                lista.appendChild(li);
            });
        });
    }
    refrescar();

    // ---------------------------------------------------------------- formulario
    var modal = new bootstrap.Modal(document.getElementById('modalActividad'));
    var form = document.getElementById('formActividad');
    var campos = {
        titulo: form.querySelector('#aTitulo'), fecha: form.querySelector('#aFecha'), inicio: form.querySelector('#aInicio'), fin: form.querySelector('#aFin'),
        todoDia: form.querySelector('#aTodoDia'), monto: form.querySelector('#aMonto'), repetir: form.querySelector('#aRepetir'), hasta: form.querySelector('#aHasta'),
        nota: form.querySelector('#aNota'), error: form.querySelector('#aError'), borrar: form.querySelector('#aBorrar'), titulo2: document.getElementById('modalTitulo'),
    };
    var ejemplos = { examen: 'Ej: Parcial de Cálculo I', entrega: 'Ej: Informe de Física', pago: 'Ej: Alquiler del cuarto', estudio: 'Ej: Repasar Química', personal: 'Ej: Llamar a mi familia' };
    var editando = null;

    function tipo() { return (form.querySelector('input[name="tipo"]:checked') || {}).value; }
    function actualizarCampos() {
        var t = tipo();
        document.getElementById('filaMonto').hidden = t !== 'pago';
        document.getElementById('filaHoras').hidden = campos.todoDia.checked;
        document.getElementById('filaHasta').hidden = campos.repetir.value === 'no';
        campos.titulo.placeholder = ejemplos[t] || '';
    }
    form.addEventListener('change', actualizarCampos);

    function abrir(a, nuevo) {
        editando = a ? a.id : null;
        nuevo = nuevo || {};
        var datos = a || {
            tipo: nuevo.tipo || 'examen', titulo: '', fecha: nuevo.fecha || ymd(new Date()), hora_inicio: nuevo.hora_inicio || '',
            hora_fin: nuevo.hora_fin || '', repetir: nuevo.repetir || 'no', hasta: '', monto: nuevo.monto || '', nota: '',
        };
        form.querySelector('input[name="tipo"][value="' + datos.tipo + '"]').checked = true;
        campos.titulo.value = datos.titulo || nuevo.titulo || '';
        campos.fecha.value = datos.fecha;
        campos.inicio.value = datos.hora_inicio || '';
        campos.fin.value = datos.hora_fin || '';
        campos.todoDia.checked = !datos.hora_inicio;
        campos.monto.value = datos.monto || '';
        campos.repetir.value = datos.repetir || 'no';
        campos.hasta.value = datos.hasta || '';
        campos.nota.value = datos.nota || '';
        campos.error.textContent = '';
        campos.borrar.hidden = !a;
        campos.titulo2.textContent = a ? 'Editar' : 'Nueva actividad';
        actualizarCampos();
        modal.show();
        setTimeout(function () { campos.titulo.focus(); }, 350);
    }

    document.querySelectorAll('[data-nuevo]').forEach(function (b) {
        b.addEventListener('click', function () {
            abrir(null, { tipo: b.dataset.nuevo, hora_inicio: b.dataset.nuevo === 'estudio' ? '16:00' : '', hora_fin: b.dataset.nuevo === 'estudio' ? '18:00' : '' });
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var datos = {
            tipo: tipo(), titulo: campos.titulo.value.trim(), fecha: campos.fecha.value,
            hora_inicio: campos.todoDia.checked ? null : (campos.inicio.value || null),
            hora_fin: campos.todoDia.checked ? null : (campos.fin.value || null),
            repetir: campos.repetir.value, hasta: campos.repetir.value === 'no' ? null : (campos.hasta.value || null),
            monto: tipo() === 'pago' && campos.monto.value !== '' ? campos.monto.value : null,
            nota: campos.nota.value.trim() || null,
        };
        if (!datos.titulo) { campos.error.textContent = 'Ponle un nombre.'; campos.titulo.focus(); return; }
        var boton = document.getElementById('aGuardar');
        boton.disabled = true;
        pedir(editando ? P.rutas.base + '/' + editando : P.rutas.guardar, editando ? 'PUT' : 'POST', datos).then(function (r) {
            modal.hide();
            aviso(r.mensaje || 'Guardado.');
            refrescar();
        }).catch(function (err) {
            var errores = err && err.errors ? Object.values(err.errors).map(function (x) { return x[0]; }) : [];
            campos.error.textContent = errores[0] || (err && err.message) || 'No se pudo guardar. Revisa los datos.';
        }).finally(function () { boton.disabled = false; });
    });

    campos.borrar.addEventListener('click', function () {
        if (!editando) return;
        if (campos.borrar.dataset.seguro !== '1') {
            campos.borrar.dataset.seguro = '1';
            campos.borrar.innerHTML = '<i class="bi bi-trash"></i> ¿Seguro? Toca otra vez';
            setTimeout(function () { campos.borrar.dataset.seguro = ''; campos.borrar.innerHTML = '<i class="bi bi-trash"></i> Borrar'; }, 3000);
            return;
        }
        pedir(P.rutas.base + '/' + editando, 'DELETE').then(function (r) {
            campos.borrar.dataset.seguro = '';
            campos.borrar.innerHTML = '<i class="bi bi-trash"></i> Borrar';
            modal.hide();
            aviso(r.mensaje || 'Eliminado.');
            refrescar();
        });
    });

    // ---------------------------------------------------------------- armar mi semana
    var btnArmar = document.getElementById('btnArmar');
    if (btnArmar) {
        btnArmar.addEventListener('click', function () {
            btnArmar.disabled = true;
            pedir(P.rutas.armar, 'POST', { bloques: document.getElementById('bloquesSemana').value })
                .then(function (r) { aviso(r.mensaje); refrescar(); })
                .catch(function () { aviso('No se pudo armar tu semana.'); })
                .finally(function () { btnArmar.disabled = false; });
        });
    }

    // ---------------------------------------------------------------- avisos del navegador
    // si la persona lo permite, al abrir el planificador le recordamos lo de hoy con una notificación
    function avisarHoy(lista) {
        if (!('Notification' in window)) return;
        var hoy = lista.filter(function (x) { return x.cuando === 'Hoy' && !x.hecha && ['examen', 'entrega', 'pago'].indexOf(x.tipo) !== -1; });
        if (!hoy.length) return;
        var clave = 'tampu-aviso-' + hoy[0].fecha;
        try { if (sessionStorage.getItem(clave)) return; sessionStorage.setItem(clave, '1'); } catch (e) {}
        var mostrar = function () {
            new Notification('TAMPU · Hoy tienes ' + hoy.length + (hoy.length === 1 ? ' pendiente' : ' pendientes'), {
                body: hoy.map(function (x) { return x.titulo + (x.hora ? ' (' + x.hora + ')' : ''); }).join('\n'),
                icon: '/img/icono-192.png',
            });
        };
        if (Notification.permission === 'granted') mostrar();
    }
    var btnAvisos = document.getElementById('btnAvisos');
    if (btnAvisos && 'Notification' in window && Notification.permission === 'default') {
        btnAvisos.hidden = false;
        btnAvisos.addEventListener('click', function () {
            Notification.requestPermission().then(function (p) {
                btnAvisos.hidden = true;
                aviso(p === 'granted' ? 'Listo: te avisaremos en este navegador.' : 'Sin problema, igual te llegan al correo y a la campanita.');
            });
        });
    }

    // ---------------------------------------------------------------- pago del cuarto
    var btnAlquiler = document.getElementById('btnAlquiler');
    if (btnAlquiler) {
        btnAlquiler.addEventListener('click', function () {
            var dia = Math.min(31, Math.max(1, parseInt(document.getElementById('diaAlquiler').value, 10) || 5));
            var hoy = new Date();
            var fecha = new Date(hoy.getFullYear(), hoy.getMonth(), dia);
            if (fecha < new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate())) fecha = new Date(hoy.getFullYear(), hoy.getMonth() + 1, dia);
            pedir(P.rutas.guardar, 'POST', {
                tipo: 'pago', titulo: 'Alquiler del cuarto', fecha: ymd(fecha), hora_inicio: null, hora_fin: null,
                repetir: 'mensual', hasta: null, monto: btnAlquiler.dataset.monto, nota: 'Pide recibo por cada pago.',
            }).then(function () {
                document.getElementById('planAlquiler').remove();
                aviso('Listo: te lo mostramos cada mes.');
                refrescar();
            });
        });
    }
})();
