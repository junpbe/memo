<?php

use App\Exceptions\ModelNotLatestException;
use App\Livewire\Forms\MemoForm;
use App\Models\Memo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Locked;

new class extends Component
{
    /** @var \App\Livewire\Forms\MemoForm フォーム */
    public MemoForm $form;

    /** @var int 新規追加リストのキー */
    #[Locked]
    public ?int $new_key = null;

    /** @var bool 削除アクションを実行した瞬間だけtrueにして親リストが更新されるまで非表示にする */
    #[Locked]
    public bool $removed = false;

    /**
     * mount.
     *
     * @param \App\Models\Memo $rec 既存モデル
     * @param int $new_key 新規追加のリストのキー
     */
    public function mount(?Memo $rec = null, ?int $new_key = null)
    {
        // モデルのIDがある場合は、既存データ
        if (isset($rec->id)) {
            $this->form->setModel($rec);
            return;
        }

        // 新規追加のリストのキーがある場合は新規追加
        if (isset($new_key)) {
            $this->new_key = $new_key;
            return;
        }

        throw new InvalidArgumentException('モデルも新規追加のリストのキーも指定されていません。');
    }

    /**
     * 保存。
     */
    public function save(): void
    {
        DB::transaction(function () {
            $this->form->save();
        });
        $this->dispatch('saved-memo', $this->form->id);
    }

    /**
     * 削除。
     */
    public function remove(): void
    {
        // データがDBに存在するなら削除
        if ($this->form->modelExists()) {
            DB::transaction(function () {
                $rec = Memo::lockForUpdate()->find($this->form->id);
                if (!isset($rec)) {
                    return;
                }

                // 権限チェック
                Gate::authorize('delete', $rec);

                $rec->delete();
            });
        }

        // 子コンポーネント再レンダリング時にすぐに見えなくさせる（イベントを親がキャッチしてからだと一瞬ラグを感じるため）
        $this->removed = true;

        // イベント発行（新規データの場合新規追加リストのキーを渡す。そうでない場合はnullを渡すことになるが特に意味は無く、親再レンダリングで消える）
        $this->dispatch('removed-memo', $this->new_key);
    }

    /**
     * リロード。
     */
    public function reload(): void
    {
        // 新規作成中データの場合は何もしない
        if (!$this->form->modelExists()) {
            return;
        }

        $model = Memo::find($this->form->id);
        if (!isset($model)) {
            // DBに存在しない場合は削除処理を呼び出し再レンダリングで消す
            $this->remove();
            return;
        }

        // データ更新
        $this->form->setModel($model);
    }

    /**
     * exception.
     *
     * @param mixed $e
     * @param mixed $stopPropagation
     */
    public function exception($e, $stopPropagation)
    {
        if ($e instanceof ModelNotLatestException) {
            $this->dispatch("model-not-latest-error");
            $stopPropagation();
        }
    }
};
?>

<div {{ $attributes->class(['invisible' => $removed]) }} wire:transition>
    <x-action-message class="me-3" on="model-not-latest-error">他の人によって更新されました。</x-action-message>
@isset($form->id)
    <livewire:memo.tags class="mb-1 w-64" :memo_id="$form->id" tag_size="sm" select_size="xs" />
@endisset
    @error('form.body') <span class="error">{{ $message }}</span> @enderror
    <flux:memo-textarea class="field-sizing-content w-64" resize="both" wire:model="form.body" wire:input.debounce.500ms="save"></flux:memo-textarea>
    <flux:button square wire:click="reload"><flux:icon.arrow-path /></flux:button>
    <flux:button square wire:click="remove"><flux:icon.trash /></flux:button>
    <x-action-message class="inline" on="saved-memo">保存しました</x-action-message>
</div>
