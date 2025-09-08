<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable; // <-- 1. Importamos el Trait

class Document extends Model
{
    use HasFactory, Multitenantable; // <-- 2. Usamos el Trait

    protected $fillable = [
        'company_id',
        'uploader_id',
        'original_filename',
        'storage_path',
        'signed_storage_path',
        'status',
        'expires_at',
    ];

    

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

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

