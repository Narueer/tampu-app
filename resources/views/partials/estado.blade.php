@php
    $clases = [
        'pendiente' => 'est-pendiente', 'aceptada' => 'est-ok', 'rechazada' => 'est-mal',
        'verificado' => 'est-ok', 'rechazado' => 'est-mal', 'no_aplica' => 'est-gris',
        'disponible' => 'est-ok', 'ocupada' => 'est-gris', 'activa' => 'est-ok', 'cerrada' => 'est-gris',
        'descartado' => 'est-gris', 'sancionado' => 'est-mal',
    ];
    $textos = ['no_aplica' => 'No aplica'];
@endphp
<span class="estado {{ $clases[$estado] ?? 'est-gris' }}">{{ $textos[$estado] ?? ucfirst($estado) }}</span>
