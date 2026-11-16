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
                        <label>Correo</label>
                        <input type="email" value="{{ $usuario->email }}" disabled>
                    </div>
                    <div>
                        <label>DNI</label>
                        <input type="text" value="{{ $usuario->dni }}" disabled>
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

                <p class="texto-suave">Cuenta de {{ $usuario->rol }} · @include('partials.estado', ['estado' => $usuario->verificacion])</p>
                <button type="submit" class="btn">Guardar cambios</button>
            </form>

            <form class="caja" action="{{ route('perfil.clave') }}" method="POST">
                @csrf
                @method('PUT')
                <h2><i class="bi bi-key"></i> Cambiar contraseña</h2>
                <label for="actual">Contraseña actual</label>
                <input type="password" id="actual" name="actual" required>
                <label for="nueva">Nueva contraseña</label>
                <input type="password" id="nueva" name="password" minlength="6" required>
                <label for="nueva2">Repite la nueva contraseña</label>
                <input type="password" id="nueva2" name="password_confirmation" minlength="6" required>
                <button type="submit" class="btn">Cambiar contraseña</button>
            </form>
        </div>
    </main>
@endsection
