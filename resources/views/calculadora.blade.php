@extends('layouts.app')

@section('titulo', '¿Me alcanza? Calcula cuánto cuesta vivir en Cusco')
@section('descripcion', 'Pon tu presupuesto del mes y mira cuánto te queda en cada distrito de Cusco: alquiler, comida, pasajes y servicios.')

@section('contenido')
    <section class="seccion calculadora">
        <div class="contenedor">
            <div class="encabezado-seccion izquierda">
                <span class="sobretitulo">Antes de mudarte</span>
                <h1 class="titulo-pagina">¿Me <em>alcanza</em>?</h1>
                <p class="texto-suave">Arma tu presupuesto del mes o compara cuánto te quedaría en cada distrito de Cusco.
                    Los precios de los cuartos son los promedios reales de TAMPU; lo demás lo ajustas a tu realidad.</p>
            </div>

            <div class="calc-pestanas" role="tablist" aria-label="Herramientas">
                <button type="button" role="tab" id="tabMio" aria-controls="panelMio" aria-selected="{{ $pestana !== 'distritos' ? 'true' : 'false' }}"><i class="bi bi-wallet2"></i> Mi presupuesto</button>
                <button type="button" role="tab" id="tabDistritos" aria-controls="panelDistritos" aria-selected="{{ $pestana === 'distritos' ? 'true' : 'false' }}"><i class="bi bi-geo-alt"></i> Comparar distritos</button>
            </div>

            {{-- ===== Mi presupuesto ===== --}}
            <div class="row g-4 presu" id="panelMio" role="tabpanel" aria-labelledby="tabMio" @if ($pestana === 'distritos') hidden @endif>
                <div class="col-lg-5">
                    <form class="caja calc-form presu-form" id="presuForm" onsubmit="return false">
                        <h2>Lo que recibes</h2>
                        <div class="presu-ingreso">
                            <label for="pIngreso" class="visually-hidden">Dinero que recibes</label>
                            <span class="presu-moneda">S/</span>
                            <input type="number" id="pIngreso" min="0" step="10" inputmode="numeric">
                            <div class="presu-periodo" role="radiogroup" aria-label="Cada cuánto lo recibes">
                                <label><input type="radio" name="pPeriodo" value="mes"> al mes</label>
                                <label><input type="radio" name="pPeriodo" value="semana"> a la semana</label>
                            </div>
                        </div>
                        <div class="calc-grid">
                            <label for="pAhorro">Quiero ahorrar al mes <span>S/ <input type="number" id="pAhorro" min="0" step="10"></span></label>
                            <label for="pDias">Días de clase por semana <span><input type="number" id="pDias" min="1" max="7"></span></label>
                            <label for="pCuarto">Ya tengo cuarto, pago (opcional) <span>S/ <input type="number" id="pCuarto" min="0" step="10" placeholder="—"></span></label>
                        </div>

                        <h2 class="mt-4">Tus gastos</h2>
                        <ul class="presu-items" id="pItems"></ul>
                        <button type="button" class="btn btn-borde btn-sm" id="pAgregar"><i class="bi bi-plus-lg"></i> Agregar gasto</button>
                        <div class="presu-sugeridos" aria-label="Gastos que suelen olvidarse">
                            <span>¿Te olvidas de alguno?</span>
                            <button type="button" data-nombre="Lavandería" data-monto="20" data-cada="mes" data-icono="basket">Lavandería</button>
                            <button type="button" data-nombre="Pasaje a mi pueblo" data-monto="60" data-cada="mes" data-icono="signpost-2">Pasaje a mi pueblo</button>
                            <button type="button" data-nombre="Medicinas" data-monto="20" data-cada="mes" data-icono="capsule">Medicinas</button>
                            <button type="button" data-nombre="Gimnasio" data-monto="50" data-cada="mes" data-icono="bicycle">Gimnasio</button>
                            <button type="button" data-nombre="Gas para cocinar" data-monto="45" data-cada="mes" data-icono="fire">Gas</button>
                            <button type="button" data-nombre="Streaming" data-monto="20" data-cada="mes" data-icono="play-btn">Streaming</button>
                        </div>
                    </form>
                </div>

                <div class="col-lg-7">
                    <article class="caja presu-hoja" id="presuHoja" aria-live="polite">
                        <div class="presu-cabecera">
                            <div>
                                <span class="sobretitulo">Mi presupuesto en Cusco</span>
                                <p class="presu-fecha" id="pFecha"></p>
                            </div>
                            <img src="{{ asset('img/logo-160.png') }}" alt="" class="presu-logo">
                        </div>
                        <div class="presu-grande" id="pGrande"></div>
                        <div class="presu-kpis" id="pKpis"></div>
                        <div class="presu-barra" id="pBarra" role="img"></div>
                        <table class="presu-tabla">
                            <caption class="visually-hidden">Gastos por semana y por mes</caption>
                            <thead><tr><th scope="col">Gasto</th><th scope="col">A la semana</th><th scope="col">Al mes</th><th scope="col">%</th></tr></thead>
                            <tbody id="pTabla"></tbody>
                            <tfoot id="pTotal"></tfoot>
                        </table>
                        <p class="presu-consejo" id="pConsejo"></p>
                        <div class="presu-acciones">
                            <a class="btn" id="pVerCuartos" href="{{ route('habitaciones.index') }}"><i class="bi bi-search"></i> Ver cuartos que entran</a>
                            <button type="button" class="btn btn-borde" id="pImprimir"><i class="bi bi-printer"></i> Imprimir o guardar PDF</button>
                            <button type="button" class="btn-texto" id="pReiniciar">Volver a los valores de ejemplo</button>
                            <span class="presu-estado" id="pEstado" role="status"></span>
                        </div>
                        <p class="presu-pie">Hecho con TAMPU · tampu.onrender.com · Los montos son una guía para organizarte.</p>
                    </article>
                </div>
            </div>

            {{-- ===== Comparar distritos ===== --}}
            <div class="row g-4" id="panelDistritos" role="tabpanel" aria-labelledby="tabDistritos" @if ($pestana !== 'distritos') hidden @endif>
                {{-- datos del estudiante --}}
                <div class="col-lg-4">
                    <form class="caja calc-form" id="calcForm" onsubmit="return false">
                        <h2>Tus datos</h2>
                        <label for="cPresupuesto">Dinero al mes (S/)</label>
                        <div class="calc-presupuesto">
                            <input type="range" id="cRango" min="400" max="2500" step="50" value="{{ $presupuesto }}" aria-label="Presupuesto mensual">
                            <input type="number" id="cPresupuesto" min="0" step="10" value="{{ $presupuesto }}" inputmode="numeric">
                        </div>

                        <label for="cUni">¿Dónde estudias?</label>
                        <select id="cUni">
                            @foreach ($lugares as $clave => $nombre)
                                <option value="{{ $clave }}" @selected($uni === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>

                        @if ($cuarto)
                            <label class="calc-check"><input type="checkbox" id="cCuarto" checked data-precio="{{ (int) $cuarto->precio }}" data-distrito="{{ $cuarto->distrito }}">
                                Usar el precio de «{{ $cuarto->titulo }}» (S/ {{ (int) $cuarto->precio }})</label>
                        @endif

                        <details class="calc-ajustes" open>
                            <summary>Ajustar gastos</summary>
                            <div class="calc-grid">
                                <label>Almuerzo (menú) <span>S/ <input type="number" id="cMenu" value="10" min="0" step="0.5"></span></label>
                                <label>Desayuno y cena al día <span>S/ <input type="number" id="cOtras" value="9" min="0" step="0.5"></span></label>
                                <label>Pasaje universitario <span>S/ <input type="number" id="cPasaje" value="1" min="0" step="0.1"></span></label>
                                <label>Días de clase por semana <span><input type="number" id="cDias" value="5" min="1" max="7"></span></label>
                                <label>Luz, agua e internet si no están incluidos <span>S/ <input type="number" id="cServicios" value="45" min="0" step="5"></span></label>
                                <label>Útiles, celular y otros <span>S/ <input type="number" id="cOtros" value="80" min="0" step="5"></span></label>
                            </div>
                            <p class="calc-nota">Por ley, el pasaje universitario no puede costar más de la mitad del pasaje adulto: lleva tu carné.</p>
                        </details>
                    </form>
                </div>

                {{-- resultados --}}
                <div class="col-lg-8">
                    <div class="calc-resumen caja" id="calcResumen" aria-live="polite"></div>
                    <div class="calc-distritos" id="calcDistritos"></div>
                    <p class="calc-nota">Alquiler: promedio de los cuartos publicados en cada distrito. Si queda a más de 1,2 km de donde estudias se suman 2 pasajes por día de clase.
                        Si en ese distrito la mayoría de cuartos ya incluye agua, luz e internet, no se suman esos servicios. Es una guía, no un presupuesto exacto.</p>
                </div>
            </div>
        </div>
    </section>

    <script id="calcDatos" type="application/json">{!! json_encode(['distritos' => $distritos], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script>
        // toda la cuenta se hace en el navegador: cambia al instante mientras mueves los valores
        (function () {
            const datos = JSON.parse(document.getElementById('calcDatos').textContent);
            const $ = (id) => document.getElementById(id);
            const num = (id) => parseFloat($(id)?.value) || 0;
            const soles = (n) => 'S/ ' + Math.round(n).toLocaleString('es-PE');
            const urlCuartos = @json(route('habitaciones.index'));

            function calcular() {
                const tiene = num('cPresupuesto');
                const uni = $('cUni').value;
                const cuarto = $('cCuarto');
                const semanas = 4.3;
                const comida = (num('cMenu') + num('cOtras')) * 30;
                const otros = num('cOtros');
                const filas = datos.distritos.map((d) => {
                    const km = d.km[uni];
                    const enBus = km !== null && km > 1.2;
                    const usaCuarto = cuarto && cuarto.checked && cuarto.dataset.distrito === d.distrito;
                    const alquiler = usaCuarto ? parseFloat(cuarto.dataset.precio) : d.promedio;
                    const pasajes = enBus ? num('cPasaje') * 2 * num('cDias') * semanas : 0;
                    const servicios = d.incluyeServicios >= 0.5 ? 0 : num('cServicios');
                    const total = alquiler + comida + pasajes + servicios + otros;
                    return { ...d, km, enBus, alquiler, pasajes, servicios, comida, otros, total, queda: tiene - total, usaCuarto };
                }).sort((a, b) => b.queda - a.queda);

                const mejor = filas[0];
                const alcanzan = filas.filter((f) => f.queda >= 0).length;
                $('calcResumen').className = 'calc-resumen caja ' + (mejor.queda >= 100 ? 'bien' : mejor.queda >= 0 ? 'justo' : 'falta');
                $('calcResumen').innerHTML = mejor.queda >= 0
                    ? `<span class="calc-icono"><i class="bi bi-emoji-smile"></i></span><div><b>Te alcanza en ${alcanzan} de ${filas.length} distritos.</b>
                       Lo más conveniente para ti es <b>${mejor.distrito}</b>: te quedarían <b>${soles(mejor.queda)}</b> al mes para imprevistos o ahorro.</div>`
                    : `<span class="calc-icono"><i class="bi bi-exclamation-circle"></i></span><div><b>Con ${soles(tiene)} todavía no alcanza.</b>
                       Te faltarían ${soles(-mejor.queda)} en ${mejor.distrito}, el más barato para ti. Prueba un cuarto compartido o busca un
                       <a href="{{ route('trabajos.index') }}">trabajo compatible con tus clases</a>.</div>`;

                const max = Math.max(tiene, ...filas.map((f) => f.total));
                $('calcDistritos').innerHTML = filas.map((f) => {
                    const p = (n) => (n / max * 100).toFixed(1) + '%';
                    return `<article class="calc-distrito ${f.queda >= 0 ? '' : 'no-alcanza'}">
                        <header><h3>${f.distrito}</h3>
                            <span class="calc-queda">${f.queda >= 0 ? 'Te quedan ' + soles(f.queda) : 'Faltan ' + soles(-f.queda)}</span></header>
                        <div class="calc-barra" role="img" aria-label="Gastos en ${f.distrito}: total ${soles(f.total)}">
                            <span class="b-alquiler" style="width:${p(f.alquiler)}"></span><span class="b-comida" style="width:${p(f.comida)}"></span><span class="b-pasajes" style="width:${p(f.pasajes)}"></span><span class="b-servicios" style="width:${p(f.servicios + f.otros)}"></span>
                            <i class="calc-tope" style="left:${p(tiene)}" title="Tu presupuesto"></i>
                        </div>
                        <ul class="calc-detalle">
                            <li><i class="b-alquiler"></i>Cuarto ${soles(f.alquiler)}${f.usaCuarto ? ' (el que elegiste)' : ' promedio · desde ' + soles(f.minimo)}</li>
                            <li><i class="b-comida"></i>Comida ${soles(f.comida)}</li>
                            <li><i class="b-pasajes"></i>${f.enBus ? 'Pasajes ' + soles(f.pasajes) + ' (' + f.km + ' km)' : 'A pie a tu sede (' + (f.km ?? '?') + ' km)'}</li>
                            <li><i class="b-servicios"></i>Servicios y otros ${soles(f.servicios + f.otros)}${f.servicios === 0 ? ' · la mayoría incluye luz y wifi' : ''}</li>
                        </ul>
                        <a class="calc-ver" href="${urlCuartos}?distrito=${encodeURIComponent(f.distrito)}&precio_max=${Math.max(0, Math.round(f.alquiler + Math.max(0, f.queda)))}">Ver ${f.cuartos} cuartos <i class="bi bi-arrow-right"></i></a>
                    </article>`;
                }).join('');
            }

            $('cRango').addEventListener('input', () => { $('cPresupuesto').value = $('cRango').value; calcular(); });
            $('cPresupuesto').addEventListener('input', () => { $('cRango').value = $('cPresupuesto').value; calcular(); });
            document.querySelectorAll('#calcForm input, #calcForm select').forEach((el) => el.addEventListener('input', calcular));
            calcular();
        })();
    </script>
@endsection

@push('scripts')
    <script>
        // ===== Mi presupuesto =====
        (function () {
            const P = window.TampuPresu;
            const $ = (id) => document.getElementById(id);
            const conSesion = @json(auth()->check());
            const urlGuardar = @json(auth()->check() ? route('presupuesto.guardar') : null);
            const urlCuartos = @json(route('habitaciones.index'));
            const urlLogin = @json(route('login'));
            const COLORES = ['#b0502a', '#c99a2e', '#17352f', '#3f7a6b', '#7a3b52', '#5b6c8f', '#8a6a4f', '#2f6f8f', '#a35d76', '#6b8f3f'];
            const ICONOS = ['wallet2', 'cart', 'bag', 'basket', 'cup-hot', 'egg-fried', 'bus-front', 'phone', 'wifi', 'lightning-charge', 'journal-text', 'emoji-smile', 'heart-pulse', 'capsule', 'bicycle', 'fire', 'play-btn', 'signpost-2', 'gift', 'piggy-bank'];
            let p = P.leer() || P.porDefecto();
            const desdeServidor = !!window.TAMPU_PRESU;
            const local = (() => { try { return JSON.parse(localStorage.getItem(P.CLAVE) || 'null'); } catch (e) { return null; } })();

            function pintarFormulario() {
                $('pIngreso').value = p.ingreso;
                document.querySelectorAll('[name=pPeriodo]').forEach((r) => r.checked = r.value === (p.periodo || 'mes'));
                $('pAhorro').value = p.ahorro ?? 0;
                $('pDias').value = p.dias || 5;
                $('pCuarto').value = p.cuarto ?? '';
                $('pItems').innerHTML = p.items.map((it, i) => `
                    <li style="--c:${COLORES[i % COLORES.length]}">
                        <button type="button" class="presu-icono" data-i="${i}" aria-label="Cambiar ícono de ${esc(it.nombre)}" title="Cambiar ícono"><i class="bi bi-${it.icono || 'wallet2'}"></i></button>
                        <input type="text" value="${esc(it.nombre)}" maxlength="40" data-i="${i}" data-campo="nombre" aria-label="Nombre del gasto">
                        <span class="presu-monto">S/ <input type="number" value="${it.monto}" min="0" step="0.5" data-i="${i}" data-campo="monto" aria-label="Monto de ${esc(it.nombre)}"></span>
                        <select data-i="${i}" data-campo="cada" aria-label="Cada cuánto">
                            ${Object.entries(P.FRECUENCIAS).map(([k, f]) => `<option value="${k}" ${it.cada === k ? 'selected' : ''}>${f.texto}</option>`).join('')}
                        </select>
                        <button type="button" class="presu-quitar" data-quitar="${i}" aria-label="Quitar ${esc(it.nombre)}"><i class="bi bi-x-lg"></i></button>
                    </li>`).join('');
            }
            const esc = (t) => String(t ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function pintarResultado() {
                const r = P.calcular(p);
                const s = P.soles;
                $('pFecha').textContent = new Date().toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' });
                if (r.cuarto !== null) {
                    $('pGrande').className = 'presu-grande ' + (r.queda >= 0 ? 'bien' : 'falta');
                    $('pGrande').innerHTML = r.queda >= 0
                        ? `<small>Después de todo te quedan</small><b>${s(r.queda)}</b><span>al mes, además de tus ${s(r.ahorro)} de ahorro.</span>`
                        : `<small>Este mes te faltan</small><b>${s(-r.queda)}</b><span>Baja algún gasto o busca un <a href="{{ route('trabajos.index') }}">trabajo compatible con tus clases</a>.</span>`;
                } else {
                    $('pGrande').className = 'presu-grande ' + (r.tope >= 250 ? 'bien' : r.tope > 0 ? 'justo' : 'falta');
                    $('pGrande').innerHTML = r.tope > 0
                        ? `<small>Puedes pagar de cuarto hasta</small><b>${s(r.tope)}</b><span>al mes, sin tocar tus ${s(r.ahorro)} de ahorro.</span>`
                        : `<small>Con estos gastos</small><b>no queda para el cuarto</b><span>Te faltan ${s(r.gastos + r.ahorro - r.ingresoMes)}. Prueba un cuarto compartido o baja algún gasto.</span>`;
                }
                $('pKpis').innerHTML = `
                    <div><small>Recibes al mes</small><b>${s(r.ingresoMes)}</b></div>
                    <div><small>Gastos${r.cuarto !== null ? ' + cuarto' : ' sin cuarto'}</small><b>${s(r.gastos + (r.cuarto ?? 0))}</b></div>
                    <div><small>Necesitas por semana</small><b>${s((r.gastos + (r.cuarto ?? 0)) / P.SEMANAS)}</b></div>`;

                // barra: cada gasto, el cuarto (o el tope) y el ahorro
                const base = Math.max(r.ingresoMes, r.gastos + r.ahorro + (r.cuarto ?? 0), 1);
                const pct = (n) => (n / base * 100).toFixed(2) + '%';
                const cuartoBarra = r.cuarto ?? r.tope;
                $('pBarra').setAttribute('aria-label', 'Reparto de tu dinero: ' + r.filas.map((f) => f.nombre + ' ' + s(f.mes)).join(', '));
                $('pBarra').innerHTML = `<span class="seg-cuarto ${r.cuarto === null ? 'tope' : ''}" style="width:${pct(cuartoBarra)}" title="Cuarto ${s(cuartoBarra)}"></span>`
                    + r.filas.map((f, i) => `<span style="width:${pct(f.mes)};background:${COLORES[i % COLORES.length]}" title="${esc(f.nombre)} ${s(f.mes)}"></span>`).join('')
                    + (r.ahorro ? `<span class="seg-ahorro" style="width:${pct(r.ahorro)}" title="Ahorro ${s(r.ahorro)}"></span>` : '');

                const total = r.gastos + r.ahorro + (r.cuarto ?? r.tope);
                const fila = (color, nombre, mes, clase = '') => `<tr class="${clase}"><th scope="row"><i style="background:${color}"></i>${esc(nombre)}</th><td>${s(mes / P.SEMANAS)}</td><td>${s(mes)}</td><td>${r.ingresoMes ? Math.round(mes / r.ingresoMes * 100) : 0}%</td></tr>`;
                $('pTabla').innerHTML = fila('var(--verde-andino)', r.cuarto !== null ? 'Cuarto' : 'Cuarto (lo máximo)', cuartoBarra, 'fila-cuarto')
                    + r.filas.map((f, i) => fila(COLORES[i % COLORES.length], f.nombre, f.mes)).join('')
                    + (r.ahorro ? fila('repeating-linear-gradient(45deg,#3f7a6b 0 3px,#cfe3dc 3px 6px)', 'Ahorro', r.ahorro) : '');
                $('pTotal').innerHTML = `<tr><th scope="row">Total</th><td>${s(total / P.SEMANAS)}</td><td>${s(total)}</td><td></td></tr>`;

                // un consejo según el gasto más grande
                const mayor = [...r.filas].sort((a, b) => b.mes - a.mes)[0];
                const comida = r.filas.filter((f) => /men[uú]|almuerzo|desayuno|cena|comida/i.test(f.nombre)).reduce((a, f) => a + f.mes, 0);
                let consejo = '';
                if (comida > r.ingresoMes * 0.4) consejo = 'La comida se lleva más del 40 % de tu dinero. En los mercados San Pedro o Wanchaq el menú cuesta menos, y muchas universidades tienen comedor.';
                else if (mayor && mayor.mes > r.ingresoMes * 0.25) consejo = `Tu gasto más grande es «${esc(mayor.nombre)}» (${s(mayor.mes)} al mes). Si lo bajas un poco, ganas margen para el cuarto.`;
                else if (r.tope >= 450 || (r.cuarto !== null && r.queda > 150)) consejo = 'Vas bien. Separa tu ahorro apenas recibas el dinero y así no lo gastas sin darte cuenta.';
                else consejo = 'Un cuarto compartido (roomies) suele costar entre 30 y 40 % menos. En TAMPU puedes filtrarlos.';
                $('pConsejo').innerHTML = '<i class="bi bi-lightbulb"></i> ' + consejo;

                const tope = r.cuarto ?? r.tope;
                $('pVerCuartos').href = urlCuartos + (tope > 0 ? '?precio_max=' + tope : '');
                $('pVerCuartos').classList.toggle('disabled', tope <= 0);
            }

            // guardar: siempre en el navegador; con sesión también en la cuenta (un momento después de dejar de escribir)
            let espera;
            function cambio(redibujar) {
                if (redibujar) pintarFormulario();
                pintarResultado();
                P.guardarLocal(p);
                if (!conSesion) {
                    $('pEstado').innerHTML = '<i class="bi bi-check2"></i> Guardado en este navegador · <a href="' + urlLogin + '">inicia sesión</a> para tenerlo en tu cuenta';
                    return;
                }
                $('pEstado').textContent = 'Guardando…';
                clearTimeout(espera);
                espera = setTimeout(async () => {
                    try {
                        const resp = await fetch(urlGuardar, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                            body: JSON.stringify(p),
                        });
                        if (!resp.ok) throw new Error(resp.status);
                        window.TAMPU_PRESU = p;
                        $('pEstado').innerHTML = '<i class="bi bi-cloud-check"></i> Guardado en tu cuenta';
                    } catch (e) {
                        $('pEstado').innerHTML = '<i class="bi bi-exclamation-triangle"></i> No se pudo guardar en tu cuenta; quedó en este navegador';
                    }
                }, 1200);
            }

            const form = $('presuForm');
            form.addEventListener('input', (e) => {
                const el = e.target;
                if (el.dataset.campo) {
                    const it = p.items[+el.dataset.i];
                    it[el.dataset.campo] = el.dataset.campo === 'monto' ? Math.max(0, parseFloat(el.value) || 0) : el.value;
                } else if (el.id === 'pIngreso') p.ingreso = Math.max(0, parseFloat(el.value) || 0);
                else if (el.name === 'pPeriodo') p.periodo = el.value;
                else if (el.id === 'pAhorro') p.ahorro = Math.max(0, parseFloat(el.value) || 0);
                else if (el.id === 'pDias') p.dias = Math.min(7, Math.max(1, parseInt(el.value) || 5));
                else if (el.id === 'pCuarto') p.cuarto = el.value === '' ? null : Math.max(0, parseFloat(el.value) || 0);
                cambio(false);
            });
            form.addEventListener('click', (e) => {
                const quitar = e.target.closest('[data-quitar]');
                if (quitar) { p.items.splice(+quitar.dataset.quitar, 1); cambio(true); return; }
                const icono = e.target.closest('.presu-icono');
                if (icono) {
                    const it = p.items[+icono.dataset.i];
                    it.icono = ICONOS[(ICONOS.indexOf(it.icono) + 1) % ICONOS.length];
                    cambio(true);
                    form.querySelector(`.presu-icono[data-i="${icono.dataset.i}"]`)?.focus();
                    return;
                }
                const sug = e.target.closest('.presu-sugeridos button');
                if (sug) {
                    if (p.items.length >= 30) return;
                    p.items.push({ nombre: sug.dataset.nombre, monto: +sug.dataset.monto, cada: sug.dataset.cada, icono: sug.dataset.icono });
                    sug.hidden = true;
                    cambio(true);
                }
            });
            $('pAgregar').addEventListener('click', () => {
                if (p.items.length >= 30) return;
                p.items.push({ nombre: 'Nuevo gasto', monto: 0, cada: 'mes', icono: 'wallet2' });
                cambio(true);
                const campos = form.querySelectorAll('[data-campo=nombre]');
                campos[campos.length - 1]?.select();
            });
            $('pReiniciar').addEventListener('click', () => { p = P.porDefecto(); document.querySelectorAll('.presu-sugeridos button').forEach((b) => b.hidden = false); cambio(true); });
            $('pImprimir').addEventListener('click', () => window.print());

            // con sesión y sin nada en la cuenta: si había algo en este navegador, se sube
            pintarFormulario();
            pintarResultado();
            if (conSesion && !desdeServidor && local) cambio(false);
            else $('pEstado').innerHTML = desdeServidor ? '<i class="bi bi-cloud-check"></i> Guardado en tu cuenta' : '';
            document.querySelectorAll('.presu-sugeridos button').forEach((b) => { if (p.items.some((it) => it.nombre === b.dataset.nombre)) b.hidden = true; });
        })();

        // pestañas
        (function () {
            const tabs = [document.getElementById('tabMio'), document.getElementById('tabDistritos')];
            const mostrar = (t) => {
                tabs.forEach((x) => {
                    const activo = x === t;
                    x.setAttribute('aria-selected', activo);
                    x.tabIndex = activo ? 0 : -1;
                    document.getElementById(x.getAttribute('aria-controls')).hidden = !activo;
                });
                const url = new URL(location.href);
                url.searchParams.set('ver', t.id === 'tabMio' ? 'mio' : 'distritos');
                history.replaceState(null, '', url);
            };
            tabs.forEach((t, i) => {
                t.tabIndex = t.getAttribute('aria-selected') === 'true' ? 0 : -1;
                t.addEventListener('click', () => mostrar(t));
                t.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { const o = tabs[1 - i]; o.focus(); mostrar(o); }
                });
            });
        })();
    </script>
@endpush
