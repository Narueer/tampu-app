{{-- botón + ventana con el video guía (public/videos/guia-<cual>.mp4) --}}
@php($cual = $cual ?? 'visitante')
@if (file_exists(public_path("videos/guia-$cual.mp4")))
    <button type="button" class="{{ $clase ?? 'btn btn-borde' }}" data-bs-toggle="modal" data-bs-target="#videoGuia">
        <i class="bi bi-play-circle"></i> {{ $texto ?? 'Ver la guía en video' }}
    </button>
    @once
        @push('modales')
            <div class="modal fade" id="videoGuia" tabindex="-1" aria-label="Video guía" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content video-guia">
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        <video controls playsinline preload="none" poster="{{ asset("videos/guia-$cual.webp") }}">
                            <source src="{{ asset("videos/guia-$cual.mp4") }}" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>
            <script>
                // el video empieza al abrir la ventana y se detiene al cerrarla
                document.addEventListener('DOMContentLoaded', () => {
                    const m = document.getElementById('videoGuia'); const v = m.querySelector('video');
                    m.addEventListener('shown.bs.modal', () => v.play().catch(() => {}));
                    m.addEventListener('hide.bs.modal', () => v.pause());
                });
            </script>
        @endpush
    @endonce
@endif
