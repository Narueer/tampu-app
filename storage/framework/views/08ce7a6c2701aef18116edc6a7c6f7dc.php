<!-- asistente virtual -->
<button id="btnAsistente" class="btn-asistente" aria-label="Abrir asistente"><i class="bi bi-chat-heart"></i></button>

<div id="asistente" class="asistente oculto" data-url="<?php echo e(route('asistente')); ?>">
    <div class="asistente-cabecera">
        <span><i class="bi bi-robot"></i> Asistente TAMPU</span>
        <button id="cerrarAsistente" aria-label="Cerrar">&times;</button>
    </div>
    <div id="asistenteMensajes" class="asistente-mensajes">
        <div class="burbuja bot">¡Hola! Te ayudo a encontrar cuarto o trabajo. ¿Qué buscas?</div>
        <div class="sugerencias">
            <button type="button">Cuartos en Wanchaq hasta 400</button>
            <button type="button">Trabajos compatibles</button>
            <button type="button">¿Cómo evito estafas?</button>
            <button type="button">Hablar con un asesor</button>
        </div>
    </div>
    <form id="asistenteForm" class="asistente-form">
        <input type="text" id="asistenteTexto" placeholder="Escribe tu pregunta..." maxlength="200" autocomplete="off">
        <button type="submit" aria-label="Enviar"><i class="bi bi-send"></i></button>
    </form>
</div>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/asistente.blade.php ENDPATH**/ ?>