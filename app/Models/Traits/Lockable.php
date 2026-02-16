<?php

namespace App\Models\Traits;

use App\Exceptions\ModelNotLatestException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * ロック用trait。
 */
trait Lockable
{
    /**
     * 最新ロック。
     *
     * 更新日時を渡し、それよりも最新のレコードだった場合例外をスローする。
     * つまり成功した場合そのモデルは最新。
     *
     * @param int $id
     * @param \Carbon\CarbonImmutable $updated_at
     * @return static
     */
    public static function lockLatest(int $id, CarbonImmutable $updated_at): static
    {
        // トランザクションを切ってない場合はエラー
        if (DB::transactionLevel() === 0) {
            throw new LogicException('トランザクションを開始してない状態で呼び出されました。');
        }

        $rec = static::lockForUpdate()->find($id);

        // DBに存在しなかった場合エラー
        if (!isset($rec)) {
            throw new ModelNotLatestException();
        }

        // 先に誰かに更新されていた場合エラー
        if ($rec->updated_at->notEqualTo($updated_at)) {
            throw new ModelNotLatestException();
        }

        return $rec;
    }
}
