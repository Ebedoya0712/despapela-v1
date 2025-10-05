<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str; // Necesario para generar la contraseña
use App\Notifications\NewStaffCredentials; // ¡Importamos la notificación!

class StaffController extends Controller
{
    /**
     * Muestra una lista del personal (Técnicos y Trabajadores) de una empresa.
     */
    public function index(Company $company)
    {
        // Cargamos el personal y lo separamos por rol
        $staff = $company->staff()->with('role')->get();
        
        $technicians = $staff->filter(fn($user) => $user->role->name === 'Técnico');
        $workers = $staff->filter(fn($user) => $user->role->name === 'Trabajador');

        return view('gestor.staff.index', compact('company', 'technicians', 'workers'));
    }

    /**
     * Muestra el formulario para crear un nuevo miembro del personal.
     */
    public function create(Company $company)
    {
        return view('gestor.staff.create', compact('company'));
    }

    /**
     * Guarda un nuevo miembro del personal y lo asigna a la empresa.
     */
    public function store(Request $request, Company $company)
    {
        // 1. Validaciones
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            // Hacemos que la contraseña sea opcional, pero si se provee, debe ser confirmada y cumplir reglas.
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], 
            'role' => ['required', 'in:Técnico,Trabajador'],
        ]);

        // 2. Lógica Condicional de Contraseña
        $role = Role::where('name', $request->role)->firstOrFail();
        
        // Verificamos si el gestor proporcionó una contraseña
        if ($request->filled('password')) {
            $rawPassword = $request->password;
        } else {
            // Si no proporcionó una, generamos una aleatoria
            $rawPassword = Str::random(10); 
        }
        
        // 3. Creación del Usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($rawPassword), // Usamos la contraseña (ingresada o generada)
            'role_id' => $role->id,
            'is_active' => true, //  TAREA CUMPLIDA: Usuario activo por defecto
        ]);

        // 4. Asignación a la Empresa
        $company->staff()->attach($user->id);

        // 5. Envío de Email con la Contraseña y Recomendación
        try {
            //  TAREA CUMPLIDA: Enviamos la contraseña real (la generada o la ingresada por el gestor)
            $user->notify(new NewStaffCredentials(
                $user->email, 
                $rawPassword, // Pasamos la contraseña en texto plano para el email
                $role->name, 
                $company->name
            ));
        } catch (\Exception $e) {
            \Log::error("Fallo el envío de correo para el usuario ID {$user->id}: " . $e->getMessage());
            return redirect()->route('gestor.companies.staff.index', $company->id)
                             ->with('warning', 'Usuario creado y asignado. ¡ADVERTENCIA! Falló el envío del correo con la contraseña.');
        }

        return redirect()->route('gestor.companies.staff.index', $company->id)
                         ->with('success', "Usuario {$request->role} creado, asignado y notificado con sus credenciales.");
    }


    public function toggleStatus(Company $company, User $staff)
{
    // Simplemente invertimos el estado actual
    $staff->update(['is_active' => !$staff->is_active]);

    $status = $staff->is_active ? 'activado' : 'desactivado';

    return back()->with('success', "Usuario {$staff->name} ha sido {$status}.");
}

    public function edit(Company $company, User $staff)
    {
        return view('gestor.staff.edit', compact('company', 'staff'));
    }

    /**
     * Actualiza un miembro del personal.
     */
    public function update(Request $request, Company $company, User $staff)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$staff->id],
            // Opcionalmente, puedes eliminar esta validación si ya no permites cambiar la contraseña en la edición.
            // Si la dejas, asegúrate de que el formulario de edición tenga los campos.
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], 
        ]);

        $userData = $request->only('name', 'email');

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $staff->update($userData);

        return redirect()->route('gestor.companies.staff.index', $company->id)
                            ->with('success', 'Usuario actualizado con éxito.');
    }

    public function destroy(Company $company, User $staff)
    {
        // El método detach quita la relación en la tabla pivote.
        // El método delete() elimina el registro del usuario.
        // Es importante hacer ambos para una limpieza completa.
        $company->staff()->detach($staff->id);
        $staff->delete();

        return redirect()->route('gestor.companies.staff.index', $company->id)
                            ->with('success', 'Usuario eliminado con éxito.');
    }

}
