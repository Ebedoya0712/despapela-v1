<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Company; // Importamos el modelo Company
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Muestra la lista de empresas a las que el Técnico está asignado.
     */
    public function index()
    {
        $tecnico = Auth::user();
        $companies = $tecnico->memberOfCompanies()->get();
        return view('tecnico.companies.index', compact('companies'));
    }

    /**
     * Muestra los detalles de una empresa específica, incluyendo sus trabajadores.
     */
    public function show(Company $company)
    {
        // Obtenemos los usuarios que son miembros de esta empresa Y tienen el rol de 'Trabajador'
        $workers = $company->staff()->whereHas('role', function ($query) {
            $query->where('name', 'Trabajador');
        })->get();

        return view('tecnico.companies.show', compact('company', 'workers'));
    }
}

