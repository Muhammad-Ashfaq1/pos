<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCart extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
