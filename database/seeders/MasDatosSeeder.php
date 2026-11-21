<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Trabajo;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// 100 viviendas (cuartos, minidepas, depas con roomies y casas de estudiantes)
// y 60 ofertas de trabajo mas, con fechas de publicacion repartidas.
// Los negocios y personas son ficticios (datos de prueba).
// Se puede ejecutar varias veces: si ya se cargo, no hace nada.
class MasDatosSeeder extends Seeder
{
    const MARCA = 'casadelestudiante@tampu.pe';

    const G = 'img/habitaciones/galeria/';

    // zonas por distrito: [nombre corto, direccion base, lat, lng, universidad cercana]
    const ZONAS = [
        'Cusco' => [
            ['Av. de la Cultura', 'Av. de la Cultura', -13.5214, -71.9603, 'UNSAAC'],
            ['Magisterio', 'Urb. Magisterio', -13.5206, -71.9628, 'UNSAAC'],
            ['Manuel Prado', 'Urb. Manuel Prado', -13.5203, -71.9552, 'UNSAAC'],
            ['Tullumayo', 'Av. Tullumayo', -13.5184, -71.9733, 'UNSAAC'],
            ['Recoleta', 'Calle Recoleta', -13.5172, -71.9701, 'UNSAAC'],
            ['San Blas', 'Calle Tandapata', -13.5146, -71.9744, 'UNSAAC'],
            ['Saphy', 'Calle Saphy', -13.5149, -71.9829, 'UNSAAC'],
            ['Nueva Alta', 'Calle Nueva Alta', -13.5201, -71.9843, 'UNSAAC'],
        ],
        'Wanchaq' => [
            ['Garcilaso', 'Av. Garcilaso', -13.5219, -71.9706, 'UNSAAC'],
            ['Huayruropata', 'Av. Huayruropata', -13.5249, -71.9639, 'UNSAAC'],
            ['Santa Mónica', 'Urb. Santa Mónica', -13.5262, -71.9588, 'UNSAAC'],
            ['Ttio', 'Urb. Ttio', -13.5338, -71.9561, 'UNSAAC'],
            ['Zarumilla', 'Urb. Zarumilla', -13.5289, -71.9602, 'UNSAAC'],
            ['Av. Infancia', 'Av. Infancia', -13.5271, -71.9661, 'UNSAAC'],
            ['Progreso', 'Urb. Progreso', -13.5304, -71.9534, 'IST Túpac Amaru'],
        ],
        'Santiago' => [
            ['Antonio Lorena', 'Av. Antonio Lorena', -13.5255, -71.9879, 'UNSAAC'],
            ['Zarzuela', 'Urb. Zarzuela', -13.5301, -71.9758, 'UNSAAC'],
            ['Belén', 'Calle Belén', -13.5267, -71.9806, 'UNSAAC'],
            ['Av. Ejército', 'Av. Ejército', -13.5226, -71.9818, 'UNSAAC'],
            ['Huancaro', 'Urb. Huancaro', -13.5369, -71.9799, 'UNSAAC'],
        ],
        'San Sebastián' => [
            ['Túpac Amaru', 'Urb. Túpac Amaru', -13.5349, -71.9088, 'U. Andina'],
            ['Plaza San Sebastián', 'Av. de la Cultura cdra. 18', -13.5301, -71.9372, 'IST Túpac Amaru'],
            ['Ayarmaca', 'Calle Ayarmaca', -13.5362, -71.9366, 'IST Túpac Amaru'],
            ['Santa Rosa', 'Urb. Santa Rosa', -13.5331, -71.9298, 'IST Túpac Amaru'],
            ['Tankarpata', 'Asoc. Tankarpata', -13.5381, -71.9249, 'IST Túpac Amaru'],
        ],
        'San Jerónimo' => [
            ['Larapa', 'Av. Villa del Carmen, Larapa', -13.5374, -71.9029, 'U. Andina'],
            ['El Tingo', 'Calle Manantial, El Tingo', -13.5391, -71.9020, 'U. Andina'],
            ['Plaza San Jerónimo', 'Calle Clorinda Matto de Turner', -13.5440, -71.8832, 'U. Andina'],
            ['Pata Pata', 'Asoc. Pata Pata', -13.5446, -71.8829, 'U. Andina'],
            ['Oropesa Huaylla', 'Asoc. Oropesa Huaylla', -13.5514, -71.8585, 'U. Continental'],
        ],
        'Saylla' => [
            ['Angostura', 'Sector Angostura', -13.5553, -71.8556, 'U. Continental'],
            ['Saylla', 'Calle Principal de Saylla', -13.5689, -71.8282, 'U. Continental'],
        ],
    ];

    public function run(): void
    {
        if (! Usuario::where('email', 'rosa@tampu.pe')->exists() || Usuario::where('email', self::MARCA)->exists()) {
            return;
        }

        mt_srand(2026);
        $ahora = Carbon::now();

        // las publicaciones que ya habia se reparten en las ultimas semanas
        foreach (Habitacion::all() as $h) {
            $this->fecha('habitaciones', $h->id, $ahora->copy()->subMinutes(mt_rand(4 * 1440, 80 * 1440)));
        }
        foreach (Trabajo::all() as $t) {
            $this->fecha('trabajos', $t->id, $ahora->copy()->subMinutes(mt_rand(3 * 1440, 60 * 1440)));
        }

        $duenos = $this->duenos();
        $estudiantes = $this->estudiantes();
        $creadas = $this->viviendas($duenos, $ahora);
        $this->resenas($creadas, $estudiantes);
        $this->trabajos($ahora, $estudiantes);
    }

    // ---------------------------------------------------------------- usuarios

    private function duenos(): array
    {
        $lista = [
            ['Gladys Huamán Ccori', 'gladys@tampu.pe'], ['Teodoro Mamani Quispe', 'teodoro@tampu.pe'],
            ['Elena Cusihuamán Puma', 'elena@tampu.pe'], ['Víctor Huallpa Condori', 'victor@tampu.pe'],
            ['Martha Sutta Ccahuana', 'martha@tampu.pe'], ['Wilber Choque Ayma', 'wilber@tampu.pe'],
            ['Lourdes Paucar Tito', 'lourdes@tampu.pe'], ['Edwin Quispe Huanca', 'edwin@tampu.pe'],
            ['Silvia Yupanqui Soto', 'silvia@tampu.pe'], ['Alberto Ccoa Huillca', 'alberto@tampu.pe'],
            ['Nélida Apaza Ramos', 'nelida@tampu.pe'], ['Rubén Tupayachi Loayza', 'ruben@tampu.pe'],
            ['Casa del Estudiante Wiñay', self::MARCA], ['Residencial Inti Yachay', 'intiyachay@tampu.pe'],
        ];
        $duenos = [];
        foreach ($lista as $i => [$nombre, $email]) {
            $duenos[] = $this->usuario($nombre, $email, (string) (51000100 + $i), '98' . (5100100 + $i * 37), 'arrendador');
        }
        // tambien publican los arrendadores que ya estaban
        foreach (['rosa', 'julio', 'carmen', 'hilda', 'fredy', 'norma'] as $n) {
            if ($u = Usuario::where('email', "$n@tampu.pe")->first()) {
                $duenos[] = $u;
            }
        }

        return $duenos;
    }

    private function estudiantes(): array
    {
        $lista = [
            ['Jhon Quispe Mamani', 'Sicuani'], ['Fiorela Huamán Ccasa', 'Abancay'], ['Ronald Condori Puma', 'Puno'],
            ['Katherine Ayma Soto', 'Quillabamba'], ['Edison Ccahuana Ttito', 'Espinar'], ['Lizbeth Paucar Huillca', 'Chumbivilcas'],
            ['Alex Mamani Choque', 'Juliaca'], ['Yeni Ccori Huanca', 'Andahuaylas'], ['Henry Tito Apaza', 'Puerto Maldonado'],
            ['Diana Quispe Loayza', 'Calca'], ['Frank Huallpa Ramos', 'Urubamba'], ['Sheyla Condori Yupanqui', 'Anta'],
        ];
        $estudiantes = [];
        foreach ($lista as $i => [$nombre, $procedencia]) {
            $email = strtolower(explode(' ', $this->sinTildes($nombre))[0]) . ($i + 1) . '@tampu.pe';
            $estudiantes[] = $this->usuario($nombre, $email, (string) (72000100 + $i), '95' . (2100100 + $i * 41), 'estudiante', $procedencia);
        }
        foreach (['luis', 'ana', 'kevin', 'rocio', 'yhon', 'milagros', 'brayan'] as $n) {
            if ($u = Usuario::where('email', "$n@tampu.pe")->first()) {
                $estudiantes[] = $u;
            }
        }

        return $estudiantes;
    }

    // ---------------------------------------------------------------- viviendas

    private function viviendas(array $duenos, Carbon $ahora): array
    {
        // cuantas por distrito y por tipo (suman 100)
        $porDistrito = ['Cusco' => 22, 'Wanchaq' => 22, 'Santiago' => 14, 'San Sebastián' => 16, 'San Jerónimo' => 16, 'Saylla' => 10];
        $tipos = array_merge(array_fill(0, 40, 'individual'), array_fill(0, 14, 'compartida'), array_fill(0, 16, 'minidepartamento'),
            array_fill(0, 16, 'roomies'), array_fill(0, 14, 'residencia'));
        $this->mezclar($tipos);

        $factor = ['Cusco' => 1.1, 'Wanchaq' => 1.05, 'Santiago' => 0.85, 'San Sebastián' => 0.95, 'San Jerónimo' => 0.9, 'Saylla' => 0.9];
        $rango = ['individual' => [240, 430], 'compartida' => [150, 240], 'minidepartamento' => [520, 850], 'roomies' => [290, 450], 'residencia' => [380, 650]];

        // de las 100: 8 en el ultimo dia, 12 entre 1 y 3 dias, el resto hasta 2 meses
        $minutos = [];
        for ($i = 0; $i < 100; $i++) {
            $minutos[] = $i < 8 ? mt_rand(20, 1440) : ($i < 20 ? mt_rand(1440, 3 * 1440) : mt_rand(3 * 1440, 65 * 1440));
        }
        $this->mezclar($minutos);

        $usados = Habitacion::pluck('titulo')->flip()->all();
        $creadas = [];
        $n = 0;
        foreach ($porDistrito as $distrito => $cantidad) {
            for ($k = 0; $k < $cantidad; $k++, $n++) {
                $tipo = $tipos[$n];
                [$zona, $calle, $lat, $lng, $uni] = self::ZONAS[$distrito][$k % count(self::ZONAS[$distrito])];
                $dueno = $tipo === 'residencia' && mt_rand(0, 1) ? $duenos[12 + mt_rand(0, 1)] : $duenos[mt_rand(0, count($duenos) - 1)];

                [$min, $max] = $rango[$tipo];
                $precio = (int) (round(mt_rand($min, $max) * $factor[$distrito] / 10) * 10);
                $titulo = $this->titulo($tipo, $zona, $distrito, $uni, $usados);
                $usados[$titulo] = true;

                $servicios = $this->servicios($tipo);
                $bano = in_array($tipo, ['minidepartamento']) || ($tipo === 'individual' && mt_rand(1, 10) <= 3) ? 'propio' : 'compartido';
                if ($bano === 'propio' && ! str_contains($servicios, 'Baño propio')) {
                    $servicios .= ', Baño propio';
                }
                $metros = match ($tipo) {
                    'individual' => mt_rand(9, 16), 'compartida' => mt_rand(16, 24), 'minidepartamento' => mt_rand(24, 38),
                    'roomies' => mt_rand(11, 16), default => mt_rand(10, 14),
                };

                $h = $dueno->habitaciones()->create([
                    'titulo' => $titulo,
                    'descripcion' => $this->descripcion($tipo, $zona, $distrito, $uni, $bano),
                    'distrito' => $distrito,
                    'direccion' => $calle . ' ' . (mt_rand(1, 9) <= 6 ? mt_rand(100, 1400) : 'Mz. ' . chr(65 + mt_rand(0, 7)) . ' Lt. ' . mt_rand(1, 20)),
                    'cerca_de' => $uni,
                    'lat' => round($lat + mt_rand(-28, 28) / 10000, 7),
                    'lng' => round($lng + mt_rand(-28, 28) / 10000, 7),
                    'precio' => $precio,
                    'tipo' => $tipo,
                    'servicios' => $servicios,
                    'foto' => $this->fotoPrincipal(),
                    'estado' => mt_rand(1, 100) <= 8 ? 'ocupada' : 'disponible',
                    'metros' => $metros,
                    'piso' => mt_rand(1, 4),
                    'bano' => $bano,
                    'garantia' => $tipo === 'residencia' ? null : $precio,
                ]);

                $orden = 1;
                foreach ($this->galeria($tipo) as $archivo => $texto) {
                    $h->fotos()->create(['ruta' => self::G . $archivo . '.jpg', 'descripcion' => $texto, 'orden' => $orden++]);
                }
                $this->fecha('habitaciones', $h->id, $ahora->copy()->subMinutes($minutos[$n]));
                $creadas[] = $h;
            }
        }

        return $creadas;
    }

    private function titulo(string $tipo, string $zona, string $distrito, string $uni, array $usados): string
    {
        $opciones = match ($tipo) {
            'individual' => ["Cuarto amoblado en $zona", "Habitación con escritorio – $zona", "Cuarto luminoso cerca de la $uni",
                "Habitación tranquila en $zona", "Cuarto con buena señal de wifi en $zona", "Habitación para estudiante en $distrito",
                "Cuarto independiente en $zona", "Habitación con ventana a la calle – $zona"],
            'compartida' => ["Habitación doble para estudiantes en $zona", "Cuarto compartido cerca de la $uni",
                "Habitación para dos estudiantes – $zona", "Cuarto compartido económico en $distrito"],
            'minidepartamento' => ["Minidepartamento amoblado en $zona", "Mini depa con cocina en $zona",
                "Minidepartamento con baño propio – $distrito", "Mini depa independiente cerca de la $uni"],
            'roomies' => ["Busco roomie: depa de 3 cuartos en $zona", "Depa compartido para estudiantes en $zona",
                "Roomies: queda un cuarto libre en $zona", "Departamento para compartir cerca de la $uni"],
            default => ["Casa de estudiantes en $zona", "Residencia universitaria cerca de la $uni",
                "Casa estudiantil con áreas comunes – $distrito", "Pensión para estudiantes con desayuno en $zona"],
        };
        $this->mezclar($opciones);
        foreach ($opciones as $t) {
            if (! isset($usados[$t])) {
                return $t;
            }
        }
        $i = 2;
        while (isset($usados[$opciones[0] . " ($i)"])) {
            $i++;
        }

        return $opciones[0] . " ($i)";
    }

    private function descripcion(string $tipo, string $zona, string $distrito, string $uni, string $bano): string
    {
        $inicio = match ($tipo) {
            'individual' => $this->uno(['Cuarto para una persona con cama de plaza y media, ropero y escritorio.',
                'Habitación individual con mucha luz natural, escritorio amplio y repisas.', 'Cuarto sencillo y limpio, ideal para concentrarse en los estudios.']),
            'compartida' => $this->uno(['Habitación para dos estudiantes: cada uno tiene su cama, escritorio y espacio en el ropero.',
                'Cuarto amplio para compartir con otro estudiante. Se busca persona tranquila y ordenada.']),
            'minidepartamento' => $this->uno(['Minidepartamento independiente con dormitorio, kitchenette y baño propio.',
                'Mini depa con ingreso propio, cocina equipada y espacio para estudiar.']),
            'roomies' => $this->uno(['Departamento de tres dormitorios que compartimos estudiantes. Queda un cuarto libre con llave propia.',
                'Somos dos estudiantes y buscamos roomie para el tercer cuarto. La sala, la cocina y la lavadora son de uso común.',
                'Depa compartido entre estudiantes universitarios: cada uno tiene su cuarto y se comparten cocina, sala y baño.']),
            default => $this->uno(['Casa de estudiantes con cuartos individuales, comedor, sala de estudio y patio. Incluye limpieza de áreas comunes.',
                'Residencia universitaria con reglas de convivencia claras, sala de estudio y lavandería. Opción de desayuno incluido.',
                'Casa estudiantil administrada por una familia. Hay horario de ingreso, sala de estudio y cocina común.']),
        };
        $zonaTexto = $this->uno(["Queda en $zona ($distrito), con tiendas y paraderos cerca.",
            "Está en $zona, una zona tranquila de $distrito.", "Ubicado en $zona, $distrito, a pocos minutos en bus de la $uni."]);
        $extra = $bano === 'propio' ? 'Tiene baño propio con agua caliente.' : $this->uno(['El baño se comparte y tiene terma.', 'Baño compartido, limpio y con agua caliente.']);
        $regla = $this->uno(['Se pide visitar antes de pagar.', 'Contrato por semestre o por año.', 'No se cobra adelanto sin visita previa.', 'Se aceptan estudiantes de cualquier universidad.']);

        return "$inicio $zonaTexto $extra $regla";
    }

    private function servicios(string $tipo): string
    {
        $base = ['Agua y luz', 'Wifi'];
        $extras = ['Agua caliente', 'Amoblado', 'Lavandería', 'Escritorio', 'Cocina', 'Cable'];
        $this->mezclar($extras);
        $lista = array_merge($base, array_slice($extras, 0, mt_rand(1, 3)));
        if (in_array($tipo, ['minidepartamento', 'roomies'])) {
            $lista[] = 'Cocina';
        }
        if ($tipo === 'residencia') {
            $lista = array_merge($lista, ['Sala de estudio', $this->uno(['Desayuno', 'Limpieza de áreas comunes'])]);
        }

        return implode(', ', array_unique($lista));
    }

    private function fotoPrincipal(): string
    {
        $n = mt_rand(1, 26);

        return $n <= 10 ? "img/habitaciones/h$n.jpg" : self::G . sprintf('dormitorio-%02d.jpg', $n - 10);
    }

    private function galeria(string $tipo): array
    {
        $fotos = [
            sprintf('dormitorio-%02d', mt_rand(1, 16)) => 'Otra vista del cuarto',
            sprintf('bano-%02d', mt_rand(1, 3)) => 'Baño',
        ];
        if (in_array($tipo, ['minidepartamento', 'roomies', 'residencia']) || mt_rand(0, 1)) {
            $fotos[sprintf('cocina-%02d', mt_rand(1, 5))] = $tipo === 'minidepartamento' ? 'Kitchenette' : 'Cocina';
        }
        if (in_array($tipo, ['residencia', 'roomies']) || mt_rand(1, 4) === 1) {
            $fotos['estudio-01'] = $tipo === 'residencia' ? 'Sala de estudio' : 'Zona de estudio';
        }
        if (mt_rand(1, 3) === 1) {
            $fotos['lavanderia-01'] = 'Lavandería';
        }

        return $fotos;
    }

    private function resenas(array $creadas, array $estudiantes): void
    {
        $comentarios = [
            5 => ['Todo tal cual las fotos. La dueña es muy amable y la zona es segura.', 'Muy cerca de mi universidad, llego caminando. Lo recomiendo.',
                'Excelente para estudiar, silencioso y con buen wifi.', 'Me sentí en casa desde el primer día. El agua caliente nunca falla.'],
            4 => ['Buen cuarto por el precio. Por las noches hace un poco de frío.', 'La casa es tranquila. El baño compartido a veces se llena en la mañana.',
                'Buena ubicación, hay paraderos cerca. El wifi podría ser mejor.', 'Mis roomies son ordenados y la cocina siempre está limpia.'],
            3 => ['Está bien, pero hay ruido de la avenida.', 'Cumple, aunque el cuarto es algo pequeño.'],
        ];
        $conResenas = array_slice($creadas, 0, 100);
        $this->mezclar($conResenas);
        foreach (array_slice($conResenas, 0, 45) as $h) {
            $quienes = $estudiantes;
            $this->mezclar($quienes);
            foreach (array_slice($quienes, 0, mt_rand(1, 3)) as $e) {
                $estrellas = $this->uno([5, 5, 5, 4, 4, 4, 3]);
                $h->solicitudes()->create(['usuario_id' => $e->id, 'fecha_visita' => now()->subDays(mt_rand(3, 40))->toDateString(),
                    'mensaje' => 'Quisiera visitar el cuarto antes de decidir.', 'estado' => 'aceptada']);
                $h->resenas()->create(['usuario_id' => $e->id, 'estrellas' => $estrellas, 'comentario' => $this->uno($comentarios[$estrellas])]);
            }
        }
        // algunas visitas por responder para los paneles de los arrendadores
        foreach (array_slice($conResenas, 45, 12) as $h) {
            $e = $estudiantes[mt_rand(0, count($estudiantes) - 1)];
            $h->solicitudes()->create(['usuario_id' => $e->id, 'fecha_visita' => now()->addDays(mt_rand(1, 7))->toDateString(),
                'mensaje' => '¿Podría verlo esta semana por la tarde?', 'estado' => 'pendiente']);
        }
    }

    // ---------------------------------------------------------------- trabajos

    private function trabajos(Carbon $ahora, array $estudiantes): void
    {
        // empresas ficticias: [clave, nombre comercial, responsable, distrito, direccion, lat, lng]
        $empresas = [
            ['tunki', 'Café Tunki Wasi', 'Mariela Ccorimanya', 'Cusco', 'Calle Plateros 340', -13.5161, -71.9795],
            ['lenador', 'Pollería El Leñador Andino', 'Jorge Huarcaya', 'Wanchaq', 'Av. de la Cultura 1020', -13.5254, -71.9534],
            ['qocha', "Restaurante Q'ocha", 'Lucía Palomino', 'Cusco', 'Calle Tecsecocha 215', -13.5153, -71.9812],
            ['intiwasi', 'Hostal Inti Wasi', 'Ricardo Yábar', 'Cusco', 'Calle Carmen Alto 180', -13.5143, -71.9747],
            ['killa', 'Hotel Killa Plaza', 'Ana María Zúñiga', 'Cusco', 'Av. Sol 860', -13.5243, -71.9713],
            ['andeanpath', 'Andes Trail Travel', 'Gustavo Paucar', 'Cusco', 'Calle Procuradores 120', -13.5159, -71.9800],
            ['presigma', 'Academia Pre Sigma', 'Héctor Loaiza', 'Wanchaq', 'Av. Garcilaso 310', -13.5214, -71.9714],
            ['rimay', 'Centro de Idiomas Rimay', 'Carla Villena', 'Wanchaq', 'Av. Tullumayo 950', -13.5207, -71.9721],
            ['canasta', 'Minimarket La Canasta', 'Pedro Ttito', 'San Sebastián', 'Av. de la Cultura 1720', -13.5296, -71.9395],
            ['donlucho', 'Bodega Don Lucho', 'Luis Champi', 'Santiago', 'Av. Antonio Lorena 620', -13.5251, -71.9869],
            ['qapaq', 'Ñan Express Delivery', 'Kevin Arredondo', 'Wanchaq', 'Av. Huayruropata 1480', -13.5262, -71.9601],
            ['saber', 'Librería y Copias Saber', 'Rosa Mendoza', 'Cusco', 'Av. de la Cultura 760, frente a la UNSAAC', -13.5222, -71.9579],
            ['fuerza', 'Gimnasio Fuerza Andina', 'Miguel Cáceres', 'Wanchaq', 'Av. Diagonal Angamos 320', -13.5232, -71.9620],
            ['ccori', 'Ccori Contadores Asociados', 'Elizabeth Ccori', 'Wanchaq', 'Av. Manco Cápac 512', -13.5240, -71.9670],
            ['pukara', 'Constructora Pukara SAC', 'Ing. Raúl Pacheco', 'San Jerónimo', 'Av. Huanacaure 140', -13.5428, -71.8865],
            ['mamaqocha', 'Estudio Mama Qocha Arquitectos', 'Arq. Sofía Valer', 'Cusco', 'Calle Saphy 650', -13.5146, -71.9836],
            ['contact', 'Andes Contact Center', 'Paola Quintana', 'Wanchaq', 'Av. Velasco Astete 1210', -13.5293, -71.9620],
            ['alpaca', 'Tienda Alpaca Moda', 'Juana Sallo', 'Cusco', 'Calle Triunfo 380', -13.5170, -71.9774],
            ['pukllay', 'Ludoteca Pukllay', 'Verónica Huamaní', 'San Sebastián', 'Urb. Santa Rosa B-7', -13.5332, -71.9301],
            ['panvalle', 'Panadería Pan del Valle', 'Teófilo Quispe', 'San Jerónimo', 'Plaza de San Jerónimo 22', -13.5441, -71.8830],
            ['angostura', 'Cafetería Angostura', 'Elmer Durand', 'Saylla', 'Sector Angostura, frente a la U. Continental', -13.5559, -71.8566],
            ['mishi', 'Veterinaria Mishi y Allqu', 'Dra. Karina Tapia', 'Santiago', 'Av. Ejército 870', -13.5229, -71.9827],
            ['grafik', 'Imprenta Grafik Inka', 'Nilton Huillca', 'Santiago', 'Calle Belén 450', -13.5268, -71.9801],
        ];
        $emp = [];
        foreach ($empresas as $i => [$clave, $nombre, $responsable, $distrito, $direccion, $lat, $lng]) {
            $usuario = $this->usuario($responsable, $clave . '@tampu.pe', (string) (53000100 + $i), '97' . (3100100 + $i * 29), 'empleador');
            $emp[$clave] = compact('usuario', 'nombre', 'distrito', 'direccion', 'lat', 'lng');
        }

        // [empresa, titulo, modalidad, pago, por, descripcion, requisitos, vacantes, turnos]
        $ofertas = [
            ['tunki', 'Barista (tardes)', 'medio tiempo', 40, 'dia', 'Preparar cafés y postres, atender la barra y cobrar.', "Trato amable\nGanas de aprender a usar la máquina de espresso", 2, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['tunki', 'Mozo/a fines de semana', 'por horas', 10, 'hora', 'Atender mesas a turistas y vecinos en el centro histórico.', "Inglés básico\nDisponibilidad sábado y domingo", 3, 'sabado-tarde domingo-tarde'],
            ['tunki', 'Ayudante de pastelería (mañanas)', 'medio tiempo', 35, 'dia', 'Apoyar en la preparación de tortas y empanadas para el día.', "Puntualidad\nCarné de sanidad (te ayudamos a sacarlo)", 1, 'martes-manana jueves-manana sabado-manana'],
            ['lenador', 'Cajero/a de pollería (noches)', 'medio tiempo', 45, 'dia', 'Cobrar en caja, tomar pedidos por teléfono y cuadrar caja al cierre.', "Manejo básico de caja\nResponsabilidad con el dinero", 1, 'lunes-noche miercoles-noche viernes-noche'],
            ['lenador', 'Repartidor/a en moto', 'por horas', 12, 'hora', 'Llevar pedidos de delivery en Wanchaq y San Sebastián. La empresa paga la gasolina.', "Brevete de moto vigente\nConocer las calles de Cusco", 2, 'viernes-noche sabado-noche domingo-noche'],
            ['lenador', 'Ayudante de cocina fin de semana', 'por horas', 10, 'hora', 'Preparar guarniciones y mantener limpia la cocina.', "Carné de sanidad\nTrabajo en equipo", 2, 'sabado-tarde domingo-tarde'],
            ['qocha', 'Anfitrión/a bilingüe', 'medio tiempo', 50, 'dia', 'Recibir a los comensales, explicar la carta y gestionar reservas.', "Inglés intermedio\nBuena presencia", 1, 'martes-noche jueves-noche sabado-noche'],
            ['qocha', 'Mozo/a de almuerzos', 'medio tiempo', 40, 'dia', 'Atender mesas en el horario de almuerzo.', "Experiencia atendiendo público (no indispensable)", 2, 'lunes-tarde martes-tarde jueves-tarde'],
            ['intiwasi', 'Recepcionista de noche', 'medio tiempo', 55, 'dia', 'Check-in de huéspedes, atención por teléfono y WhatsApp. Puedes estudiar en los ratos libres.', "Inglés intermedio\nManejo de computadora", 1, 'domingo-noche martes-noche jueves-noche'],
            ['intiwasi', 'Apoyo en desayunos', 'medio tiempo', 35, 'dia', 'Servir desayunos y ordenar el comedor.', "Puntualidad (entrada 6:30 a. m.)", 2, 'sabado-manana domingo-manana'],
            ['killa', 'Botones / maletero', 'medio tiempo', 45, 'dia', 'Recibir a los huéspedes y llevar el equipaje a las habitaciones.', "Buena condición física\nInglés básico", 2, 'viernes-tarde sabado-tarde domingo-tarde'],
            ['killa', 'Practicante de recepción hotelera', 'practicas', 930, 'mes', 'Prácticas preprofesionales en recepción y reservas. Constancia y certificado.', "Estudiante de Turismo u Hotelería\nInglés intermedio", 2, 'lunes-tarde martes-tarde miercoles-tarde jueves-tarde viernes-tarde'],
            ['killa', 'Ayudante de housekeeping', 'por horas', 9, 'hora', 'Apoyar en la limpieza y preparación de habitaciones.', "Orden y responsabilidad", 3, 'sabado-manana domingo-manana'],
            ['andeanpath', 'Asesor/a de ventas de tours', 'medio tiempo', 900, 'mes', 'Atender a turistas en la oficina y vender paquetes a Machu Picchu y Valle Sagrado.', "Inglés intermedio\nFacilidad de palabra", 2, 'lunes-tarde miercoles-tarde viernes-tarde sabado-tarde'],
            ['andeanpath', 'Guía de apoyo en city tour', 'por horas', 25, 'hora', 'Acompañar al guía principal en el city tour y el ingreso a Qoricancha y Sacsayhuamán.', "Estudiante de Turismo\nInglés intermedio o avanzado", 3, 'sabado-tarde domingo-manana'],
            ['andeanpath', 'Practicante de marketing digital', 'practicas', 1025, 'mes', 'Crear contenido para redes, responder mensajes y medir campañas.', "Estudiante de Marketing o Comunicación\nManejo de Canva", 1, 'lunes-manana martes-manana miercoles-manana'],
            ['presigma', 'Profesor/a de física preuniversitaria', 'por horas', 28, 'hora', 'Dictar física a grupos que se preparan para el examen de la UNSAAC.', "Estudiante de Ingeniería o Física\nHaber ingresado por examen", 2, 'martes-tarde jueves-tarde'],
            ['presigma', 'Profesor/a de razonamiento verbal', 'por horas', 25, 'hora', 'Clases de comprensión lectora y analogías para preuniversitarios.', "Estudiante de Educación, Derecho o Letras", 1, 'lunes-tarde miercoles-tarde'],
            ['presigma', 'Tutor/a de simulacros (domingos)', 'por horas', 20, 'hora', 'Supervisar los simulacros de examen y corregir hojas de respuesta.', "Responsabilidad\nPuntualidad", 4, 'domingo-manana'],
            ['rimay', 'Profesor/a de inglés para niños', 'por horas', 22, 'hora', 'Clases de inglés con juegos para niños de 7 a 11 años.', "Inglés avanzado\nPaciencia y creatividad", 2, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['rimay', 'Profesor/a de quechua básico', 'por horas', 25, 'hora', 'Enseñar quechua básico a jóvenes y turistas.', "Quechua fluido\nGanas de enseñar", 1, 'sabado-manana'],
            ['rimay', 'Recepcionista de academia', 'medio tiempo', 850, 'mes', 'Atender a los alumnos, inscribir y cobrar pensiones.', "Manejo de Excel básico\nTrato amable", 1, 'lunes-noche martes-noche miercoles-noche jueves-noche'],
            ['canasta', 'Reponedor/a de mercadería', 'por horas', 9, 'hora', 'Ordenar productos en anaqueles y revisar fechas de vencimiento.', "Orden y rapidez", 2, 'sabado-manana domingo-manana'],
            ['canasta', 'Cajero/a de minimarket (noches)', 'medio tiempo', 40, 'dia', 'Atención en caja y apoyo en almacén.', "Manejo básico de caja", 1, 'martes-noche jueves-noche sabado-noche'],
            ['donlucho', 'Ayudante de bodega', 'por horas', 8, 'hora', 'Atender a los clientes del barrio y ordenar la bodega.', "Vivir cerca de Santiago es un plus", 1, 'lunes-noche miercoles-noche viernes-noche'],
            ['qapaq', 'Repartidor/a en bicicleta', 'por horas', 10, 'hora', 'Repartir pedidos de restaurantes en Wanchaq y el centro. Te prestamos mochila térmica.', "Bicicleta propia\nCelular con datos", 6, 'viernes-noche sabado-tarde sabado-noche domingo-tarde'],
            ['qapaq', 'Repartidor/a en moto (fines de semana)', 'por horas', 13, 'hora', 'Pedidos de comida y farmacia en todo Cusco.', "Brevete y SOAT vigentes", 3, 'sabado-noche domingo-noche'],
            ['qapaq', 'Atención al cliente por WhatsApp', 'medio tiempo', 800, 'mes', 'Responder a clientes y coordinar con los repartidores desde la oficina.', "Buena redacción\nManejo de WhatsApp Business", 1, 'lunes-tarde martes-tarde miercoles-tarde jueves-tarde'],
            ['saber', 'Atención en fotocopiadora', 'medio tiempo', 35, 'dia', 'Fotocopias, impresiones, anillados y venta de útiles frente a la universidad.', "Rapidez\nManejo de impresoras", 2, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['saber', 'Diseñador/a de trabajos e impresiones', 'por horas', 12, 'hora', 'Diagramar trabajos, tesis y afiches de los clientes.', "Word, Canva o Illustrator", 1, 'martes-tarde jueves-tarde sabado-manana'],
            ['fuerza', 'Recepcionista de gimnasio (mañanas)', 'medio tiempo', 35, 'dia', 'Registrar el ingreso de socios y vender membresías. Gimnasio gratis para ti.', "Puntualidad (entrada 6:00 a. m.)", 1, 'lunes-manana miercoles-manana viernes-manana'],
            ['fuerza', 'Instructor/a de baile fitness', 'por horas', 30, 'hora', 'Dictar clases grupales de baile fitness.', "Experiencia en baile\nEnergía y carisma", 1, 'martes-noche jueves-noche'],
            ['ccori', 'Practicante de contabilidad', 'practicas', 1025, 'mes', 'Registro de compras y ventas, declaraciones mensuales y archivo.', "Estudiante de Contabilidad desde 6.° ciclo\nExcel intermedio", 2, 'lunes-tarde martes-tarde miercoles-tarde jueves-tarde viernes-tarde'],
            ['ccori', 'Asistente de archivo (por horas)', 'por horas', 9, 'hora', 'Ordenar y digitalizar documentos de clientes.', "Orden y reserva", 1, 'sabado-manana'],
            ['pukara', 'Practicante de ingeniería civil', 'practicas', 1200, 'mes', 'Apoyo en metrados, control de obra y planos en AutoCAD.', "Estudiante de Ingeniería Civil desde 8.° ciclo\nAutoCAD y Excel", 2, 'lunes-manana martes-manana miercoles-manana jueves-manana viernes-manana'],
            ['pukara', 'Asistente de topografía (sábados)', 'por horas', 15, 'hora', 'Apoyar en levantamientos topográficos en obras de San Jerónimo.', "Estudiante de Ingeniería Civil o Geomática", 1, 'sabado-manana sabado-tarde'],
            ['mamaqocha', 'Practicante de arquitectura', 'practicas', 1025, 'mes', 'Modelado 3D, maquetas y apoyo en expedientes municipales.', "Estudiante de Arquitectura\nSketchUp o Revit", 1, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['mamaqocha', 'Dibujante CAD por horas', 'por horas', 15, 'hora', 'Pasar planos a AutoCAD a partir de croquis.', "AutoCAD intermedio", 1, 'martes-noche jueves-noche'],
            ['contact', 'Teleoperador/a de atención al cliente', 'medio tiempo', 1025, 'mes', 'Atender llamadas de clientes de una empresa de internet. Capacitación pagada.', "Buena dicción\nSecundaria completa", 8, 'lunes-tarde martes-tarde miercoles-tarde jueves-tarde viernes-tarde'],
            ['contact', 'Teleoperador/a turno noche', 'medio tiempo', 1100, 'mes', 'Atención de reclamos en horario nocturno.', "Buena dicción\nManejo de computadora", 4, 'lunes-noche martes-noche miercoles-noche jueves-noche'],
            ['contact', 'Agente bilingüe fines de semana', 'por horas', 15, 'hora', 'Atención en inglés a clientes de una aerolínea.', "Inglés avanzado", 2, 'sabado-manana domingo-manana'],
            ['alpaca', 'Vendedor/a de tienda de alpaca', 'medio tiempo', 45, 'dia', 'Vender prendas de alpaca a turistas y mantener la tienda ordenada.', "Inglés básico\nFacilidad para vender", 2, 'viernes-tarde sabado-tarde domingo-tarde'],
            ['alpaca', 'Modelo y ayudante de fotos para redes', 'por horas', 20, 'hora', 'Apoyar en sesiones de fotos de la nueva colección.', "Ganas de aparecer en cámara", 2, 'sabado-manana'],
            ['pukllay', 'Animador/a de fiestas infantiles', 'por horas', 25, 'hora', 'Animar cumpleaños con juegos, música y globoflexia.', "Carisma\nBuen trato con niños", 3, 'sabado-tarde domingo-tarde'],
            ['pukllay', 'Cuidador/a de ludoteca', 'medio tiempo', 35, 'dia', 'Acompañar a los niños mientras juegan y hacen tareas.', "Estudiante de Educación o Psicología (deseable)", 2, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['panvalle', 'Ayudante de panadería (madrugada)', 'medio tiempo', 40, 'dia', 'Preparar la masa y hornear el pan del día.', "Disponibilidad desde las 4:00 a. m.", 1, 'sabado-manana domingo-manana'],
            ['panvalle', 'Vendedor/a de mostrador', 'medio tiempo', 35, 'dia', 'Atender a los clientes y cobrar.', "Trato amable", 1, 'lunes-tarde martes-tarde jueves-tarde'],
            ['angostura', 'Ayudante de cafetería frente a la universidad', 'medio tiempo', 35, 'dia', 'Preparar sándwiches, jugos y atender a estudiantes de la U. Continental.', "Puntualidad\nRapidez en hora punta", 2, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['angostura', 'Cajero/a por horas (mañanas)', 'por horas', 9, 'hora', 'Cobrar en caja en el horario de desayunos.', "Manejo básico de caja", 1, 'martes-manana jueves-manana'],
            ['mishi', 'Asistente de veterinaria', 'medio tiempo', 900, 'mes', 'Apoyar en consultas, baños y peluquería canina.', "Estudiante de Veterinaria o afines\nAmor por los animales", 1, 'lunes-tarde martes-tarde miercoles-tarde jueves-tarde viernes-tarde'],
            ['mishi', 'Paseador/a de perros', 'por horas', 12, 'hora', 'Pasear perros de clientes por Santiago y Wanchaq.', "Responsabilidad\nGusto por los perros", 3, 'sabado-manana domingo-manana'],
            ['grafik', 'Operador/a de impresora gigante', 'medio tiempo', 40, 'dia', 'Imprimir banners y gigantografías; acabados.', "Ganas de aprender\nCuidado con los equipos", 1, 'lunes-tarde miercoles-tarde viernes-tarde'],
            ['grafik', 'Diseñador/a gráfico practicante', 'practicas', 1025, 'mes', 'Diseño de logos, tarjetas y publicidad para clientes.', "Estudiante de Diseño Gráfico o Publicidad\nIllustrator y Photoshop", 1, 'lunes-manana martes-manana miercoles-manana jueves-manana'],
            ['tunki', 'Practicante de administración', 'practicas', 1025, 'mes', 'Control de inventario, compras y reportes de ventas del café.', "Estudiante de Administración\nExcel intermedio", 1, 'lunes-manana miercoles-manana viernes-manana'],
            ['killa', 'Practicante de sistemas (soporte)', 'practicas', 1025, 'mes', 'Soporte a computadoras, red wifi y sistema de reservas del hotel.', "Estudiante de Ingeniería de Sistemas\nRedes básicas", 1, 'martes-tarde jueves-tarde sabado-manana'],
            ['qocha', 'Bartender fin de semana', 'por horas', 14, 'hora', 'Preparar cócteles y bebidas calientes en la barra.', "Mayor de 18 años\nExperiencia en barra (deseable)", 1, 'viernes-noche sabado-noche'],
            ['andeanpath', 'Promotor/a de tours en la Plaza de Armas', 'por horas', 12, 'hora', 'Informar a turistas sobre los tours y llevarlos a la oficina.', "Inglés básico\nFacilidad de palabra", 4, 'sabado-tarde domingo-tarde'],
            ['saber', 'Ayudante de librería (inicio de clases)', 'por horas', 9, 'hora', 'Apoyar en la venta de útiles en temporada escolar.', "Disponibilidad inmediata", 3, 'lunes-noche martes-noche miercoles-noche'],
            ['fuerza', 'Practicante de nutrición deportiva', 'practicas', 1025, 'mes', 'Evaluaciones nutricionales y planes de alimentación para socios.', "Estudiante de Nutrición desde 7.° ciclo", 1, 'martes-manana jueves-manana sabado-manana'],
            ['rimay', 'Corrector/a de exámenes de inglés', 'por horas', 15, 'hora', 'Corregir exámenes escritos de los alumnos.', "Inglés avanzado", 1, 'domingo-tarde'],
        ];

        $minutos = [];
        foreach ($ofertas as $i => $o) {
            $minutos[] = $i < 6 ? mt_rand(30, 1440) : ($i < 15 ? mt_rand(1440, 4 * 1440) : mt_rand(4 * 1440, 45 * 1440));
        }
        $this->mezclar($minutos);

        $creados = [];
        foreach ($ofertas as $i => [$clave, $titulo, $modalidad, $pago, $por, $descripcion, $requisitos, $vacantes, $turnos]) {
            $e = $emp[$clave];
            $t = $e['usuario']->trabajos()->create([
                'titulo' => $titulo, 'empresa' => $e['nombre'], 'distrito' => $e['distrito'], 'modalidad' => $modalidad,
                'pago' => $pago, 'pago_por' => $por, 'direccion' => $e['direccion'],
                'lat' => round($e['lat'] + mt_rand(-6, 6) / 10000, 7), 'lng' => round($e['lng'] + mt_rand(-6, 6) / 10000, 7),
                'descripcion' => $descripcion, 'requisitos' => $requisitos, 'vacantes' => $vacantes,
            ]);
            foreach (explode(' ', $turnos) as $turno) {
                [$dia, $parte] = explode('-', $turno);
                $t->horarios()->create(['dia' => $dia, 'turno' => $parte]);
            }
            $this->fecha('trabajos', $t->id, $ahora->copy()->subMinutes($minutos[$i]));
            $creados[] = $t;
        }

        // algunas postulaciones para que los paneles de las empresas tengan movimiento
        $this->mezclar($creados);
        foreach (array_slice($creados, 0, 20) as $t) {
            $quienes = $estudiantes;
            $this->mezclar($quienes);
            foreach (array_slice($quienes, 0, mt_rand(1, 3)) as $est) {
                $t->postulaciones()->create(['usuario_id' => $est->id, 'estado' => $this->uno(['pendiente', 'pendiente', 'aceptada', 'rechazada']),
                    'mensaje' => $this->uno(['Tengo disponibilidad en ese horario.', 'Me interesa mucho, ya trabajé en algo parecido.', null])]);
            }
        }
    }

    // ---------------------------------------------------------------- ayudas

    private function usuario($nombre, $email, $dni, $celular, $rol, $procedencia = null): Usuario
    {
        return Usuario::firstOrCreate(['email' => $email], [
            'nombre' => $nombre, 'dni' => $dni, 'celular' => $celular, 'rol' => $rol, 'procedencia' => $procedencia,
            'verificacion' => $rol === 'estudiante' ? 'no_aplica' : 'verificado', 'password' => '123456',
            'email_verificado_at' => now(),
        ]);
    }

    private function fecha(string $tabla, int $id, Carbon $cuando): void
    {
        DB::table($tabla)->where('id', $id)->update(['created_at' => $cuando, 'updated_at' => $cuando]);
    }

    private function uno(array $opciones)
    {
        return $opciones[mt_rand(0, count($opciones) - 1)];
    }

    // mezcla usando mt_rand para que con la misma semilla salga siempre igual
    private function mezclar(array &$lista): void
    {
        for ($i = count($lista) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$lista[$i], $lista[$j]] = [$lista[$j], $lista[$i]];
        }
    }

    private function sinTildes(string $texto): string
    {
        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Á' => 'A', 'É' => 'E']);
    }
}
