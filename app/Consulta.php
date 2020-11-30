<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table = 'consultas';
    public $timestamps = true;

    protected $fillable = [
        'sector', 'nombre', 'consulta'
    ];

}
