<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_active',
        'dni',
        'phone',
        'bank_account',
        'address',
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

    /**
     * El rol al que pertenece este usuario.
     */
    public function role() {
        return $this->belongsTo(Role::class);
    }

    // --- RELACIONES DE EMPRESA ---

    /**
     * Relación para un GESTOR.
     * Devuelve las empresas que este usuario posee directamente.
     */
    public function managedCompanies(): HasMany {
        return $this->hasMany(Company::class, 'gestor_id');
    }

    /**
     * Relación para TÉCNICOS y TRABAJADORES.
     * Devuelve las empresas de las que son miembros a través de la tabla pivote.
     */
    public function memberOfCompanies(): BelongsToMany {
        return $this->belongsToMany(Company::class);
    }

    /**
     * **[NUEVO MÉTODO UNIFICADOR]**
     * Devuelve la colección correcta de empresas para ser usada en el controlador.
     * Esto resuelve el error 'Call to undefined method App\Models\User::companies()'.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function companies() 
    {
        if ($this->role->name === 'Gestor') {
            // Si es Gestor, usa la relación HasMany (empresas que gestiona).
            return $this->managedCompanies();
        }
        
        // Si es Técnico o Trabajador, usa la relación BelongsToMany (empresas de las que es miembro).
        return $this->memberOfCompanies();
    }

    // --- OTRAS RELACIONES ---

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
}
