<?php

namespace App\Http\Controllers\Puntoventa;

use App\Product;
use App\VentasProductos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\UploadTrait;

class CatalogController extends Controller
{

    use UploadTrait;

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
        $products = Product::where('active', 1)->orderBy('id', 'desc')->latest()->paginate(15);

        return view('puntoventa.productos.index',compact('products'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorias = [
            '1' => 'Barra',
            '2' => 'Cocina (Alitas)',
            '3' => 'Cocina (General)'
        ];
        return view('puntoventa.productos.create',compact('categorias'));
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
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
            'price' => 'required',
            'type' => 'required'
            //'product_image'     =>  'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // if ($request->has('product_image')) {
        //     // Get image file
        //     $image = $request->file('product_image');
        //     // Make a image name based on user name and current timestamp
        //     $name = str_slug($request->input('name')).'_'.time();
        //     // Define folder path
        //     $folder = '/uploads/images/';
        //     // Make a file path where image will be stored [ folder path + file name + file extension]
        //     $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
        //     // Upload image
        //     $this->uploadOne($image, $folder, 'public', $name);
        //     // Set user profile image path in database to filePath
        //     $attributes['image'] = $filePath;
        // }
        $attributes = array_merge($attributes, array("active" => 1));
        Product::create($attributes);

        return redirect()->route('catalogos.index')
                        ->with('success','Producto Creado Satisfactoriamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function show(Product $catalogo)
    {
        return view('puntoventa.productos.show',compact('catalogo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $catalogo)
    {
        $categorias = [
            '1' => 'Barra',
            '2' => 'Cocina (Alitas)',
            '3' => 'Cocina (General)'
        ];
        return view('puntoventa.productos.edit',compact('catalogo', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $catalogo)
    {
        $attributes = $request->all();
        $request->validate([
            'name' => 'required',
            'detail' => 'required',
            'price' => 'required',
            'type' => 'required'
        ]);

        // if ($request->has('product_image')) {
        //     // Get image file
        //     $image = $request->file('product_image');
        //     // Make a image name based on user name and current timestamp
        //     //$name = str_slug($request->input('name')).'_'.time();
        //     $name = $request->input('name');
        //     // Define folder path
        //     $folder = '/uploads/images/';
        //     // Make a file path where image will be stored [ folder path + file name + file extension]
        //     $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
        //     // Upload image
        //     $this->uploadOne($image, $folder, 'public', $name);
        //     // Set user profile image path in database to filePath
        //     $attributes['image'] = $filePath;
        // }

        $catalogo->update($attributes);

        return redirect()->route('catalogos.index')
                        ->with('success','Producto Actualizado Satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $catalogo)
    {
        $catalogo->active = 0;
        $catalogo->save();

        return redirect()->route('catalogos.index')
                        ->with('success','Producto Eliminado Satisfactoriamente');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function findByType()
    {
        // $drinks = Product::where('type', 1)->get();
        // $food = Product::where('type', 2)->get();
        //
        // return ([
        //   $drinks, $food
        // ]);
        $ventaProductos = VentasProductos::where('IdVenta', 25)->get();
        foreach ($ventaProductos as $value) {
            $value->product;
        }
        return $ventaProductos;

    }
}
