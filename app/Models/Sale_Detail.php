<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale_Detail extends Model
{
    use HasFactory;
    //vincular con la tabla
    protected $table = 'sales_detail' ;

    //actualiza los campos
    protected $fillable = [ 'quantity'] ;

    //relaciones
    public function product() {
        return $this->belongsTo(Product::class, 'idProduct', 'id');
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'idUser', 'id');
    }
    

}
