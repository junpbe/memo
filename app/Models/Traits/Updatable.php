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
            // Model::withoutTimestamps()などで一時的にタイムスタンプ更新を無効にしている場合、自動更新しない
            if (!$model->usesTimestamps()) {
                return;
            }
            $model->updated_by = Auth::id() ?? 0;
        });

        static::updating(function (Model $model) {
            // Model::withoutTimestamps()などで一時的にタイムスタンプ更新を無効にしている場合、自動更新しない
            if (!$model->usesTimestamps()) {
                return;
            }
            $model->updated_by = Auth::id() ?? 0;
        });
    }

    /**
     * リレーション：更新者。
     *
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withDefault([
            'id' => 0,
            'name' => '削除済みユーザ',
            'email' => 'deleted@example.com',
        ]);
    }
}
