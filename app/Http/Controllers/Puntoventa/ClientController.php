<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    public function getQueryClient (Request $request) {
        if ($request->get('query')) {
            $query = $request->get('query');
            $data = Client::where('name', 'like', '%'.$query.'%')->get();
            return response()->json($data);
        } else {
            return [];
        }

    }

    public function addClient (Request $request) {
        // dd($request->all());
        $client = new Client([
            'name' => $request->get('clientName'),
            'phone' => $request->get('clientPhone'),
            'address' => $request->get('clientAddress'),
            'reference' => $request->get('clientReference')
        ]);
        $client->save();
        // $venta = $request->get('venta');
        if ($request->get('ventaid')) {
            $venta = Venta::find($request->get('ventaid'));
            $venta->client_id = $client->id;
            $venta->type = $request->get('type');
            $venta->save();
        }
        return redirect()->route('drinksTab', [$venta]);
    }

}
