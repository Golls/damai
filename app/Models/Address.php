<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'city', 'zip', 'area', 'road', 'lane', 'alley', 'no', 'floor', 'address', 'filename', 'latitude', 'lontitue', 'full_address',
    ];
}
