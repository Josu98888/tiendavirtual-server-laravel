<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    //vincular con la tabla
    protected $table = 'products' ;

    //actualiza los campos
    protected $fillable = ['id', 'name', 'description','image','priceNow','priceBefore','numSales','stock'] ;

    //relaciones

    public function categorie() {
        //retorna de uno a uno
        return $this->belongsTo('App\Models\Categorie') ;
    }

    public function sale_details() {
        //retorna uno a muchos
        return $this->hasMany('App\Models\Sale_Detail') ;
    }
    public function sale() {
        //retorna uno a muchos
        return $this->hasMany('App\Models\Sale') ;
    }
}
