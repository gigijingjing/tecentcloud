<?php
namespace App\Goods;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = "goods";

    protected $fillable = [
        'name', 'logo', 'url', 'status', 'content',
        'stock', 'loan_ratio', 'day_profit_ratio', 'contact_name', 'contact_phone',
        'click_num', 'register_num', 'mode', 'cpa', 'cps',
        'loan_price', 'loan_money', 'loan_num', 'banner_url', 'tags',
        'popularity', 'loan_speed', 'loan_range', 'star', 'type',
        'conversion', 'income', 'register_price', 'order'
    ];
}
