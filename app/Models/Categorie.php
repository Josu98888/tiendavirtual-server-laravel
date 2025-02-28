<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;
    //vincular con la tabla
    protected $table = 'categories' ;

    //actualiza los campos
    protected $fillable = ['name'] ;

    //relacion de uno a muchos 
    public function products() {
        //retorna de uno a muchos
        return $this->hasMany('App\Models\Product') ;
    }
}
