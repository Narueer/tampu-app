<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Usuario;
use App\Notifications\Aviso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactoController extends Controller
{
    public function formulario()
    {
        return view('contacto');
    }

    public function enviar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100',
            'asunto' => ['required', Rule::in(Contacto::ASUNTOS)],
            'mensaje' => 'required|string|min:10|max:1000',
        ]);

        $contacto = Contacto::create($datos);

        foreach (Usuario::where('rol', 'admin')->get() as $admin) {
            $admin->notify(new Aviso('Nuevo mensaje de contacto: ' . $contacto->asunto, route('admin.contactos'), 'envelope'));
        }

        // copia al correo del equipo; si no hay correo configurado no pasa nada
        try {
            Mail::raw("De: {$contacto->nombre} <{$contacto->email}>\nAsunto: {$contacto->asunto}\n\n{$contacto->mensaje}", function ($m) use ($contacto) {
                $m->to(config('tampu.correo'))->replyTo($contacto->email)->subject('[TAMPU] ' . $contacto->asunto);
            });
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el correo de contacto: ' . $e->getMessage());
        }

        return back()->with('ok', 'Gracias por escribirnos. Te responderemos a tu correo lo antes posible.');
    }

    // boletin: guarda el correo como un mensaje para el administrador
    public function boletin(Request $request)
    {
        $datos = $request->validate(['email' => 'required|email|max:100']);

        Contacto::firstOrCreate(
            ['email' => $datos['email'], 'asunto' => 'Boletín de cuartos nuevos'],
            ['nombre' => 'Suscriptor del boletín', 'mensaje' => 'Quiere recibir por correo las habitaciones nuevas que se publiquen.']
        );

        return redirect()->to(route('inicio') . '#boletin')->with('ok', '¡Listo! Te avisaremos cuando se publiquen cuartos nuevos.');
    }
}
