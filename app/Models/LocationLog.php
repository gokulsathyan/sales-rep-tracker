<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationLog extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy' => 'decimal:2',
        ];
    }
    public function userDetail()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
