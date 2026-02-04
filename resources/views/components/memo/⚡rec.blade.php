<?php

use App\Exceptions\ModelNotLatestException;
use App\Livewire\Forms\MemoForm;
use App\Models\Memo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Reactive;

new class extends Component
{
    /** @var \App\Livewire\Forms\MemoForm フォーム */
    #[Reactive]
    public MemoForm $form;

    /**
     * mount.
     *
     * @param Memo $rec
     */
    public function mount(?Memo $rec = null)
    {
        // 新規の場合はモデルのIDがない
        if (!isset($rec->id)) {
            return;
        }

        $this->form->setModel($rec);
    }

    /**
     * 保存。
     */
    public function save()
    {
        DB::transaction(function () {
            $this->form->save();
        });
    }
    //TODO　削除ロジックをどこに入れるか

    /**
     * exception.
     *
     * @param mixed $e
     * @param mixed $stopPropagation
     */
    public function exception($e, $stopPropagation) {
        if ($e instanceof ModelNotLatestException) {
            $this->dispatch("model-not-latest-error");
            $stopPropagation();
        }
    }
};
?>

<div>
	<x-action-message class="me-3" on="model-not-latest-error">他の人によって更新されました。</x-action-message>
	@error('form.body') <span class="error">{{ $message }}</span> @enderror
	<flux:memo-textarea resize="both" wire:model="form.body" wire:change="save"></flux:memo-textarea>
	{{ $form->model?->body }}
	{{ $form->model?->updated_at }}
	<button type="button" wire:click="$refresh">test</button>
</div>
