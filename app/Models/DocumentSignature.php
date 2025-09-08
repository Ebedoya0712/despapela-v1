<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'signer_id',
        'filled_data',
        'signature_image_path',
        'signed_at',
    ];
    
    protected $casts = [
        'filled_data' => 'array', // Convierte el JSON a array automáticamente
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}