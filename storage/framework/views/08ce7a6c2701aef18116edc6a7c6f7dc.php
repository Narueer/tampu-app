<!-- asistente virtual -->
<button id="btnAsistente" class="btn-asistente" aria-label="Abrir el asistente virtual" aria-expanded="false" aria-controls="asistente">
    <img src="<?php echo e(asset('img/asistente/tampu-bot.webp')); ?>" alt="" width="60" height="60">
    <span class="asistente-punto" aria-hidden="true"></span>
</button>
<div id="asistenteAviso" class="asistente-aviso oculto" role="status">
    <button type="button" class="asistente-aviso-cerrar" aria-label="Cerrar aviso">&times;</button>
    ¡Hola! Soy <strong>Tampu</strong>. ¿Te ayudo a encontrar cuarto o trabajo?
</div>

<div id="asistente" class="asistente oculto" data-url="<?php echo e(route('asistente')); ?>" data-avatar="<?php echo e(asset('img/asistente/tampu-bot.webp')); ?>" role="dialog" aria-label="Asistente virtual TAMPU">
    <div class="asistente-cabecera">
        <img src="<?php echo e(asset('img/asistente/tampu-bot.webp')); ?>" alt="" class="asistente-avatar" width="44" height="44">
        <div class="asistente-titulo">
            <strong>Tampu</strong>
            <small><span class="en-linea"></span> Asistente virtual · en línea</small>
        </div>
        <button id="cerrarAsistente" class="asistente-cerrar" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
    </div>
    <div id="asistenteMensajes" class="asistente-mensajes" aria-live="polite">
        <div class="asistente-bienvenida">
            <img src="<?php echo e(asset('img/asistente/tampu-medallon.webp')); ?>" alt="Tampu, asistente virtual" width="104" height="98">
            <p>Te ayudo a encontrar <strong>cuarto</strong> o <strong>trabajo</strong> en Cusco y a evitar estafas.</p>
        </div>
        <div class="fila-bot">
            <img src="<?php echo e(asset('img/asistente/tampu-bot.webp')); ?>" alt="" class="mini-avatar" width="28" height="28">
            <div class="burbuja bot">¡Hola! 👋 ¿Qué estás buscando hoy?</div>
        </div>
        <div class="sugerencias">
            <button type="button"><i class="bi bi-house-door"></i>Cuartos en Wanchaq hasta 400</button>
            <button type="button"><i class="bi bi-people"></i>Busco roomies</button>
            <button type="button"><i class="bi bi-briefcase"></i>Trabajo los fines de semana</button>
            <button type="button"><i class="bi bi-cash-coin"></i>Precios por distrito</button>
            <button type="button"><i class="bi bi-shield-check"></i>¿Cómo evito estafas?</button>
            <button type="button"><i class="bi bi-headset"></i>Hablar con un asesor</button>
        </div>
    </div>
    <form id="asistenteForm" class="asistente-form">
        <input type="text" id="asistenteTexto" placeholder="Escribe tu pregunta..." maxlength="200" autocomplete="off" aria-label="Tu pregunta">
        <button type="submit" aria-label="Enviar"><i class="bi bi-send-fill"></i></button>
    </form>
    <p class="asistente-pie">Respuestas automáticas · no compartas contraseñas</p>
</div>
<?php /**PATH C:\Users\User\Desktop\Tampu\resources\views/partials/asistente.blade.php ENDPATH**/ ?>