<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'product_name',
        'description',
        'product_price',
        'image',
        'stock',
        'rating',
        'expired_at',
        'modified_by'
    ];
}
