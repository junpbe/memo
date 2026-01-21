<?php

namespace App\Models;

use App\Models\Traits\Creatable;
use App\Models\Traits\Updatable;

/**
 * メモモデル。
 */
class Memo
{
    use Creatable, Updatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'body',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        //
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }
}
