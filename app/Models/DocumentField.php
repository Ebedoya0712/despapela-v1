<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentField extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'type',
        'name',
        'coordinates',
    ];
    
    protected $casts = [
        'coordinates' => 'array', // Convierte el JSON a array automáticamente
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}