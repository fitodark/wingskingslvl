<?php

namespace App\Http\Controllers\Users;

use App\User;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\PinAuthService;

class UsersController extends Controller
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
    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['admin']);
        $users = User::latest()->paginate(15);

        return view('config.users.index',compact('users'))
          ->with('i', (request()->input('page', 1) - 1) * 15);
    }

        /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['required', 'string', 'min:4', 'confirmed'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $disablecheck = false;
        $disablecheckpin = false;
        return view('config.users.create', compact('disablecheck', 'disablecheckpin'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['required', 'string', 'min:4', 'confirmed', $this->reglaPinUnico()],
            'idrol' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect('usuarios/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $newUser = User::Create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'pin' => Hash::make($request->get('pin')),
            'status' => true
        ]);

        $newUser->roles()->attach(Role::where('id', $request->get('idrol'))->first());

        return redirect()->route('usuarios')
                ->with('success','Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(String $id)
    {
        $user = User::find($id);
        $rol = $user->stringRole->first();
        $disablecheck = true;
        $disablecheckpin = true;
        $arraystatus = [
            '0' => 'Inactivo',
            '1' => 'Activo'
        ];
        return view('config.users.edit', compact('user', 'rol', 'disablecheck', 'arraystatus', 'disablecheckpin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, String $id)
    {
        // Antes: si no se cambiaba contraseña ni PIN, $validator no existía y la edición tronaba;
        // y si se cambiaban ambos, la contraseña no se validaba.
        $reglas = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'idrol' => ['required'],
        ];
        if (!empty($request->get('change-password')) && $request->get('change-password') == 'on') {
            $reglas['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }
        if (!empty($request->get('change-pin')) && $request->get('change-pin') == 'on') {
            $reglas['pin'] = ['required', 'string', 'min:4', 'confirmed', $this->reglaPinUnico($id)];
        }
        $validator = \Validator::make($request->all(), $reglas);

        if ($validator->fails()) {
            return redirect()->route('usuarios.edit', ['id' => $id])
                        ->withErrors($validator)
                        ->withInput();
        }

        $edituser = User::find($id);

        $edituser->name = $request->get('name');
        $edituser->email = $request->get('email');
        if ($request->get('change-password') && $request->get('change-password') == 'on') {
            $edituser->password = Hash::make($request->get('password'));
        }
        if ($request->get('change-pin') && $request->get('change-pin') == 'on') {
            $edituser->pin = Hash::make($request->get('pin'));
        }
        $edituser->save();
        $edituser->roles()->detach();
        $edituser->roles()->attach(Role::where('id', $request->get('idrol'))->first());

        return redirect()->route('usuarios')
                ->with('success','Usuario editado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(String $userId)
    {
        //
        $deleteuser = User::find($userId);
        $deleteuser->status = false;
        $deleteuser->save();

        return redirect()->route('usuarios')
                        ->with('success','Usuario eliminado correctamente');
    }

    /**
     * El PIN identifica a quien autoriza una eliminación, por eso no puede repetirse.
     */
    private function reglaPinUnico($exceptoUserId = null)
    {
        return function ($attribute, $value, $fail) use ($exceptoUserId) {
            if (app(PinAuthService::class)->pinEnUso($value, $exceptoUserId)) {
                $fail('Ese PIN ya lo usa otro usuario. Elige uno distinto.');
            }
        };
    }
}
