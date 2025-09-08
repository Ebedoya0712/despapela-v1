<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELACIONES ---

    /**
     * El rol al que pertenece este usuario.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Los documentos que este usuario ha subido (si es Técnico).
     */
    public function documentsUploaded()
    {
        return $this->hasMany(Document::class, 'uploader_id');
    }

    /**
     * Las firmas que este usuario ha realizado.
     */
    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class, 'signer_id');
    }

    /**
     * Las empresas que este usuario gestiona (si es Gestor).
     */
    public function managedCompanies()
    {
        return $this->hasMany(Company::class, 'gestor_id');
    }

    /**
     * Las empresas a las que este usuario pertenece (como Técnico o Trabajador).
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}

