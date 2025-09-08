<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Muestra una lista de todas las empresas.
     */
    public function index()
    {
        $companies = Company::with('gestor')->get();
        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Muestra el formulario para crear una nueva empresa.
     */
    public function create()
    {
        // Obtenemos solo los usuarios con el rol de 'Gestor' para el selector
        $gestores = User::whereHas('role', function ($query) {
            $query->where('name', 'Gestor');
        })->get();

        return view('admin.companies.create', compact('gestores'));
    }

    /**
     * Guarda una nueva empresa en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies',
            'gestor_id' => 'required|exists:users,id',
        ]);

        Company::create($request->all());

        return redirect()->route('admin.companies.index')->with('success', 'Empresa creada con éxito.');
    }

    /**
     * Muestra el formulario para editar una empresa existente.
     */
    public function edit(Company $company)
    {
        $gestores = User::whereHas('role', function ($query) {
            $query->where('name', 'Gestor');
        })->get();

        return view('admin.companies.edit', compact('company', 'gestores'));
    }

    /**
     * Actualiza una empresa existente en la base de datos.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
            'gestor_id' => 'required|exists:users,id',
        ]);

        $company->update($request->all());

        return redirect()->route('admin.companies.index')->with('success', 'Empresa actualizada con éxito.');
    }

    /**
     * Elimina una empresa de la base de datos.
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')->with('success', 'Empresa eliminada con éxito.');
    }
}

