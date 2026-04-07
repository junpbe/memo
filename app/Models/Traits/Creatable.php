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
            // Model::withoutTimestamps()などで一時的にタイムスタンプ更新を無効にしている場合、自動更新しない
            if (!$model->usesTimestamps()) {
                return;
            }
            $model->created_by = Auth::id() ?? 0;
        });
    }

    /**
     * リレーション：作成者。
     *
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault([
            'id' => 0,
            'name' => '削除済みユーザ',
            'email' => 'deleted@example.com',
        ]);
    }
}
