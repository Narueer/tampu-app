@extends('layouts.app')

@section('titulo', 'Mi perfil')

@section('contenido')
    <main class="contenedor">
        @include('partials.subnav')

        <h1 class="titulo-pagina">Mi perfil</h1>
        @include('partials.errores')

        <div class="perfil">
            <form class="caja" action="{{ route('perfil') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <h2><i class="bi bi-person"></i> Mis datos</h2>

                <div class="perfil-foto">
                    @if ($usuario->foto)
                        <img src="{{ $usuario->fotoUrl() }}" alt="Mi foto" id="vistaPrevia" class="avatar-grande">
                    @else
                        <span class="avatar-grande avatar-letras">{{ $usuario->iniciales() }}</span>
                        <img src="" alt="Vista previa" id="vistaPrevia" class="avatar-grande oculto">
                    @endif
                    <div>
                        <label for="foto">Foto de perfil</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <p class="texto-suave">JPG o PNG, máximo 2 MB.</p>
                    </div>
                </div>

                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required>

                <div class="fila">
                    <div>
                        <label for="correoActual">Correo</label>
                        <input type="email" id="correoActual" value="{{ $usuario->email }}" disabled>
                    </div>
                    <div>
                        <label for="dniActual">DNI</label>
                        <input type="text" id="dniActual" value="{{ $usuario->dni }}" disabled>
                    </div>
                </div>

                <div class="fila">
                    <div>
                        <label for="celular">Celular</label>
                        <input type="text" id="celular" name="celular" value="{{ old('celular', $usuario->celular) }}" maxlength="9">
                    </div>
                    @if ($usuario->rol === 'estudiante')
                        <div>
                            <label for="procedencia">¿De dónde eres?</label>
                            <input type="text" id="procedencia" name="procedencia" value="{{ old('procedencia', $usuario->procedencia) }}" maxlength="60">
                        </div>
                    @endif
                </div>

                <label class="interruptor">
                    <input type="checkbox" name="avisos_correo" value="1" @checked(old('avisos_correo', $usuario->avisos_correo ?? true))>
                    <span class="interruptor-pista" aria-hidden="true"></span>
                    <span><b>Avisos por correo</b> Te escribimos cuando acepten tu visita o postulación, cuando te pidan una visita o cuando verifiquen tu cuenta.</span>
                </label>

                <p class="texto-suave">Cuenta de {{ $usuario->rol }} · @include('partials.estado', ['estado' => $usuario->verificacion]) @include('partials.insignia-uni')</p>
                <button type="submit" class="btn">Guardar cambios</button>
            </form>

            <form class="caja" action="{{ route('perfil.clave') }}" method="POST">
                @csrf
                @method('PUT')
                <h2><i class="bi bi-key"></i> Cambiar contraseña</h2>
                <label for="actual">Contraseña actual</label>
                <input type="password" id="actual" name="actual" required>
                <label for="nueva">Nueva contraseña</label>
                <input type="password" id="nueva" name="password" minlength="8" required>
                <small class="ayuda">Mínimo 8 caracteres, con letras y números.</small>
                <label for="nueva2">Repite la nueva contraseña</label>
                <input type="password" id="nueva2" name="password_confirmation" minlength="8" required>
                <button type="submit" class="btn">Cambiar contraseña</button>
            </form>

            <div class="caja actividad">
                <h2><i class="bi bi-person-check"></i> Seguridad de la cuenta</h2>

                @if (session('respaldo'))
                    <div class="alerta alerta-aviso respaldo">
                        <b>Guarda estos códigos de respaldo</b> (anótalos o toma captura). Cada uno sirve una sola vez si pierdes el celular. No los volveremos a mostrar.
                        <ul>@foreach (session('respaldo') as $c)<li><code>{{ $c }}</code></li>@endforeach</ul>
                    </div>
                @endif

                <div class="seguridad-fila">
                    <div>
                        <b><i class="bi bi-envelope-check"></i> Correo</b>
                        <span class="texto-suave">{{ $usuario->email }}</span>
                    </div>
                    @if ($usuario->correoConfirmado())
                        <span class="estado est-ok">Confirmado</span>
                    @elseif (config('tampu.verificar_correo'))
                        <a href="{{ route('correo.verificar') }}" class="btn btn-borde">Confirmar</a>
                    @else
                        <span class="estado est-gris">Sin confirmar</span>
                    @endif
                </div>

                @if ($usuario->rol === 'estudiante')
                    <div class="seguridad-fila">
                        <div>
                            <b><i class="bi bi-mortarboard"></i> Correo de la universidad</b>
                            <span class="texto-suave">
                                @if ($usuario->correo_institucional)
                                    {{ $usuario->correo_institucional }}. Arrendadores y empresas te ven como estudiante verificado.
                                @else
                                    Vincula tu correo {{ '@' . config('tampu.dominio_institucional') }} para aparecer como estudiante verificado.
                                @endif
                            </span>
                        </div>
                        @if ($usuario->correo_institucional)
                            @include('partials.insignia-uni')
                        @else
                            <div class="botones-fila">
                                @forelse (collect(config('tampu.oauth'))->filter(fn ($p) => $p['id']) as $clave => $p)
                                    <a href="{{ route('externo.ir', $clave) }}" class="btn-externo"><i class="bi bi-{{ $clave }}"></i> {{ $p['nombre'] }}</a>
                                @empty
                                    <span class="texto-suave">Disponible cuando se configure Google o Microsoft.</span>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif

                <div class="seguridad-fila">
                    <div>
                        <b><i class="bi bi-phone"></i> Verificación en dos pasos</b>
                        <span class="texto-suave">
                            @if ($usuario->dos_pasos_activo)
                                Activa. Al entrar te pedimos el código de tu app autenticadora. Te quedan {{ count($usuario->dos_pasos_codigos ?? []) }} códigos de respaldo.
                            @else
                                Además de la contraseña, pide un código de tu celular.{{ $usuario->rol === 'admin' ? ' Recomendada para el administrador.' : '' }}
                            @endif
                        </span>
                    </div>
                    @if ($usuario->dos_pasos_activo)
                        <form action="{{ route('dos-pasos.desactivar') }}" method="POST" class="form-desactivar">
                            @csrf
                            @method('DELETE')
                            <input type="password" name="clave" placeholder="Tu contraseña" aria-label="Tu contraseña" required>
                            <button class="btn btn-borde">Desactivar</button>
                        </form>
                    @else
                        <a href="{{ route('dos-pasos.activar') }}" class="btn">Activar</a>
                    @endif
                </div>
            </div>

            <div class="caja actividad">
                <h2><i class="bi bi-shield-check"></i> Actividad de tu cuenta</h2>
                <p class="texto-suave">Los últimos movimientos de tu cuenta. Si ves algo que no hiciste, cambia tu contraseña.</p>
                @forelse ($actividad as $e)
                    <div class="actividad-fila {{ $e->esAlerta() ? 'fila-alerta' : '' }}">
                        <span>{{ $e->nombreTipo() }}</span>
                        <small>{{ $e->created_at->format('d/m/Y H:i') }} · {{ $e->ip }}</small>
                    </div>
                @empty
                    <p class="vacio">Aún no hay movimientos registrados.</p>
                @endforelse
            </div>
        </div>
    </main>
@endsection
