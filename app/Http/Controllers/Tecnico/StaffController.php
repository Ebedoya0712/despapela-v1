<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Importamos Rule para validaciones únicas que ignoran el ID
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewStaffCredentials; // Importamos la notificación de credenciales
use Illuminate\Contracts\View\View; // Importamos para tipar el retorno de vista
use Illuminate\Http\RedirectResponse; // Importamos para tipar la respuesta de redirección

class StaffController extends Controller
{
    /**
     * Muestra una lista consolidada de todos los trabajadores (staff) 
     * asignados a las empresas gestionadas por el técnico autenticado.
     * Esta función maneja la ruta: /tecnico/workers (GET)
     */
    public function index(): View
    {
        // 1. Obtener las IDs de las empresas gestionadas por este técnico.
        $companyIds = Auth::user()->memberOfCompanies->pluck('id'); 
        
        // 2. Obtener todos los trabajadores (rol 'Trabajador') que pertenecen a estas compañías.
        $workers = User::whereHas('memberOfCompanies', function ($query) use ($companyIds) {
            $query->whereIn('company_id', $companyIds);
        })
        ->whereHas('role', function($query) {
            // Asumiendo que el campo 'name' del modelo Role es 'Trabajador'
            $query->where('name', 'Trabajador'); 
        })
        // Cargar las empresas y el rol del usuario
        ->with(['memberOfCompanies', 'role']) 
        // Excluir usuarios con soft delete
        ->whereNull('deleted_at') 
        ->orderBy('name')
        ->get();

        // Retornamos la vista de listado.
        return view('tecnico.workers.index', compact('workers'));
    }

    /**
     * Muestra el formulario para crear un nuevo trabajador y asociarlo a la empresa.
     */
    public function create(Company $company): View
    {
        // 1. Verificamos que el técnico actual esté asignado a ESTA empresa.
        if (!Auth::user()->memberOfCompanies->contains($company)) {
            abort(403, 'No tienes permiso para gestionar el personal de esta empresa.');
        }

        return view('tecnico.workers.create', compact('company'));
    }

    /**
     * Almacena un nuevo trabajador en la base de datos y lo asocia a la empresa.
     */
    public function store(Request $request, Company $company): RedirectResponse
    {
        // Verificación de asignación de empresa (redundante, pero buena práctica)
        if (!Auth::user()->memberOfCompanies->contains($company)) {
            abort(403, 'No tienes permiso para gestionar el personal de esta empresa.');
        }

        // 1. Validaciones
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);
        
        try {
            // 2. Obtener el Rol 'Trabajador'
            $role = Role::where('name', 'Trabajador')->firstOrFail();

            // 3. Lógica Condicional de Contraseña
            $rawPassword = $request->filled('password') ? $request->password : Str::random(10); 
            
            // 4. Crear el nuevo usuario
            $worker = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($rawPassword), 
                'role_id' => $role->id, 
                'is_active' => true, 
            ]);

            // 5. Asociar el nuevo trabajador a la empresa
            $company->staff()->attach($worker->id); 
            
            // 6. Envío de Email con la Contraseña
            try {
                $worker->notify(new NewStaffCredentials(
                    $worker->email, 
                    $rawPassword, 
                    $role->name, 
                    $company->name
                ));
                $message = 'Trabajador "' . $worker->name . '" creado, asignado y notificado con éxito a ' . $company->name . '.';
            } catch (\Exception $e) {
                Log::error('Error al enviar correo al trabajador creado por Técnico: ' . $e->getMessage());
                $message = 'Trabajador creado y asignado. ¡ADVERTENCIA! Falló el envío del correo con la contraseña.';
            }

            // Redirigimos al listado general de trabajadores
            return redirect()->route('tecnico.workers.index')
                             ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error general al crear o asignar trabajador por Técnico: ' . $e->getMessage());

            return back()->withInput()
                         ->with('error', 'Ocurrió un error al intentar crear el trabajador. Por favor, revisa el log para más detalles.');
        }
    }

    /**
     * Muestra el formulario de edición para un trabajador específico.
     * Recibe la empresa ($company) y el trabajador ($worker) por inyección de dependencias.
     */
    public function edit(Company $company, User $worker)
    {
        // 1. OBTENER LOS ROLES POR NOMBRE: Usamos 'name' en lugar de 'slug'.
        // Asegúrate de que los valores dentro del array ('Trabajador', 'Técnico', 'Gestor')
        // coincidan EXACTAMENTE con los valores que tienes en la columna 'name' de tu tabla.
        $roles = Role::whereIn('name', ['Trabajador', 'Técnico', 'Gestor'])->get(); 

        // 2. OBTENER TODAS LAS EMPRESAS para el dropdown de asignación.
        $companies = Company::all(); 

        // 3. Pasamos todas las variables a la vista.
        return view('tecnico.workers.edit', compact('worker', 'company', 'roles', 'companies')); 
    }

    /**
     * Actualiza los datos del trabajador en la base de datos.
     */
    public function update(Request $request, Company $company, User $worker)
    {
        // 1. Validar que la compañía seleccionada es gestionada por el técnico
        $companyIds = auth()->user()->memberOfCompanies->pluck('id');
        
        $request->validate([
            'name' => 'required|string|max:255',
            // Regla para asegurar que el email es único, excepto para el usuario actual
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($worker->id),
            ],
            // Validar que el rol exista
            'role_id' => 'required|exists:roles,id', 
            'company_id' => [
                'required',
                'exists:companies,id',
                Rule::in($companyIds), 
            ],
        ]);

        // 2. Actualizar los datos básicos del usuario
        $worker->update($request->only('name', 'email', 'role_id'));

        // 3. Sincronizar la pertenencia a la empresa (solo debe estar en la seleccionada)
        // Esto desvincula al trabajador de cualquier otra empresa y lo vincula a 'company_id'
        $worker->memberOfCompanies()->sync([$request->company_id]);

        return redirect()
            ->route('tecnico.workers.index', $company->id)
            ->with('success', 'Trabajador actualizado exitosamente.');
    }

    /**
     * Elimina (soft delete) a un trabajador.
     * Solo requiere el ID del trabajador en la ruta DELETE '/tecnico/workers/{worker}'.
     */
    public function destroy(User $worker): RedirectResponse
    {
        // 1. Verificación de permisos. El técnico debe gestionar al menos una de las empresas 
        // a las que pertenece este trabajador.
        $workerCompanyIds = $worker->memberOfCompanies->pluck('id');
        $technicianCompanyIds = Auth::user()->memberOfCompanies->pluck('id');

        // Si la intersección de IDs es vacía, el técnico no gestiona ninguna de sus empresas.
        if ($workerCompanyIds->intersect($technicianCompanyIds)->isEmpty()) {
            abort(403, 'No tienes permiso para eliminar este trabajador, ya que no gestionas ninguna de sus empresas.');
        }
        
        // 2. Comprobar que el usuario a eliminar sea un 'Trabajador'
        if ($worker->role->name !== 'Trabajador') {
            abort(403, 'Solo se pueden eliminar Trabajadores desde esta sección.');
        }

        try {
            $workerName = $worker->name;
            $worker->delete(); // Aplica Soft Delete si el modelo User lo tiene configurado

            Log::info('Trabajador eliminado (Soft Delete) por Técnico: ' . Auth::id() . '. ID Trabajador: ' . $worker->id);

            return redirect()->route('tecnico.workers.index')
                             ->with('success', 'Trabajador "' . $workerName . '" eliminado con éxito (soft delete).');

        } catch (\Exception $e) {
            Log::error('Error al eliminar trabajador por Técnico: ' . $e->getMessage());

            return back()
                         ->with('error', 'Ocurrió un error al intentar eliminar el trabajador. Por favor, revisa el log.');
        }
    }

    /**
     * Alterna el estado de is_active de un trabajador.
     * Solo requiere el ID del trabajador en la ruta PATCH '/tecnico/workers/{worker}/toggle-status'.
     */
    public function toggleStatus(User $worker): RedirectResponse
    {
        // 1. Verificación de permisos (similar a destroy)
        $workerCompanyIds = $worker->memberOfCompanies->pluck('id');
        $technicianCompanyIds = Auth::user()->memberOfCompanies->pluck('id');

        if ($workerCompanyIds->intersect($technicianCompanyIds)->isEmpty()) {
            abort(403, 'No tienes permiso para modificar el estado de este trabajador.');
        }
        
        // 2. Comprobar que el usuario a modificar sea un 'Trabajador'
        if ($worker->role->name !== 'Trabajador') {
            abort(403, 'Solo se puede modificar el estado de Trabajadores desde esta sección.');
        }

        try {
            $worker->is_active = !$worker->is_active;
            $worker->save();
            
            $status = $worker->is_active ? 'activo' : 'inactivo';

            return back()->with('success', 'El estado del trabajador "' . $worker->name . '" ha sido cambiado a ' . $status . '.');

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de trabajador: ' . $e->getMessage());

            return back()->with('error', 'No se pudo cambiar el estado del trabajador.');
        }
    }
}
