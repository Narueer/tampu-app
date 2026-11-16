<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
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

        return view('admin.panel', compact('datos', 'pendientes', 'graficos'));
    }

    public function usuarios(Request $request)
    {
        $consulta = Usuario::where('rol', '!=', 'admin');
        if ($request->filled('rol')) {
            $consulta->where('rol', $request->rol);
        }
        $usuarios = $consulta->withCount(['habitaciones', 'trabajos'])->latest()->get();

        return view('admin.usuarios', compact('usuarios'));
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

        $usuario->notify(new Aviso(
            $request->accion === 'verificado' ? 'Tu cuenta fue verificada. Ya puedes publicar.' : 'Tu documento fue rechazado por el administrador.',
            route('panel'), 'patch-check'
        ));

        $texto = $request->accion === 'verificado' ? 'verificado. Ya puede publicar.' : 'rechazado.';

        return back()->with('ok', $usuario->nombre . ' fue ' . $texto);
    }

    public function reportes(Request $request)
    {
        $estado = $request->input('estado', 'pendiente');
        $reportes = Reporte::with(['autor', 'habitacion.arrendador', 'trabajo.empleador'])
            ->when($estado !== 'todos', fn ($q) => $q->where('estado', $estado))
            ->latest()->get();

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
        }

        return back()->with('ok', 'Se suspendió la cuenta de ' . ($denunciado->nombre ?? 'el usuario') . '. Sus publicaciones ya no se muestran.');
    }

    public function suspender(Usuario $usuario)
    {
        abort_if($usuario->rol === 'admin', 403);
        $usuario->update(['suspendido' => ! $usuario->suspendido]);

        return back()->with('ok', $usuario->nombre . ($usuario->suspendido ? ' fue suspendido.' : ' fue reactivado.'));
    }

    public function contactos()
    {
        $contactos = Contacto::latest()->get();

        return view('admin.contactos', compact('contactos'));
    }

    public function leerContacto(Contacto $contacto)
    {
        $contacto->update(['leido' => ! $contacto->leido]);

        return back();
    }
}
