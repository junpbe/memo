<?php

namespace App\Models\Traits;

use App\Exceptions\ModelNotLatestException;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * ロック用trait。
 */
trait Lockable
{
    /**
     * ロック。
     *
     * @return static 最新のモデル。レコードが消えていた場合はnull。
     */
    public function lock(): ?static
    {
        // トランザクションを切ってない場合はエラー
        if (DB::transactionLevel() === 0) {
            throw new LogicException('トランザクションを開始してない状態で呼び出されました。');
        }

        // newしただけでsaveをまだしていない場合などはエラー
        if (!$this->exists) {
            throw new LogicException('実態が存在しない状態で呼び出されました。');
        }

        return $this->setKeysForSelectQuery($this->newQueryWithoutScopes())
            ->useWritePdo()
            ->lockForUpdate()
            ->first();
    }

    /**
     * 最新ロック。
     *
     * ロックしようとしているモデルが最新ではなかった場合例外をスローする。
     * つまり成功した場合そのモデルは最新。
     */
    public function lockLatest(): void
    {
        $latest = $this->lock();

        // DBに存在しなかった場合エラー
        if (!isset($latest)) {
            throw new ModelNotLatestException();
        }
//         dump($latest->updated_at);
//         dump($this->updated_at);
        // 先に誰かに更新されていた場合エラー
        if ($latest->updated_at->notEqualTo($this->updated_at)) {//TODO　livewireでは毎回モデルのidから実態を引っ張っているから一致してしまう
            throw new ModelNotLatestException();
        }
    }
}
