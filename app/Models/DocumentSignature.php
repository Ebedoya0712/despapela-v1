<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable; // 1. Importamos el Trait

class DocumentSignature extends Model
{
    use HasFactory, Multitenantable; // 2. Usamos el Trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'company_id', // 3. Añadimos al fillable para asignación masiva
        'signer_id',
        'filled_data',
        'signature_image_path',
        'signed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filled_data' => 'array', // Convierte el JSON a array automáticamente
    ];

    /**
     * Obtiene el documento al que pertenece la firma.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Obtiene el usuario que firmó el documento.
     */
    public function signer()
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
