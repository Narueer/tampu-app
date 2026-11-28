@extends('emails.base')

@section('contenido')
    @foreach ($lineas as $linea)
        @php
            // la línea de "no pagues adelanto" va resaltada como consejo
            $consejo = str_starts_with($linea, 'Recuerda');
        @endphp
        @if ($consejo)
            @include('emails.partes.nota', ['color' => '#b58a4a', 'titulo' => 'Consejo', 'html' => e(\Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::after($linea, 'Recuerda: ')))])
        @else
            @include('emails.partes.parrafo', ['html' => e($linea)])
        @endif
    @endforeach

    @include('emails.partes.boton', ['url' => $url, 'texto' => $boton])
@endsection
