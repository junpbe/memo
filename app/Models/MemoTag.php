<?php

namespace App\Models;

use App\Models\Traits\Creatable;
use App\Models\Traits\Updatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * メモタグ中間モデル。
 */
class MemoTag extends Pivot
{
    use Creatable, Updatable;

    /**
     * リレーション：メモ。
     *
     * @return BelongsTo
     */
    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }

    /**
     * リレーション：タグ。
     *
     * @return BelongsTo
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}