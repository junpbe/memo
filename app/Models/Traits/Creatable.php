<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * created_by用trait。
 */
trait Creatable
{
    /**
     * 作成者カラム自動更新。
     */
    public static function bootCreatable(): void
    {
        static::creating(function (Model $model) {
            $model->created_by = Auth::id() ?? 0;
        });
    }

    /**
     * 作成者リレーション。
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
