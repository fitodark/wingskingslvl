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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = Client::where('active', 1)->orderBy('id', 'desc')->latest()->paginate(15);

        return view('puntoventa.clientes.index',compact('clients'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('puntoventa.clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes = $request->all();
        // $request->validate([
        //     'name' => 'required',
        //     'address' => 'required',
        //     'phone' => 'required',
        //     'reference' => 'required'
        // ]);
        $validator = \Validator::make($request->all(), [
          'name' => 'required',
          'address' => 'required',
          'phone' => 'required',
          'reference' => 'required'
        ]);

        if ($validator->fails()) {
            if ($request->get('ventaid') && $request->get('type')) {
                return response()->json(['errors' => $validator->errors()->all()]);
            } else {
                return redirect('clientes/create')
                ->withErrors($validator)
                ->withInput();
            }
        }

        $attributes = array_merge($attributes, array("active" => 1));
        $client = Client::create($attributes);

        if ($request->get('ventaid') && $request->get('type')) {
            $venta = Venta::find($request->get('ventaid'));
            $venta->client_id = $client->id;
            $venta->type = $request->get('type');
            $venta->save();
            return redirect()->route('drinksTab', [$venta]);
        } else {
            return redirect()->route('clientes')
            ->with('success','Cliente Creado Satisfactoriamente.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        return view('puntoventa.clientes.show',compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $cliente)
    {
        return view('puntoventa.clientes.edit',compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $cliente)
    {
        $attributes = $request->all();
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'reference' => 'required'
        ]);
        $cliente->update($attributes);
        return redirect()->route('clientes')
                        ->with('success','Cliente Actualizado Satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $cliente)
    {
        $cliente->active = 0;
        $cliente->save();

        return redirect()->route('clientes')
                        ->with('success','Producto Eliminado Satisfactoriamente');
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
        $validator = \Validator::make($request->all(), [
          'clientName' => 'required',
          'clientAddress' => 'required',
          'clientPhone' => 'required',
          'clientReference' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $client = new Client([
            'name' => $request->get('clientName'),
            'phone' => $request->get('clientPhone'),
            'address' => $request->get('clientAddress'),
            'reference' => $request->get('clientReference'),
            'active' => 1
        ]);
        $client->save();
        if ($request->get('ventaid') && $request->get('type')) {
            $venta = Venta::find($request->get('ventaid'));
            $venta->client_id = $client->id;
            $venta->type = $request->get('type');
            $venta->save();
        }
        return redirect()->route('drinksTab', [$venta]);
    }

}
