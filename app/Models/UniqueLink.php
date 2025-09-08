<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable; // 1. Importamos el Trait

class UniqueLink extends Model
{
    use HasFactory, Multitenantable; // 2. Usamos el Trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'company_id', // 3. Añadimos al fillable
        'user_id',
        'token',
        'expires_at',
    ];

    /**
     * Obtiene el documento al que pertenece el enlace.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Obtiene el usuario al que está destinado el enlace.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

