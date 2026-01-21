<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * updated_by用trait。
 */
trait Updatable
{
    /**
     * 更新者カラム自動更新。
     */
    public static function bootUpdatable(): void
    {
        static::creating(function (Model $model) {
            $model->updated_by = Auth::id() ?? 0;
        });

        static::updating(function (Model $model) {
            $model->updated_by = Auth::id() ?? 0;
        });
    }

    /**
     * 更新者リレーション。
     *
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
