<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gestor_id']; // Añade gestor_id aquí

    /**
     * El Gestor (dueño) de esta empresa.
     */
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Los usuarios (Técnicos y Trabajadores) que pertenecen a esta empresa.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}