<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $notificaciones = $request->user()->notifications()->take(50)->get();

        return view('notificaciones', compact('notificaciones'));
    }

    // marca como leida y lleva a la pagina
    public function abrir(Request $request, string $id)
    {
        $notificacion = $request->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        return redirect($notificacion->data['url'] ?? route('panel'));
    }

    // la campana pregunta cada 10 segundos si hay algo nuevo
    public function estado(Request $request)
    {
        $yo = $request->user();
        $ultima = $yo->unreadNotifications()->first();

        return response()->json([
            'notificaciones' => $yo->unreadNotifications()->count(),
            'mensajes' => $yo->mensajesSinLeer(),
            'ultima' => $ultima ? $ultima->data['texto'] : null,
        ]);
    }

    public function leerTodas(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('ok', 'Todas las notificaciones quedaron como leídas.');
    }
}
