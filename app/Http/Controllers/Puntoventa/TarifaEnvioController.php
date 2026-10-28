<?php

namespace App\Http\Controllers\Puntoventa;

use App\TarifaEnvio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

/**
 * Catálogo de colonias y tarifas de envío (solo admin). Lo consulta el agente de WhatsApp.
 * No se borra: se desactiva para ocultarla al agente sin perder historial.
 */
class TarifaEnvioController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('q'));

        $tarifas = TarifaEnvio::query()
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where('colonia', 'like', '%' . $buscar . '%');
            })
            ->orderBy('colonia')
            ->paginate(30)
            ->appends(['q' => $buscar]);

        return view('puntoventa.tarifasenvio.index', compact('tarifas', 'buscar'));
    }

    public function create()
    {
        return view('puntoventa.tarifasenvio.create', ['tarifa' => new TarifaEnvio([
            'tipo' => TarifaEnvio::TIPO_COLONIA,
            'envio_gratuito' => true,
        ])]);
    }

    public function store(Request $request)
    {
        TarifaEnvio::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('tarifasenvio.index')
            ->with('success', 'Colonia agregada.');
    }

    public function edit(TarifaEnvio $tarifa)
    {
        return view('puntoventa.tarifasenvio.edit', compact('tarifa'));
    }

    public function update(Request $request, TarifaEnvio $tarifa)
    {
        $tarifa->update($this->validar($request, $tarifa));

        return redirect()->route('tarifasenvio.index')
            ->with('success', 'Colonia actualizada.');
    }

    /**
     * Activa o desactiva la colonia (en lugar de borrarla).
     */
    public function cambiarActivo(TarifaEnvio $tarifa)
    {
        $tarifa->activo = !$tarifa->activo;
        $tarifa->save();

        return redirect()->back()
            ->with('success', $tarifa->colonia . ($tarifa->activo ? ' activada.' : ' desactivada; el agente ya no la ofrece.'));
    }

    /**
     * Si la colonia no tiene envío gratuito, la tarifa express es obligatoria.
     * Vacía = NULL (sin tarifa cargada), nunca 0, para no ofrecer un express "gratis".
     */
    private function validar(Request $request, TarifaEnvio $tarifa = null)
    {
        $datos = $request->validate([
            'colonia' => ['required', 'string', 'max:150',
                Rule::unique('tarifas_envio', 'colonia')->ignore(optional($tarifa)->id)],
            'tipo' => ['required', Rule::in(array_keys(TarifaEnvio::TIPOS))],
            'envio_gratuito' => ['required', 'boolean'],
            'monto_express' => ['nullable', 'required_if:envio_gratuito,0', 'numeric', 'min:0.01', 'max:99999'],
        ], [
            'colonia.unique' => 'Esa colonia ya está en el catálogo.',
            'monto_express.required_if' => 'La tarifa express es obligatoria si la colonia no tiene envío gratuito.',
            'monto_express.min' => 'La tarifa express debe ser mayor a 0.',
        ]);

        $datos['colonia'] = trim($datos['colonia']);
        $datos['envio_gratuito'] = (bool) $datos['envio_gratuito'];
        $datos['monto_express'] = isset($datos['monto_express']) && $datos['monto_express'] !== ''
            ? $datos['monto_express'] : null;

        return $datos;
    }
}
