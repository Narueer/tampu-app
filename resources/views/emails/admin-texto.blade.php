{{ $titulo }}

@foreach ($filas as $campo => $valor)
{{ $campo }}: {{ $valor }}
@endforeach

@if ($mensaje)
{{ $mensaje }}

@endif
@if ($url)
{{ $boton }}: {{ $url }}
@endif
