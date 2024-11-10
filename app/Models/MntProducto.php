<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MntProducto extends Model
{
    use HasFactory;

    protected $table = 'mnt_producto';

    protected $guarded = [
        'id'
    ];

    protected $fillable = [
        'id_categoria_producto',
        'id_establecimiento_producto',
        'nombre'
    ];

    public function establecimientoOrigen()
    {
        return $this->belongsTo(CtlEstablecimientoOrigen::class, 'id_establecimiento_origen');
    }

    public function categoriaProducto()
    {
        return $this->belongsTo(CtlCategoriaProducto::class, 'id_categoria_producto');
    }

}
