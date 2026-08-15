<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'uuid', 'name', 'date', 'is_optional', 'region',
])]
class Holiday extends Model
{
    use HasUuid;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_optional' => 'boolean',
        ];
    }
}
