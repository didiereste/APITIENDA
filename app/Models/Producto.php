<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable=[
        'nombre',
        'descripcion',
        'precio',
        'cantidad_disponible',
        'categoria_id',
        'marca_id',
    ];

    protected $table='productos';

    

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withPivot('precio','cantidad','subtotal')->withTimestamps();
    }

}
