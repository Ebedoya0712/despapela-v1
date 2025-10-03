<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Company;
use App\Models\Document;
use App\Models\UniqueLink;

class DashboardController extends Controller
{
    public function index()
{
    $user = Auth::user();
    $viewData = []; 

    if (!$user->role) {
        return view('dashboard');
    }

    switch ($user->role->name) {
        case 'Administrador':
            $viewData = [
                'totalUsers' => \App\Models\User::count(),
                'totalCompanies' => \App\Models\Company::count(),
                'totalDocuments' => \App\Models\Document::count(),
            ];
            break;
        
        case 'Gestor':
            $companyIds = $user->managedCompanies()->pluck('id');
            $viewData = [
                'techniciansCount' => \App\Models\User::where('role_id', 3)->whereHas('memberOfCompanies', fn($q) => $q->whereIn('company_id', $companyIds))->count(),
                'workersCount' => \App\Models\User::where('role_id', 4)->whereHas('memberOfCompanies', fn($q) => $q->whereIn('company_id', $companyIds))->count(),
                'documentsCount' => \App\Models\Document::whereIn('company_id', $companyIds)->count(),
            ];
            break;

        case 'Técnico':
            $companyIds = $user->memberOfCompanies()->pluck('companies.id');
            $viewData = [
                'pendingAssignment' => \App\Models\Document::whereIn('company_id', $companyIds)->where('status', 'pending')->doesntHave('links')->count(),
                'pendingSignatures' => \App\Models\UniqueLink::whereIn('company_id', $companyIds)->whereHas('document', fn($q) => $q->where('status', 'pending'))->count(),
                'recentlySigned' => \App\Models\Document::whereIn('company_id', $companyIds)->where('status', 'signed')->latest()->take(5)->get(),
            ];
            break;

        case 'Trabajador':
            // CORRECCIÓN: Usamos $viewData en lugar de $data
            $viewData = [
                'pendingToSign' => \App\Models\UniqueLink::where('user_id', $user->id)->whereHas('document', fn($q) => $q->where('status', 'pending'))->count(),
                'recentlySigned' => $user->signatures()->whereHas('document')->with('document')->latest()->take(5)->get(),
            ];
            break;
    }
    
    return view('dashboard', $viewData);
    }
}