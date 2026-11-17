<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

// mas habitaciones, galerias de fotos, mas trabajos y ubicaciones revisadas en OpenStreetMap.
// Se puede ejecutar varias veces: si ya se cargo, no hace nada.
class AmpliarDatosSeeder extends Seeder
{
    const MARCA = 'Cuarto cerca de la U. Continental – Angostura';

    const G = 'img/habitaciones/galeria/';

    public function run(): void
    {
        if (! Usuario::where('email', 'rosa@tampu.pe')->exists() || Habitacion::where('titulo', self::MARCA)->exists()) {
            return;
        }

        $u = fn ($email) => Usuario::where('email', $email)->first();

        // nuevos arrendadores, empresas y estudiantes
        $hilda = $this->usuario('Hilda Quispe Ttito', 'hilda@tampu.pe', '41987654', '984222111', 'arrendador');
        $fredy = $this->usuario('Fredy Callañaupa Huamán', 'fredy@tampu.pe', '42876543', '984444333', 'arrendador');
        $norma = $this->usuario('Norma Álvarez Puma', 'norma@tampu.pe', '43765432', '984666555', 'arrendador');
        $kuntur = $this->usuario('Raúl Quispe Mayta', 'kuntur@tampu.pe', '45123987', '974222111', 'empleador');
        $nusta = $this->usuario('Patricia Rojas Huamán', 'nustawasi@tampu.pe', '46234876', '974444333', 'empleador');
        $saqsay = $this->usuario('Sonia Choque Pari', 'saqsay@tampu.pe', '47345765', '974666555', 'empleador');
        $yhon = $this->usuario('Yhon Ccama Huanca', 'yhon@tampu.pe', '76789012', '952111222', 'estudiante', 'Espinar');
        $milagros = $this->usuario('Milagros Ttito Roque', 'milagros@tampu.pe', '77890123', '952333444', 'estudiante', 'Urubamba');
        $brayan = $this->usuario('Brayan Huillca Soncco', 'brayan@tampu.pe', '78901234', '952555666', 'estudiante', 'Andahuaylas');

        $duenos = [
            'rosa' => $u('rosa@tampu.pe'), 'julio' => $u('julio@tampu.pe'), 'carmen' => $u('carmen@tampu.pe'),
            'mario' => $u('mario@tampu.pe'), 'hilda' => $hilda, 'fredy' => $fredy, 'norma' => $norma,
        ];

        // [dueño, titulo, titulo anterior, distrito, direccion, cerca de, lat, lng, precio, tipo, servicios, m2, piso, baño, garantia, foto, galeria, descripcion]
        $cuartos = [
            ['rosa', 'Habitación amoblada en Av. Pachacútec', 'Habitación amoblada cerca de la U. Continental', 'Wanchaq', 'Av. Pachacútec 506', 'UNSAAC',
                -13.5242495, -71.9696114, 380, 'individual', 'Agua y luz, Wifi, Amoblado, Agua caliente', 14, 2, 'compartido', 380, 'img/habitaciones/h1.jpg',
                ['dormitorio-02' => 'Escritorio junto a la ventana', 'bano-03' => 'Baño compartido con terma', 'cocina-02' => 'Cocina de la casa'],
                'Habitación amplia y luminosa en segundo piso, con escritorio, cama de 1.5 plazas y ropero. Ambiente tranquilo para estudiar. Ingreso independiente y paradero de buses a la UNSAAC en la esquina.'],
            ['julio', 'Cuarto económico en Santiago', null, 'Santiago', 'Calle Tambopata 230, Urb. Roccopata', 'UNSAAC',
                -13.5238400, -71.9829900, 250, 'individual', 'Agua y luz, Wifi', 10, 1, 'compartido', 250, 'img/habitaciones/h2.jpg',
                ['bano-01' => 'Baño compartido', 'lavanderia-01' => 'Zona de lavado'],
                'Cuarto sencillo con cama, mesa y estante. Baño compartido con otro estudiante. A dos cuadras del Mercado de San Pedro y cerca de los paraderos de la Av. Ejército.'],
            ['carmen', 'Habitación con baño propio – San Sebastián', null, 'San Sebastián', 'Calle Kiswar 145, Urb. Túpac Amaru', 'U. Andina',
                -13.5347800, -71.9087300, 450, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 16, 3, 'propio', 450, 'img/habitaciones/h3.jpg',
                ['bano-02' => 'Baño propio con ducha', 'dormitorio-11' => 'Otra vista del cuarto', 'estudio-01' => 'Zona de estudio'],
                'Habitación con baño propio y terma. Edificio con portón y vigilancia. A unos ocho minutos caminando del campus Larapa de la U. Andina.'],
            ['rosa', 'Cuarto cerca de la UNSAAC', null, 'Cusco', 'Pasaje Inca Roca 120, Urb. Manuel Prado', 'UNSAAC',
                -13.5207000, -71.9553900, 320, 'individual', 'Agua y luz, Wifi, Cocina', 12, 2, 'compartido', 320, 'img/habitaciones/h4.jpg',
                ['cocina-05' => 'Cocina compartida', 'dormitorio-07' => 'Cama y ropero'],
                'Cuarto en casa familiar a cinco minutos caminando de la UNSAAC y frente al Real Plaza. Se puede usar la cocina en horarios acordados.'],
            ['carmen', 'Minidepartamento para estudiante', null, 'San Sebastián', 'Urb. Túpac Amaru Mz. B Lt. 12', 'U. Andina',
                -13.5387800, -71.9070200, 550, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente', 28, 2, 'propio', 550, 'img/habitaciones/h5.jpg',
                ['cocina-01' => 'Kitchenette', 'bano-02' => 'Baño propio', 'dormitorio-05' => 'Dormitorio', 'estudio-01' => 'Sala de estudio'],
                'Minidepartamento con kitchenette, baño propio y sala de estudio. Ideal para quien busca privacidad. Cerca de la Vía Expresa y de la U. Andina.'],
            ['julio', 'Habitación compartida en Wanchaq', null, 'Wanchaq', 'Jr. La Convención B-17', 'UNSAAC',
                -13.5274600, -71.9635100, 220, 'compartida', 'Agua y luz, Wifi, Lavandería', 20, 1, 'compartido', 220, 'img/habitaciones/h6.jpg',
                ['dormitorio-13' => 'Cada uno con su cama', 'lavanderia-01' => 'Lavadora de la casa'],
                'Habitación para dos estudiantes, cada uno con su cama y escritorio. Zona segura, cerca del Mercado de Wanchaq y con tiendas alrededor.'],
            ['rosa', 'Habitación amplia en el centro', null, 'Cusco', 'Calle Pumacurco 490, San Cristóbal', 'UNSAAC',
                -13.5132800, -71.9780400, 400, 'individual', 'Agua y luz, Wifi, Agua caliente', 18, 2, 'compartido', 400, 'img/habitaciones/h7.jpg',
                ['dormitorio-09' => 'Ventana a la calle', 'bano-01' => 'Baño con agua caliente'],
                'Habitación grande en casona del barrio de San Cristóbal, con ventana a la calle y mucha luz natural. A cinco cuadras de la Plaza de Armas.'],
            ['julio', 'Cuarto para estudiante – Santiago', null, 'Santiago', 'Av. Antonio Lorena 780, Dignidad Nacional', 'UNSAAC',
                -13.5256400, -71.9882000, 270, 'individual', 'Agua y luz, Lavandería', 11, 2, 'compartido', 270, 'img/habitaciones/h8.jpg',
                ['lavanderia-01' => 'Lavandería', 'dormitorio-06' => 'Ropero y cama'],
                'Cuarto con cama de plaza y media y ropero, frente al Hospital Antonio Lorena. Pasan varias líneas de buses hacia la Av. de la Cultura.'],
            ['carmen', 'Cuarto con vista – San Jerónimo', null, 'San Jerónimo', 'Calle Clorinda Matto de Turner 210', 'U. Andina',
                -13.5415800, -71.8838000, 280, 'individual', 'Agua y luz, Wifi', 12, 3, 'compartido', 280, 'img/habitaciones/h9.jpg',
                ['dormitorio-14' => 'Mucha luz por la mañana', 'bano-03' => 'Baño del piso'],
                'Cuarto en tercer piso con vista a los cerros, cerca de la plaza de San Jerónimo. Buses directos a la U. Andina y a la U. Continental.'],
            ['mario', 'Cuarto amplio a solo S/ 150 en el centro', null, 'Cusco', 'Mesón de la Estrella 175', 'UNSAAC',
                -13.5198000, -71.9802000, 150, 'individual', 'Agua y luz, Wifi', null, null, null, null, 'img/habitaciones/h10.jpg', [],
                'Cuarto grande, quedan pocos. Para separarlo deposita 300 soles al número de cuenta que te paso por mensaje.'],

            // nuevas
            ['hilda', 'Cuarto con escritorio frente al Real Plaza', null, 'Cusco', 'Av. Collasuyo 912, Urb. Manuel Prado', 'UNSAAC',
                -13.5205400, -71.9552700, 350, 'individual', 'Agua y luz, Wifi, Amoblado, Escritorio', 13, 3, 'compartido', 350, self::G . 'dormitorio-03.jpg',
                ['estudio-01' => 'Escritorio amplio', 'bano-01' => 'Baño del piso', 'cocina-04' => 'Cocina compartida'],
                'Cuarto amoblado en tercer piso, con escritorio grande y buena señal de wifi. A cuatro cuadras de la UNSAAC y frente al Real Plaza.'],
            ['hilda', 'Habitación en Mariscal Gamarra', null, 'Cusco', 'Parque Magisterio 21, Urb. Mariscal Gamarra', 'UNSAAC',
                -13.5211200, -71.9623000, 300, 'individual', 'Agua y luz, Wifi, Agua caliente', 12, 2, 'compartido', 300, self::G . 'dormitorio-12.jpg',
                ['bano-03' => 'Baño con terma', 'dormitorio-10' => 'Otra vista'],
                'Habitación en casa familiar al lado del Parque del Magisterio. Barrio tranquilo, ideal para estudiar. La UNSAAC queda a diez minutos caminando.'],
            ['fredy', self::MARCA, null, 'Saylla', 'Sector Angostura, Mz. C Lt. 8', 'U. Continental',
                -13.5551700, -71.8558700, 300, 'individual', 'Agua y luz, Wifi, Agua caliente', 14, 2, 'compartido', 300, self::G . 'dormitorio-01.jpg',
                ['dormitorio-08' => 'Cama de plaza y media', 'bano-01' => 'Baño compartido', 'cocina-03' => 'Cocina'],
                'Cuarto a cinco minutos caminando del campus de la U. Continental en Angostura. Sin perder tiempo en el tráfico de la Vía Expresa.'],
            ['fredy', 'Minidepartamento en Saylla', null, 'Saylla', 'Carretera Cusco–Urcos km 10', 'U. Continental',
                -13.5565700, -71.8548700, 520, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cochera', 30, 1, 'propio', 520, self::G . 'dormitorio-04.jpg',
                ['cocina-02' => 'Kitchenette equipada', 'bano-02' => 'Baño propio', 'estudio-01' => 'Zona de estudio'],
                'Minidepartamento nuevo con cocina, baño propio y espacio para bicicleta o moto. A cinco minutos de la U. Continental.'],
            ['fredy', 'Habitación en Oropesa Huaylla', null, 'San Jerónimo', 'Asoc. Oropesa Huaylla Mz. F Lt. 3', 'U. Continental',
                -13.5515400, -71.8584000, 260, 'individual', 'Agua y luz, Wifi', 11, 2, 'compartido', 260, self::G . 'dormitorio-06.jpg',
                ['bano-03' => 'Baño compartido'],
                'Cuarto sencillo en casa nueva, a unos diez minutos caminando de la U. Continental. Hay tiendas y restaurante de menú en la misma cuadra.'],
            ['norma', 'Cuarto en Larapa a pasos de la U. Andina', null, 'San Jerónimo', 'Av. Villa del Carmen 340, La Arboleda', 'U. Andina',
                -13.5375400, -71.9020900, 330, 'individual', 'Agua y luz, Wifi, Amoblado, Agua caliente', 13, 2, 'compartido', 330, self::G . 'dormitorio-07.jpg',
                ['bano-01' => 'Baño con terma', 'cocina-04' => 'Cocina compartida', 'dormitorio-13' => 'Ropero empotrado'],
                'Cuarto amoblado a dos cuadras del campus Larapa de la U. Andina. Casa con portón eléctrico y patio.'],
            ['norma', 'Habitación compartida en El Tingo', null, 'San Jerónimo', 'Calle Manantial 118, El Tingo', 'U. Andina',
                -13.5392700, -71.9022300, 200, 'compartida', 'Agua y luz, Wifi, Lavandería', 18, 1, 'compartido', 200, self::G . 'dormitorio-10.jpg',
                ['lavanderia-01' => 'Lavadora', 'cocina-05' => 'Cocina'],
                'Habitación para dos estudiantes de la U. Andina. Cada uno tiene su cama, escritorio y repisa. Lavadora de uso común.'],
            ['norma', 'Cuarto con baño propio en Larapita', null, 'San Jerónimo', 'Av. Collasuyo 1520, Urb. Larapita', 'U. Andina',
                -13.5354400, -71.9022900, 420, 'individual', 'Agua y luz, Wifi, Baño propio, Agua caliente', 15, 3, 'propio', 420, self::G . 'dormitorio-05.jpg',
                ['bano-02' => 'Baño propio', 'dormitorio-02' => 'Escritorio'],
                'Cuarto con baño propio en edificio de tres pisos, a cinco minutos del campus Larapa. Agua caliente todo el día.'],
            ['carmen', 'Cuarto cerca del Instituto Túpac Amaru', null, 'San Sebastián', 'Jr. Las Magnolias 205, Señor de Exaltación', 'IST Túpac Amaru',
                -13.5354900, -71.9263500, 260, 'individual', 'Agua y luz, Wifi', 11, 2, 'compartido', 260, self::G . 'dormitorio-08.jpg',
                ['bano-03' => 'Baño compartido'],
                'Cuarto en casa familiar a cinco minutos del Instituto Túpac Amaru. Zona residencial, con paraderos sobre la Av. de la Cultura.'],
            ['julio', 'Cuarto económico en Ayarmaca', null, 'San Sebastián', 'Calle Mariscal Cáceres 77, Ayarmaca', 'IST Túpac Amaru',
                -13.5363500, -71.9368000, 230, 'individual', 'Agua y luz', 10, 1, 'compartido', 230, self::G . 'dormitorio-09.jpg',
                ['bano-01' => 'Baño compartido'],
                'Cuarto sencillo en primer piso, con cama y mesa. Buena opción si buscas pagar poco y estudias en San Sebastián.'],
            ['rosa', 'Habitación con vista en San Blas', null, 'Cusco', 'Cuesta de San Blas 318', 'UNSAAC',
                -13.5148000, -71.9750000, 420, 'individual', 'Agua y luz, Wifi, Agua caliente, Amoblado', 15, 2, 'compartido', 420, self::G . 'dormitorio-14.jpg',
                ['dormitorio-11' => 'Ventana con vista a los techos', 'bano-03' => 'Baño con terma'],
                'Habitación en casona del barrio de San Blas, con vista a los techos del centro histórico. Calle peatonal y tranquila.'],
            ['hilda', 'Cuarto en Wanchaq cerca del mercado', null, 'Wanchaq', 'Av. Huayruropata 1205', 'UNSAAC',
                -13.5247200, -71.9645200, 300, 'individual', 'Agua y luz, Wifi, Cocina', 12, 2, 'compartido', 300, self::G . 'dormitorio-11.jpg',
                ['cocina-03' => 'Cocina compartida', 'bano-01' => 'Baño'],
                'Cuarto en Wanchaq a una cuadra de la Av. de la Cultura. Mercado, farmacias y bancos alrededor.'],
            ['hilda', 'Minidepartamento en Av. Garcilaso', null, 'Wanchaq', 'Av. Garcilaso 615', 'UNSAAC',
                -13.5219300, -71.9709200, 600, 'minidepartamento', 'Agua y luz, Wifi, Baño propio, Cocina, Amoblado, Agua caliente, Cable', 32, 4, 'propio', 600, self::G . 'dormitorio-13.jpg',
                ['cocina-01' => 'Cocina', 'bano-02' => 'Baño propio', 'dormitorio-03' => 'Dormitorio', 'estudio-01' => 'Escritorio'],
                'Minidepartamento completo en cuarto piso: dormitorio, kitchenette y baño propio. Ideal para compartir entre hermanos.'],
            ['julio', 'Cuarto junto a la Plaza Belén', null, 'Santiago', 'Plaza Belén 14, Urb. Roccopata', 'UNSAAC',
                -13.5267500, -71.9808300, 240, 'individual', 'Agua y luz, Wifi', 10, 2, 'compartido', 240, self::G . 'dormitorio-15.jpg',
                ['bano-01' => 'Baño compartido'],
                'Cuarto frente a la Plaza Belén, en Santiago. Precio cómodo y a diez minutos en bus del centro.'],
            ['norma', 'Cuarto cerca del Mercado Vinocanchón', null, 'San Jerónimo', 'Calle Arica 330, Pata Pata', 'U. Andina',
                -13.5445400, -71.8830300, 250, 'individual', 'Agua y luz, Wifi, Lavandería', 11, 2, 'compartido', 250, self::G . 'dormitorio-16.jpg',
                ['lavanderia-01' => 'Lavandería', 'cocina-05' => 'Cocina'],
                'Cuarto a tres cuadras del Mercado Vinocanchón. Buses a la U. Andina y a la U. Continental en la misma avenida.'],
        ];

        $creadas = [];
        foreach ($cuartos as $c) {
            [$dueno, $titulo, $anterior, $distrito, $direccion, $cerca, $lat, $lng, $precio, $tipo, $servicios, $m2, $piso, $bano, $garantia, $foto, $galeria, $descripcion] = $c;
            $datos = [
                'titulo' => $titulo, 'distrito' => $distrito, 'direccion' => $direccion, 'cerca_de' => $cerca,
                'lat' => $lat, 'lng' => $lng, 'precio' => $precio, 'tipo' => $tipo, 'servicios' => $servicios,
                'metros' => $m2, 'piso' => $piso, 'bano' => $bano, 'garantia' => $garantia, 'descripcion' => $descripcion,
            ];

            // las que ya existian se corrigen; las nuevas se crean
            $h = Habitacion::where('titulo', $anterior ?? $titulo)->where('usuario_id', $duenos[$dueno]->id)->first();
            if ($h) {
                $h->update($datos);
            } else {
                $h = $duenos[$dueno]->habitaciones()->create($datos + ['foto' => $foto]);
            }

            $h->fotos()->delete();
            $orden = 1;
            foreach ($galeria as $archivo => $texto) {
                $h->fotos()->create(['ruta' => self::G . $archivo . '.jpg', 'descripcion' => $texto, 'orden' => $orden++]);
            }
            $creadas[$titulo] = $h;
        }

        // visitas y reseñas de las nuevas
        $resenas = [
            [$yhon, self::MARCA, 5, 'Llego a clases en cinco minutos. El señor Fredy es muy atento y el agua caliente funciona bien.'],
            [$milagros, 'Cuarto en Larapa a pasos de la U. Andina', 5, 'Muy cerca de la universidad y la casa es segura. Las fotos son reales.'],
            [$brayan, 'Cuarto con escritorio frente al Real Plaza', 4, 'Buen escritorio y buen wifi. Por las noches hay algo de ruido de la avenida.'],
            [$milagros, 'Habitación compartida en El Tingo', 4, 'Compartí con una compañera de la U. Andina y nos fue bien. La lavadora ayuda mucho.'],
            [$yhon, 'Minidepartamento en Saylla', 5, 'Tiene todo lo necesario y está al lado de la Continental. Vale lo que cuesta.'],
            [$brayan, 'Habitación con vista en San Blas', 5, 'La vista es increíble y el barrio es tranquilo. Hay que subir escaleras, eso sí.'],
            [$u('ana@tampu.pe'), 'Habitación en Mariscal Gamarra', 4, 'Zona tranquila y cerca de la UNSAAC. La familia es respetuosa.'],
            [$u('kevin@tampu.pe'), 'Cuarto cerca del Instituto Túpac Amaru', 3, 'Está bien para el precio. El baño compartido se llena por las mañanas.'],
        ];
        foreach ($resenas as [$estudiante, $titulo, $estrellas, $texto]) {
            $h = $creadas[$titulo];
            $h->solicitudes()->create(['usuario_id' => $estudiante->id, 'fecha_visita' => now()->subDays(rand(5, 20))->toDateString(),
                'mensaje' => 'Me gustaría ver el cuarto antes de decidir.', 'estado' => 'aceptada']);
            $h->resenas()->create(['usuario_id' => $estudiante->id, 'estrellas' => $estrellas, 'comentario' => $texto]);
        }
        $creadas['Minidepartamento en Av. Garcilaso']->solicitudes()->create(['usuario_id' => $yhon->id,
            'fecha_visita' => now()->addDays(3)->toDateString(), 'mensaje' => '¿Se puede compartir entre dos hermanos?', 'estado' => 'pendiente']);

        $this->trabajos($u, $kuntur, $nusta, $saqsay, $yhon, $milagros, $brayan);
    }

    private function trabajos($u, $kuntur, $nusta, $saqsay, $yhon, $milagros, $brayan): void
    {
        $wayra = $u('wayra@tampu.pe');
        $andes = $u('andestech@tampu.pe');
        $inti = $u('intiraymi@tampu.pe');
        $qhapaq = $u('qhapaq@tampu.pe');

        // ubicacion, requisitos y vacantes de las ofertas que ya estaban
        $antes = [
            'Repartidor en bicicleta' => ['Calle Los Ángeles 1265, Urb. Roccopata', -13.5262800, -71.9848400, "Tener bicicleta o saber manejarla\nConocer las calles de Santiago\nMayor de 18 años", 2],
            'Soporte técnico sábados' => ['Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209, "Estudiar Ingeniería de Sistemas o afines\nSaber instalar Windows y armar computadoras", 1],
            'Ayudante de cocina (mañanas)' => ['Calle Procuradores 344', -13.5155052, -71.9794839, "Carné de sanidad (te ayudamos a sacarlo)\nPuntualidad", 2],
            'Atención en minimarket (noches)' => ['Av. de la Cultura s/n, frente a la plaza de San Sebastián', -13.5300534, -71.9373475, "Facilidad para atender al público\nManejo básico de caja", 1],
            'Practicante de sistemas' => ['Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209, "Estudiante de sexto ciclo en adelante\nConocer HTML, CSS y algo de PHP o JavaScript", 1],
            'Mozo/a fin de semana' => ['Calle Procuradores 344', -13.5155052, -71.9794839, "Inglés básico para atender turistas\nBuena presencia y trato amable", 3],
            'Guía de apoyo turístico' => ['Plaza San Blas 102', -13.5151764, -71.9742158, "Estudiante de Turismo\nInglés intermedio", 2],
        ];
        foreach ($antes as $titulo => [$direccion, $lat, $lng, $requisitos, $vacantes]) {
            Trabajo::where('titulo', $titulo)->update(compact('direccion', 'lat', 'lng', 'requisitos', 'vacantes'));
        }

        // [empresa, titulo, nombre comercial, distrito, modalidad, pago, por, direccion, lat, lng, descripcion, requisitos, vacantes, turnos]
        $nuevos = [
            [$kuntur, 'Barista medio tiempo', 'Café Kuntur', 'Cusco', 'medio tiempo', 45, 'dia', 'Av. El Sol 520', -13.5227515, -71.9745808,
                'Preparar cafés, jugos y postres y atender la barra. Te enseñamos a usar la máquina de espresso.', "Ganas de aprender\nTrato amable con el público", 2, ['lunes-tarde', 'miercoles-tarde', 'viernes-tarde', 'sabado-manana']],
            [$kuntur, 'Ayudante de barra (noches de fin de semana)', 'Café Kuntur', 'Cusco', 'por horas', 12, 'hora', 'Av. El Sol 520', -13.5227515, -71.9745808,
                'Apoyo en barra y limpieza los viernes y sábados por la noche.', "Mayor de 18 años\nDisponibilidad viernes y sábado", 2, ['viernes-noche', 'sabado-noche']],
            [$kuntur, 'Ayudante en juguería del mercado', 'Café Kuntur', 'Wanchaq', 'medio tiempo', 35, 'dia', 'Mercado de Wanchaq, puesto 48', -13.5312589, -71.9603152,
                'Preparar jugos y atender a los clientes del puesto en el Mercado de Wanchaq.', "Puntualidad\nCarné de sanidad", 1, ['martes-manana', 'jueves-manana', 'sabado-manana']],
            [$nusta, 'Recepcionista de noche en hostal', 'Hostal Ñusta Wasi', 'Cusco', 'medio tiempo', 50, 'dia', 'Cuesta de San Blas 310', -13.5150500, -71.9748000,
                'Recibir a los huéspedes, hacer check-in y atender el teléfono. Hay tiempo para estudiar en el turno.', "Inglés básico o intermedio\nResponsabilidad", 1, ['lunes-noche', 'miercoles-noche', 'viernes-noche']],
            [$nusta, 'Apoyo en desayunos (fines de semana)', 'Hostal Ñusta Wasi', 'Cusco', 'medio tiempo', 40, 'dia', 'Cuesta de San Blas 310', -13.5150500, -71.9748000,
                'Servir desayunos y ordenar el comedor del hostal los sábados y domingos.', "Puntualidad\nOrden y limpieza", 2, ['sabado-manana', 'domingo-manana']],
            [$saqsay, 'Profesor/a de matemática para preuniversitarios', 'Academia Saqsay', 'Wanchaq', 'por horas', 25, 'hora', 'Av. Pachacútec 615', -13.5244740, -71.9704044,
                'Dictar clases de álgebra y aritmética a grupos de 15 alumnos que se preparan para el examen de admisión.', "Estudiante de ingeniería, matemática o educación\nHaber ingresado a la universidad por examen", 3, ['martes-tarde', 'jueves-tarde', 'sabado-manana']],
            [$saqsay, 'Tutor/a de inglés para escolares', 'Academia Saqsay', 'Wanchaq', 'por horas', 20, 'hora', 'Av. Pachacútec 615', -13.5244740, -71.9704044,
                'Reforzar inglés a escolares de secundaria en grupos pequeños.', "Inglés intermedio o avanzado\nPaciencia con adolescentes", 2, ['lunes-tarde', 'miercoles-tarde']],
            [$saqsay, 'Promotor/a de matrícula en Real Plaza', 'Academia Saqsay', 'Cusco', 'por horas', 50, 'dia', 'Real Plaza Cusco, Av. Collasuyo 2964', -13.5234656, -71.9502285,
                'Informar sobre los ciclos de la academia en un módulo dentro del centro comercial.', "Facilidad de palabra\nDisponibilidad los domingos", 4, ['sabado-tarde', 'domingo-tarde']],
            [$wayra, 'Cajero/a fin de semana en San Jerónimo', 'Minimarket Wayra', 'San Jerónimo', 'medio tiempo', 45, 'dia', 'Calle Agricultura 120, frente al Mercado Vinocanchón', -13.5435425, -71.8877752,
                'Atención en caja y reposición de productos en nuestro local de San Jerónimo.', "Manejo básico de caja\nVivir cerca de San Jerónimo es un plus", 1, ['sabado-manana', 'sabado-tarde', 'domingo-manana']],
            [$andes, 'Community manager (medio tiempo)', 'Andes Tech SAC', 'Wanchaq', 'medio tiempo', 900, 'mes', 'Av. Tomasa Tito Condemayta 718', -13.5241973, -71.9647209,
                'Manejar las redes sociales de nuestros clientes: publicaciones, diseño básico y respuestas a mensajes.', "Estudiante de Comunicación, Marketing o Diseño\nManejo de Canva", 1, ['lunes-tarde', 'martes-tarde', 'jueves-tarde']],
            [$andes, 'Encuestador/a para estudio de mercado', 'Andes Tech SAC', 'San Sebastián', 'por horas', 50, 'dia', 'Plaza de Armas de San Sebastián', -13.5300534, -71.9373475,
                'Aplicar encuestas en celular a vecinos de San Sebastián durante dos fines de semana.', "Celular con datos\nTrato amable", 6, ['sabado-manana', 'domingo-manana']],
        ];
        $creados = [];
        foreach ($nuevos as [$empresa, $titulo, $nombre, $distrito, $modalidad, $pago, $por, $direccion, $lat, $lng, $descripcion, $requisitos, $vacantes, $turnos]) {
            $t = $empresa->trabajos()->create([
                'titulo' => $titulo, 'empresa' => $nombre, 'distrito' => $distrito, 'modalidad' => $modalidad,
                'pago' => $pago, 'pago_por' => $por, 'direccion' => $direccion, 'lat' => $lat, 'lng' => $lng,
                'descripcion' => $descripcion, 'requisitos' => $requisitos, 'vacantes' => $vacantes,
            ]);
            foreach ($turnos as $turno) {
                [$dia, $parte] = explode('-', $turno);
                $t->horarios()->create(['dia' => $dia, 'turno' => $parte]);
            }
            $creados[$titulo] = $t;
        }

        $creados['Profesor/a de matemática para preuniversitarios']->postulaciones()->create(['usuario_id' => $yhon->id, 'estado' => 'aceptada',
            'mensaje' => 'Ingresé a Ingeniería Civil por examen y ya enseñé a mis primos.']);
        $creados['Barista medio tiempo']->postulaciones()->create(['usuario_id' => $milagros->id, 'mensaje' => 'Trabajé en la cafetería de mi tío en Urubamba.']);
        $creados['Recepcionista de noche en hostal']->postulaciones()->create(['usuario_id' => $brayan->id, 'mensaje' => 'Tengo inglés intermedio y estudio de día.']);
        $creados['Encuestador/a para estudio de mercado']->postulaciones()->create(['usuario_id' => $u('rocio@tampu.pe')->id]);
    }

    private function usuario($nombre, $email, $dni, $celular, $rol, $procedencia = null): Usuario
    {
        return Usuario::firstOrCreate(['email' => $email], [
            'nombre' => $nombre, 'dni' => $dni, 'celular' => $celular, 'rol' => $rol, 'procedencia' => $procedencia,
            'verificacion' => $rol === 'estudiante' ? 'no_aplica' : 'verificado', 'password' => '123456',
            'email_verificado_at' => now(),
        ]);
    }
}
