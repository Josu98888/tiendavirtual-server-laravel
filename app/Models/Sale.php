<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    //vincular con la tabla
    protected $table = 'sales' ;
    protected $fillable = ['idProduct', 'idUser', 'quantity'];

    //relaciones 

    public function user() {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }

    public function product() {
        return $this->belongsTo(Product::class, 'idProduct', 'id');
    }
}
