<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Document extends Model
{
    use HasFactory, Multitenantable;

    protected $fillable = [
        'company_id',
        'uploader_id',
        'original_filename',
        'storage_path',
        'signed_storage_path',
        'status',
        'expires_at',
        'etiquette', // <-- ¡NUEVO CAMPO AGREGADO!
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'etiquette' => 'array', // <-- ¡CAST A ARRAY/JSON!
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    // Esta relación ya no es necesaria para la 'etiquette' única, pero la dejamos por si se usa para tags múltiples.
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function fields()
    {
        return $this->hasMany(DocumentField::class);
    }

    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }
    
    public function links()
    {
        return $this->hasMany(UniqueLink::class);
    }
}
