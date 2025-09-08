<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable; // 1. Importamos el Trait

class DocumentField extends Model
{
    use HasFactory, Multitenantable; // 2. Usamos el Trait

    protected $fillable = [
        'document_id',
        'company_id', // 3. Añadimos al fillable
        'type',
        'name',
        'coordinates',
    ];
    
    protected $casts = [
        'coordinates' => 'array',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}

