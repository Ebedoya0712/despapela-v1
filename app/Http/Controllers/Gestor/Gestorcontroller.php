<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Gestorcontroller extends Controller
{
    /**
     * Muestra la lista de empresas que el Gestor autenticado administra.
     */
    public function index()
    {
        // Obtenemos el usuario autenticado (que será un Gestor)
        $gestor = Auth::user();

        // Usamos la relación 'managedCompanies' para obtener solo sus empresas
        $companies = $gestor->managedCompanies()->get();

        return view('gestor.companies.index', compact('companies'));
    }
}
