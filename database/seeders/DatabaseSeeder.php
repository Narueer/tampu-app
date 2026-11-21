<?php

namespace Database\Seeders;

use App\Models\Conversacion;
use App\Models\Reporte;
use App\Models\Usuario;
use App\Notifications\Aviso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

// datos de prueba
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // si ya hay datos no se vuelve a llenar (en la nube se ejecuta en cada despliegue)
        if (Usuario::exists()) {
            return;
        }

        // documentos de prueba para que el admin pueda verlos
        foreach (['demo-pedro.png', 'demo-qhapaq.png'] as $doc) {
            if (! Storage::disk('documentos')->exists('documentos/' . $doc)) {
                Storage::disk('documentos')->put('documentos/' . $doc, file_get_contents(database_path('seeders/archivos/' . $doc)));
            }
        }

        // usuarios
        Usuario::create([
            'nombre' => 'Administrador TAMPU', 'email' => 'admin@tampu.pe', 'dni' => '70000001',
            'rol' => 'admin', 'password' => 'admin123',
        ]);

        $rosa = $this->usuario('Rosa Huamán Quispe', 'rosa@tampu.pe', '41234567', '984111222', 'arrendador');
        $julio = $this->usuario('Julio Ccori Mamani', 'julio@tampu.pe', '42345678', '984333444', 'arrendador');
        $carmen = $this->usuario('Carmen Mamani Huallpa', 'carmen@tampu.pe', '43456789', '984555666', 'arrendador');
        $this->usuario('Pedro Ccahuana Rojas', 'pedro@tampu.pe', '44567890', '984777888', 'arrendador', 'pendiente', 'documentos/demo-pedro.png');

        $wayra = $this->usuario('Lucía Ttito Condori', 'wayra@tampu.pe', '45678901', '974111222', 'empleador');
        $andes = $this->usuario('Marco Paredes Soto', 'andestech@tampu.pe', '46789012', '974333444', 'empleador');
        $inti = $this->usuario('Elena Condori Apaza', 'intiraymi@tampu.pe', '47890123', '974555666', 'empleador');
        $qhapaq = $this->usuario('Diego Huallpa Cusi', 'qhapaq@tampu.pe', '48901234', '974777888', 'empleador', 'pendiente', 'documentos/demo-qhapaq.png');

        $luis = $this->usuario('Luis Mamani Choque', 'luis@tampu.pe', '72345678', '951222333', 'estudiante', 'no_aplica', null, 'Sicuani');
        $ana = $this->usuario('Ana Quispe Ccasa', 'ana@tampu.pe', '73456789', '951444555', 'estudiante', 'no_aplica', null, 'Abancay');
        $rocio = $this->usuario('Rocío Apaza Layme', 'rocio@tampu.pe', '74567890', '951666777', 'estudiante', 'no_aplica', null, 'Puno');
        $kevin = $this->usuario('Kevin Huamán Cusi', 'kevin@tampu.pe', '75678901', '951888999', 'estudiante', 'no_aplica', null, 'Quillabamba');

        // arrendador con una publicacion sospechosa (para probar los reportes)
        $mario = $this->usuario('Mario Sullca Ttito', 'mario@tampu.pe', '49012345', '984999000', 'arrendador');

        // habitaciones (fotos de Unsplash)
        $h = [
            [$rosa, 'Habitación amoblada cerca de la U. Continental', 'Wanchaq', 'Av. Los Incas 512', 'U. Continental', 380, 'individual', 'Agua y luz, Wifi, Amoblado', 1,
                'Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente.'],
            [$julio, 'Cuarto económico en Santiago', 'Santiago', 'Jr. Arcopata 230', 'IST Túpac Amaru', 250, 'individual', 'Agua y luz, Wifi', 2,
                'Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. Cerca de paraderos.'],
            [$carmen, 'Habitación con baño propio – San Sebastián', 'San Sebastián', 'Calle Las Rosas 145', 'U. Andina', 450, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 3,
                'Habitación con baño propio y terma. Edificio con portón y vigilancia. A seis minutos caminando de la U. Andina.'],
            [$rosa, 'Cuarto cerca de la UNSAAC', 'Cusco', 'Av. de la Cultura 1020', 'UNSAAC', 320, 'individual', 'Agua y luz, Wifi, Cocina', 4,
                'Cuarto en casa familiar a cinco minutos de la UNSAAC. Se puede usar la cocina en horarios acordados.'],
            [$carmen, 'Minidepartamento para estudiante', 'San Sebastián', 'Urb. Tupac Amaru B-12', 'U. Andina', 550, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado', 5,
                'Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad.'],
            [$julio, 'Habitación compartida en Wanchaq', 'Wanchaq', 'Calle Tacna 340', 'U. Continental', 220, 'compartida', 'Agua y luz, Wifi, Lavandería', 6,
                'Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura y con tiendas cerca.'],
            [$rosa, 'Habitación amplia en el centro', 'Cusco', 'Calle Saphy 455', 'UNSAAC', 400, 'individual', 'Agua y luz, Wifi, Agua caliente', 7,
                'Habitación grande en casona del centro, con ventana a la calle y mucha luz natural.'],
            [$julio, 'Cuarto para estudiante – Santiago', 'Santiago', 'Av. Antonio Lorena 780', 'IST Túpac Amaru', 270, 'individual', 'Agua y luz, Lavandería', 8,
                'Cuarto con cama de plaza y media y ropero. Se aceptan estudiantes de institutos.'],
            [$carmen, 'Cuarto con vista – San Jerónimo', 'San Jerónimo', 'Av. La Cultura 2450', 'U. Andina', 280, 'individual', 'Agua y luz, Wifi', 9,
                'Cuarto en tercer piso con vista a los cerros. Movilidad directa a la U. Andina.'],
        ];

        // ubicacion aproximada de cada una
        $coordenadas = [
            [-13.5248, -71.9655], [-13.5185, -71.9858], [-13.5312, -71.9381],
            [-13.5221, -71.9588], [-13.5336, -71.9329], [-13.5232, -71.9682],
            [-13.5152, -71.9822], [-13.5226, -71.9831], [-13.5452, -71.8893],
        ];

        $habitaciones = [];
        foreach ($h as $i => $d) {
            $habitaciones[] = $d[0]->habitaciones()->create([
                'lat' => $coordenadas[$i][0], 'lng' => $coordenadas[$i][1],
                'titulo' => $d[1], 'distrito' => $d[2], 'direccion' => $d[3], 'cerca_de' => $d[4],
                'precio' => $d[5], 'tipo' => $d[6], 'servicios' => $d[7], 'foto' => 'img/habitaciones/h' . $d[8] . '.jpg',
                'descripcion' => $d[9],
            ]);
        }

        $sospechosa = $mario->habitaciones()->create([
            'titulo' => 'Cuarto amplio a solo S/ 150 en el centro', 'distrito' => 'Cusco', 'direccion' => 'Calle Nueva Alta 88',
            'cerca_de' => 'UNSAAC', 'lat' => -13.5195, 'lng' => -71.9745, 'precio' => 150, 'tipo' => 'individual',
            'servicios' => 'Agua y luz, Wifi', 'foto' => 'img/habitaciones/h10.jpg',
            'descripcion' => 'Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.',
        ]);

        // trabajos
        $repartidor = $this->trabajo($wayra, 'Repartidor en bicicleta', 'Minimarket Wayra', 'Santiago', 'medio tiempo', 40, 'dia',
            'Reparto de pedidos en la zona de Santiago. Se da la bicicleta.', ['martes-tarde', 'jueves-tarde', 'sabado-tarde']);
        $this->trabajo($andes, 'Soporte técnico sábados', 'Andes Tech SAC', 'Wanchaq', 'por horas', 70, 'dia',
            'Mantenimiento de computadoras e instalación de programas.', ['sabado-manana']);
        $this->trabajo($inti, 'Ayudante de cocina (mañanas)', 'Restaurante Inti Raymi', 'Cusco', 'medio tiempo', 50, 'dia',
            'Apoyo en la preparación de desayunos y almuerzos.', ['lunes-manana', 'martes-manana', 'miercoles-manana', 'jueves-manana', 'viernes-manana']);
        $noches = $this->trabajo($wayra, 'Atención en minimarket (noches)', 'Minimarket Wayra', 'San Sebastián', 'medio tiempo', 45, 'dia',
            'Atención al cliente y caja en turno de noche.', ['lunes-noche', 'martes-noche', 'miercoles-noche', 'jueves-noche', 'viernes-noche']);
        $practicas = $this->trabajo($andes, 'Practicante de sistemas', 'Andes Tech SAC', 'Wanchaq', 'practicas', 1025, 'mes',
            'Apoyo en desarrollo web y soporte a usuarios. Para estudiantes de Ingeniería de Sistemas.', ['lunes-tarde', 'miercoles-tarde', 'viernes-tarde']);
        $mozo = $this->trabajo($inti, 'Mozo/a fin de semana', 'Restaurante Inti Raymi', 'Cusco', 'medio tiempo', 60, 'dia',
            'Atención en mesas los fines de semana. Se valora inglés básico.', ['sabado-tarde', 'sabado-noche', 'domingo-tarde', 'domingo-noche']);
        // empresa sin verificar
        $this->trabajo($qhapaq, 'Guía de apoyo turístico', 'Qhapaq Tours', 'Cusco', 'por horas', 80, 'dia',
            'Acompañar a grupos de turistas en el centro histórico.', ['sabado-manana', 'sabado-tarde']);

        // horario de clases de Luis
        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            $luis->horarios()->create(['dia' => $dia, 'turno' => 'manana']);
        }
        $luis->horarios()->create(['dia' => 'lunes', 'turno' => 'tarde']);
        $luis->horarios()->create(['dia' => 'miercoles', 'turno' => 'tarde']);

        // solicitudes de visita
        $habitaciones[3]->solicitudes()->create([
            'usuario_id' => $ana->id, 'fecha_visita' => now()->addDays(2)->toDateString(),
            'mensaje' => 'Hola, soy de Abancay y estudio en la UNSAAC. ¿Puedo ir el sábado?', 'estado' => 'aceptada',
        ]);
        $habitaciones[5]->solicitudes()->create([
            'usuario_id' => $ana->id, 'fecha_visita' => now()->addDays(4)->toDateString(),
            'mensaje' => '¿La habitación compartida es solo para mujeres?', 'estado' => 'pendiente',
        ]);

        // visitas que ya pasaron (permiten dejar reseña)
        $this->visita($habitaciones[0], $luis, -6, 'Quisiera verla antes de mudarme.');
        $this->visita($habitaciones[0], $rocio, -10, 'Hola, estudio en la Continental.');
        $this->visita($habitaciones[2], $kevin, -8, '¿Puedo ir por la tarde?');
        $this->visita($habitaciones[1], $rocio, -12, 'Me interesa, ¿sigue disponible?');
        $this->visita($habitaciones[6], $ana, -3, 'Hola, ¿puedo verla el lunes?');

        // reseñas
        $habitaciones[0]->resenas()->create(['usuario_id' => $luis->id, 'estrellas' => 5,
            'comentario' => 'La habitación es igual que en las fotos. La señora Rosa es muy amable y la zona es tranquila.']);
        $habitaciones[0]->resenas()->create(['usuario_id' => $rocio->id, 'estrellas' => 4,
            'comentario' => 'Buena habitación y cerca de la universidad. El wifi a veces falla en las noches.']);
        $habitaciones[2]->resenas()->create(['usuario_id' => $kevin->id, 'estrellas' => 5,
            'comentario' => 'El baño propio y el agua caliente valen la pena. Muy seguro el edificio.']);
        $habitaciones[1]->resenas()->create(['usuario_id' => $rocio->id, 'estrellas' => 3,
            'comentario' => 'Es económico pero el baño compartido es pequeño. Para el precio está bien.']);

        // postulaciones
        $repartidor->postulaciones()->create(['usuario_id' => $luis->id, 'estado' => 'aceptada',
            'mensaje' => 'Tengo bicicleta propia y conozco bien Santiago.']);
        $mozo->postulaciones()->create(['usuario_id' => $luis->id, 'mensaje' => 'Tengo inglés básico.']);
        $practicas->postulaciones()->create(['usuario_id' => $ana->id, 'mensaje' => 'Estoy en sexto ciclo de Ingeniería de Sistemas.']);
        $noches->postulaciones()->create(['usuario_id' => $kevin->id, 'estado' => 'aceptada']);
        $repartidor->postulaciones()->create(['usuario_id' => $rocio->id, 'estado' => 'rechazada']);

        // conversaciones
        $this->conversacion($ana, $rosa, ['habitacion_id' => $habitaciones[3]->id], [
            [$ana, 'Hola señora Rosa, ¿el cuarto tiene escritorio?'],
            [$rosa, 'Hola Ana, sí tiene escritorio y una silla. Te espero el día de la visita.'],
            [$ana, 'Perfecto, muchas gracias.'],
        ]);
        $this->conversacion($luis, $wayra, ['trabajo_id' => $repartidor->id], [
            [$luis, 'Buenas tardes, ¿qué días empezaría?'],
            [$wayra, 'Hola Luis, puedes empezar este martes a las 2 pm en el local de Santiago.'],
        ]);

        // reporte pendiente
        Reporte::create([
            'usuario_id' => $kevin->id, 'habitacion_id' => $sospechosa->id, 'motivo' => 'Pide adelanto sin visita',
            'detalle' => 'Me pidió depositar 300 soles para separar el cuarto sin dejarme verlo.',
        ]);

        // algunas notificaciones de ejemplo
        $luis->notify(new Aviso('Minimarket Wayra aceptó tu postulación a "Repartidor en bicicleta"', route('estudiante.panel', [], false), 'briefcase'));
        $julio->notify(new Aviso('Ana Quispe Ccasa quiere visitar "Habitación compartida en Wanchaq"', route('arrendador.solicitudes', [], false), 'house-door'));

        // las cuentas de prueba ya tienen el correo confirmado
        Usuario::query()->update(['email_verificado_at' => now()]);

        // mas habitaciones con galeria, mas trabajos y ubicaciones revisadas
        $this->call(AmpliarDatosSeeder::class);
        $this->call(MasDatosSeeder::class);
    }

    private function visita($habitacion, $estudiante, $dias, $mensaje)
    {
        $habitacion->solicitudes()->create([
            'usuario_id' => $estudiante->id, 'fecha_visita' => now()->addDays($dias)->toDateString(),
            'mensaje' => $mensaje, 'estado' => 'aceptada',
        ]);
    }

    private function conversacion($a, $b, $tema, $mensajes)
    {
        $c = Conversacion::create(['usuario_a_id' => $a->id, 'usuario_b_id' => $b->id] + $tema);
        foreach ($mensajes as $i => [$autor, $texto]) {
            $c->mensajes()->create(['usuario_id' => $autor->id, 'texto' => $texto, 'leido' => $i < count($mensajes) - 1]);
        }
    }

    private function usuario($nombre, $email, $dni, $celular, $rol, $verificacion = 'verificado', $documento = null, $procedencia = null)
    {
        if ($rol === 'estudiante') {
            $verificacion = 'no_aplica';
        }

        return Usuario::create([
            'nombre' => $nombre, 'email' => $email, 'dni' => $dni, 'celular' => $celular,
            'procedencia' => $procedencia, 'rol' => $rol, 'verificacion' => $verificacion,
            'documento' => $documento, 'password' => '123456',
        ]);
    }

    private function trabajo($empleador, $titulo, $empresa, $distrito, $modalidad, $pago, $pagoPor, $descripcion, $turnos)
    {
        $trabajo = $empleador->trabajos()->create([
            'titulo' => $titulo, 'empresa' => $empresa, 'distrito' => $distrito, 'modalidad' => $modalidad,
            'pago' => $pago, 'pago_por' => $pagoPor, 'descripcion' => $descripcion,
        ]);

        foreach ($turnos as $t) {
            [$dia, $turno] = explode('-', $t);
            $trabajo->horarios()->create(['dia' => $dia, 'turno' => $turno]);
        }

        return $trabajo;
    }
}
