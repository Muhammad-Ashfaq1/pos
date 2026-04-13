<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
public $incrementing = false;
protected $keyType = 'string';

 
    protected $fillable = [
        'id', // usually UUID hota hai
        'shop_name',
        'business_type',
        'owner_name',
        'email',
        'phone',
        'website_url',
        'address',
        'city',
        'state',
        'country',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'onboarding_completed' => 'boolean',
        'approved_at' => 'datetime',
    ];
    
}