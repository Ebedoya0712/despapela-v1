<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Asegúrate de que el modelo Role está importado
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validamos todos los campos del formulario de registro
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'dni' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:20'],
            'bank_account' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        // 2. Buscamos el rol "Gestor" para asignarlo al nuevo usuario
        $gestorRole = Role::where('name', 'Gestor')->firstOrFail();

        // 3. Creamos el usuario con todos sus datos
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dni' => $request->dni,
            'phone' => $request->phone,
            'bank_account' => $request->bank_account,
            'address' => $request->address,
            'role_id' => $gestorRole->id, // Asignamos el rol de Gestor
            'is_active' => true,         // El Gestor se crea como ACTIVO por defecto
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}