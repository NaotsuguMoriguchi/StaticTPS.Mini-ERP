<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempTransferProduct extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temp_transfer_products';

    public function product()
    {
        return $this->belongsTo(\App\Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(\App\Variation::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(\App\Category::class, 'sub_category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(\App\Brands::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id', 'id');
    }

    public function transfer_location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'transfer_location_id', 'id');
    }
}