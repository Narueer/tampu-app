<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\EventoSeguridad;
use App\Models\Habitacion;
use App\Models\Postulacion;
use App\Models\Reporte;
use App\Models\Solicitud;
use App\Models\Trabajo;
use App\Models\Usuario;
use App\Notifications\Aviso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function panel()
    {
        $datos = [
            'estudiantes' => Usuario::where('rol', 'estudiante')->count(),
            'verificados' => Usuario::where('verificacion', 'verificado')->count(),
            'habitaciones' => Habitacion::publicadas()->count(),
            'trabajos' => Trabajo::publicadas()->count(),
            'solicitudes' => Solicitud::count(),
            'aceptadas' => Solicitud::where('estado', 'aceptada')->count(),
            'postulaciones' => Postulacion::count(),
            'reportes' => Reporte::where('estado', 'pendiente')->count(),
            'contactos' => Contacto::where('leido', false)->count(),
        ];

        $pendientes = Usuario::where('verificacion', 'pendiente')->oldest()->get();

        // datos para los graficos
        $graficos = [
            'distritos' => Habitacion::publicadas()
                ->selectRaw('distrito, count(*) as total, round(avg(precio)) as promedio')
                ->groupBy('distrito')->orderBy('distrito')->get(),
            'solicitudes' => Solicitud::selectRaw('estado, count(*) as total')
                ->groupBy('estado')->pluck('total', 'estado'),
            'postulaciones' => Postulacion::selectRaw('estado, count(*) as total')
                ->groupBy('estado')->pluck('total', 'estado'),
            'roles' => Usuario::where('rol', '!=', 'admin')->selectRaw('rol, count(*) as total')
                ->groupBy('rol')->pluck('total', 'rol'),
        ];

        // lo ultimo que paso, para el costado del panel
        $actividad = EventoSeguridad::with('usuario')->latest('created_at')->latest('id')->take(7)->get();
        $nuevos = Usuario::where('rol', '!=', 'admin')->latest()->latest('id')->take(5)->get();

        return view('admin.panel', compact('datos', 'pendientes', 'graficos', 'actividad', 'nuevos'));
    }

    // filtro común "desde / hasta" (fechas en formato del calendario, AAAA-MM-DD)
    private function entreFechas($consulta, Request $request, string $columna = 'created_at')
    {
        $fecha = fn ($v) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
        if ($desde = $fecha($request->desde)) {
            $consulta->where($columna, '>=', $desde . ' 00:00:00');
        }
        if ($hasta = $fecha($request->hasta)) {
            $consulta->where($columna, '<=', $hasta . ' 23:59:59');
        }

        return $consulta;
    }

    public function usuarios(Request $request)
    {
        $consulta = Usuario::where('rol', '!=', 'admin');
        if ($request->filled('rol')) {
            $consulta->where('rol', $request->rol);
        }
        // nombre, correo, DNI o celular (sin importar mayúsculas)
        if ($request->filled('buscar')) {
            $b = '%' . trim($request->buscar) . '%';
            $consulta->where(fn ($q) => $q->whereLike('nombre', $b)->orWhereLike('email', $b)->orWhereLike('dni', $b)->orWhereLike('celular', $b)->orWhereLike('procedencia', $b));
        }
        if (in_array($request->verificacion, ['verificado', 'pendiente', 'rechazado', 'no_aplica'], true)) {
            $consulta->where('verificacion', $request->verificacion);
        }
        if ($request->cuenta === 'suspendida') {
            $consulta->where('suspendido', true);
        } elseif ($request->cuenta === 'activa') {
            $consulta->where('suspendido', false);
        }
        $this->entreFechas($consulta, $request);
        $orden = match ($request->orden) {
            'antiguos' => ['created_at', 'asc'],
            'nombre' => ['nombre', 'asc'],
            default => ['created_at', 'desc'],
        };
        $usuarios = $consulta->withCount(['habitaciones', 'trabajos'])->orderBy(...$orden)->orderBy('id')->get();

        // sugerencias para el buscador: nombres y correos de todos los usuarios
        $sugerencias = Usuario::where('rol', '!=', 'admin')->orderBy('nombre')->get(['nombre', 'email']);

        return view('admin.usuarios', compact('usuarios', 'sugerencias'));
    }

    // solo el admin ve el documento
    public function documento(Usuario $usuario)
    {
        $disco = Storage::disk('documentos');
        abort_unless($usuario->documento && $disco->exists($usuario->documento), 404);

        return $disco->response($usuario->documento);
    }

    public function verificar(Request $request, Usuario $usuario)
    {
        $request->validate(['accion' => 'required|in:verificado,rechazado']);
        abort_if($usuario->verificacion === 'no_aplica', 422);

        $usuario->update(['verificacion' => $request->accion]);
        EventoSeguridad::registrar('verificacion', 'El admin marcó a ' . $usuario->email . ' como ' . $request->accion, $usuario);

        $ok = $request->accion === 'verificado';
        Aviso::enviar($usuario,
            $ok ? 'Tu cuenta fue verificada. Ya puedes publicar.' : 'Tu documento fue rechazado por el administrador.',
            route('panel'), 'patch-check',
            $ok ? 'Tu cuenta fue verificada' : 'No pudimos verificar tu cuenta',
            $ok ? ['Revisamos tu documento y tu cuenta ya está verificada.', 'Ya puedes publicar en TAMPU. Las publicaciones con buenas fotos reciben más mensajes.']
                : ['Revisamos tu documento y no pudimos verificarlo (puede estar borroso o no coincidir con tus datos).', 'Sube una foto clara de tu DNI o RUC desde Mi perfil y lo revisamos de nuevo.'],
            $ok ? 'Publicar ahora' : 'Ir a mi perfil');

        $texto = $request->accion === 'verificado' ? 'verificado. Ya puede publicar.' : 'rechazado.';

        return back()->with('ok', $usuario->nombre . ' fue ' . $texto);
    }

    public function reportes(Request $request)
    {
        $estado = $request->input('estado', 'pendiente');
        $consulta = Reporte::with(['autor', 'habitacion.arrendador', 'trabajo.empleador'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->when($request->filled('motivo'), fn ($q) => $q->where('motivo', $request->motivo));
        $reportes = $this->entreFechas($consulta, $request)->latest()->get();

        return view('admin.reportes', compact('reportes', 'estado'));
    }

    public function resolver(Request $request, Reporte $reporte)
    {
        $request->validate(['accion' => 'required|in:descartado,sancionado']);
        $reporte->update(['estado' => $request->accion]);

        if ($request->accion === 'descartado') {
            return back()->with('ok', 'Reporte descartado.');
        }

        // sancion: se suspende al dueño y se ocultan sus publicaciones
        $denunciado = $reporte->denunciado();
        if ($denunciado && $denunciado->rol !== 'admin') {
            $denunciado->update(['suspendido' => true]);
            EventoSeguridad::registrar('suspension', 'Por reporte: ' . $reporte->motivo, $denunciado);
        }

        return back()->with('ok', 'Se suspendió la cuenta de ' . ($denunciado->nombre ?? 'el usuario') . '. Sus publicaciones ya no se muestran.');
    }

    public function suspender(Usuario $usuario)
    {
        abort_if($usuario->rol === 'admin', 403);
        $usuario->update(['suspendido' => ! $usuario->suspendido]);
        EventoSeguridad::registrar($usuario->suspendido ? 'suspension' : 'reactivacion', 'Desde la lista de usuarios', $usuario);

        return back()->with('ok', $usuario->nombre . ($usuario->suspendido ? ' fue suspendido.' : ' fue reactivado.'));
    }

    public function contactos(Request $request)
    {
        $consulta = Contacto::query()
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $b = '%' . trim($request->buscar) . '%';
                $q->where(fn ($w) => $w->whereLike('nombre', $b)->orWhereLike('email', $b)->orWhereLike('mensaje', $b));
            })
            ->when($request->filled('asunto'), fn ($q) => $q->where('asunto', $request->asunto))
            ->when($request->estado === 'nuevos', fn ($q) => $q->where('leido', false))
            ->when($request->estado === 'leidos', fn ($q) => $q->where('leido', true));
        $contactos = $this->entreFechas($consulta, $request)->latest()->get();

        return view('admin.contactos', compact('contactos'));
    }

    public function leerContacto(Contacto $contacto)
    {
        $contacto->update(['leido' => ! $contacto->leido]);

        return back();
    }

    // bitacora de seguridad
    public function seguridad(Request $request)
    {
        $tipo = $request->input('tipo');
        $eventos = EventoSeguridad::with('usuario')
            ->when($tipo === 'alertas', fn ($q) => $q->whereIn('tipo', collect(EventoSeguridad::TIPOS)->filter(fn ($t) => $t[1])->keys()))
            ->when($tipo && $tipo !== 'alertas', fn ($q) => $q->where('tipo', $tipo))
            ->when($request->filled('buscar'), fn ($q) => $q->whereLike('email', '%' . trim($request->buscar) . '%'))
            ->when($request->filled('ip'), fn ($q) => $q->where('ip', trim($request->ip)));
        $eventos = $this->entreFechas($eventos, $request)->latest('created_at')->latest('id')->paginate(30)->withQueryString();
        $correos = EventoSeguridad::whereNotNull('email')->distinct()->orderBy('email')->limit(300)->pluck('email');

        // resumen de las ultimas 24 horas
        $desde = now()->subDay();
        $resumen = [
            'login' => EventoSeguridad::where('tipo', 'login')->where('created_at', '>=', $desde)->count(),
            'login_fallido' => EventoSeguridad::where('tipo', 'login_fallido')->where('created_at', '>=', $desde)->count(),
            'bloqueo' => EventoSeguridad::where('tipo', 'bloqueo')->where('created_at', '>=', $desde)->count(),
            'acceso_denegado' => EventoSeguridad::where('tipo', 'acceso_denegado')->where('created_at', '>=', $desde)->count(),
        ];

        return view('admin.seguridad', compact('eventos', 'resumen', 'tipo', 'correos'));
    }
}
