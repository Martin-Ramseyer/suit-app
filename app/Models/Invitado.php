<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitado extends Model
{
    use HasFactory;

    protected $table = 'invitados';

    protected $fillable = [
        'nombre_completo',
        'numero_acompanantes',
        'ingreso',
        'usuario_id',
        'evento_id',
    ];

    /**
     * Define la relación "pertenece a" con el Usuario (RRPP).
     */
    public function rrpp()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Define la relación "pertenece a" con el Evento.
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    /**
     * Define la relación "pertenece a muchos" con Beneficios.
     */
    public function beneficios()
    {
        return $this->belongsToMany(Beneficio::class, 'beneficio_invitado')
            ->withPivot('cantidad') // Para poder acceder a la columna 'cantidad'
            ->withTimestamps();
    }
}
