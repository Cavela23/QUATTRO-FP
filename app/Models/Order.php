<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order_Item;

class Order extends Model
{
    use HasFactory;

    protected $table = 'order';

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id',
        'order_date',
        'order_status',
        'total_amount'
    ];

    public function orderItem()
    {
        return $this->hasMany(Order_Item::class, 'order_id', 'order_id');
    }
}
