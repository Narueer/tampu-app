@php($v = auth()->user()->verificacion)
@if ($v === 'pendiente')
    <p class="alerta alerta-aviso"><i class="bi bi-hourglass-split"></i> Tu cuenta está en revisión. Cuando el administrador verifique tu documento podrás publicar.</p>
@elseif ($v === 'rechazado')
    <p class="alerta alerta-error"><i class="bi bi-x-circle"></i> Tu documento fue rechazado. Escríbenos para volver a enviarlo.</p>
@endif
