@if ($usuario->esEstudianteVerificado())
    <span class="insignia-uni" title="Comprobado con su correo {{ '@' . config('tampu.dominio_institucional') }}"><i class="bi bi-mortarboard-fill"></i> Estudiante verificado</span>
@endif
