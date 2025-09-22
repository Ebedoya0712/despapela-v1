<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Company; // 1. Importamos el modelo Company
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     */
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->with('role')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_active' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado con éxito.');
    }


    public function toggleStatus(User $user)
    {
    // Protección para que el admin no se desactive a sí mismo
    if (auth()->user()->id == $user->id) {
        return back()->with('error', 'No puedes desactivar tu propia cuenta.');
    }

    // Invertimos el estado actual del usuario
    $user->update(['is_active' => !$user->is_active]);

    $status = $user->is_active ? 'activado' : 'desactivado';

    return back()->with('success', "El usuario {$user->name} ha sido {$status}.");
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Actualiza un usuario existente en la base de datos.
     */
    public function update(Request $request, User $user)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
        'role_id' => ['required', 'exists:roles,id'],
        'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        
        // --- NUEVAS REGLAS DE VALIDACIÓN ---
        'dni' => ['nullable', 'string', 'max:20'],
        'phone' => ['nullable', 'string', 'max:20'],
        'address' => ['nullable', 'string', 'max:500'],
        'is_active' => ['required', 'boolean'],
    ]);

    // --- RECOPILAMOS TODOS LOS DATOS A ACTUALIZAR ---
    $userData = $request->only('name', 'email', 'role_id', 'dni', 'phone', 'address', 'is_active');

    if ($request->filled('password')) {
        $userData['password'] = Hash::make($request->password);
    }

    $user->update($userData);

    return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado con éxito.');
}

    /**
     * Elimina un usuario de la base de datos.
     */
    public function destroy(User $user)
    {
        if (auth()->user()->id == $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
        }
        
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado con éxito.');
    }
    
    /**
     * Muestra el formulario para asignar empresas a un Gestor.
     */
    public function assignCompanyForm(User $user)
    {
        // 2. Verificamos que el usuario sea un Gestor
        if ($user->role->name !== 'Gestor') {
            return redirect()->route('admin.users.index')->with('error', 'Solo se pueden asignar empresas a los Gestores.');
        }

        // 3. Obtenemos todas las empresas y las que ya gestiona este usuario
        $companies = Company::all();
        $managedCompanyIds = $user->managedCompanies->pluck('id')->toArray();

        return view('admin.users.assign-company', compact('user', 'companies', 'managedCompanyIds'));
    }

    /**
     * Sincroniza las empresas asignadas a un Gestor.
     */
    public function syncCompanies(Request $request, User $user)
    {
        // 4. Validación: nos aseguramos de recibir un array de IDs de empresas existentes
        $request->validate([
            'companies' => 'nullable|array',
            'companies.*' => 'exists:companies,id',
        ]);

        // 5. Desasignamos todas las empresas que este gestor pudiera tener
        Company::where('gestor_id', $user->id)->update(['gestor_id' => null]);
        
        // 6. Asignamos las nuevas empresas seleccionadas
        if ($request->has('companies')) {
            Company::whereIn('id', $request->companies)->update(['gestor_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Empresas asignadas con éxito al gestor: ' . $user->name);
    }
}

