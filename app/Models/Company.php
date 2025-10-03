<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'gestor_id'];

    /**
     * Devuelve el usuario que es el GESTOR (dueño) de esta empresa.
     */
    public function gestor()
    {
        return $this->belongsTo(User::class, 'gestor_id');
    }

    /**
     * Devuelve todos los TÉCNICOS y TRABAJADORES que son miembros de esta empresa.
     */
    public function staff()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Devuelve todos los documentos que pertenecen a esta empresa.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
