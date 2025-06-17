<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Beneficio extends Model
{
    use HasFactory;

    protected $table = 'beneficios';

    protected $fillable = [
        'nombre_beneficio',
    ];
}
