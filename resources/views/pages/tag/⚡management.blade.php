<?php

use App\Exceptions\ModelNotLatestException;
use App\Livewire\Forms\TagForm;
use App\Models\Memo;
use App\Models\Tag;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    /** @var \App\Livewire\Forms\TagForm フォーム */
    public TagForm $form;

    /**
     * 一覧。
     *
     * @return Collection 一覧
     */
    #[Computed]
    public function list(): Collection
    {
        return Auth::user()->tags()->orderBy('priority')->get();
    }

    /**
     * 新規追加。
     */
    public function create(): void
    {
        // 念のためフォームをリセットする
        $this->form->reset();
        $this->form->resetErrorBag();

        Flux::modal('edit')->show();
    }

    /**
     * 編集。
     *
     * @param int $id モデルのid
     */
    public function edit(int $id): void
    {
        $rec = Tag::find($id);
        if (!isset($rec)) {
            // 存在しない場合エラー
            throw new ModelNotLatestException();
        }
        $this->form->setModel($rec);

        Flux::modal('edit')->show();
    }

    /**
     * 編集クローズ。
     */
    public function closeEdit(): void
    {
        // フォームをリセットする
        $this->form->reset();
        $this->form->resetErrorBag();
    }

    /**
     * 保存。
     */
    public function save(): void
    {
        DB::transaction(function () {
            $this->form->save();
        });
        $this->dispatch('saved-tag', $this->form->id);
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

        // モーダルを同時に消すためにあえてPHPのロジックの中でクローズさせる
        Flux::modal('delete')->close();
        Flux::modal('edit')->close();
        $this->closeEdit();
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

<div>
    <div class="mb-3">
        <flux:button square wire:click="create"><flux:icon.plus /></flux:button>
        <flux:button square wire:click="$refresh"><flux:icon.arrow-path /></flux:button>
    </div>
    <div class="flex flex-wrap gap-4">
@foreach ($this->list as $rec)
        <div class="w-64">
            <flux:card size="sm" class="hover:bg-zinc-100 dark:hover:bg-zinc-600" wire:click="edit({{ $rec->id }})">
                <flux:text class="whitespace-pre-wrap wrap-break-word">{{ $rec->name }}</flux:text>
            </flux:card>
        </div>
@endforeach
    </div>
    <flux:modal name="edit" class="w-full lg:max-w-5/10 max-w-9/10 dark:backdrop:bg-black/80!" wire:close="closeEdit" :dismissible="false">
        <x-action-message class="me-3" on="model-not-latest-error">他の人によって更新されました。</x-action-message>
        <x-action-message class="inline" on="saved-tag">保存しました</x-action-message>
        @error('form.body') <span class="error">{{ $message }}</span> @enderror
        <div class="mt-5">
            <textarea name="body" class="w-full resize outline-none" rows="10" wire:model.live.debounce.500ms="form.name"></textarea>
        </div>
        <div class="flex justify-between">
            <flux:modal.close>
                <flux:button variant="ghost" wire:close="closeEdit">閉じる</flux:button>
            </flux:modal.close>
@if($form->modelExists())
            <flux:modal.trigger name="delete">
                <flux:button variant="danger">削除</flux:button>
            </flux:modal.trigger>
@endif
            <flux:button variant="primary" wire:click="save">保存</flux:button>
        </div>
    </flux:modal>
    <flux:modal name="delete" class="min-w-[22rem] dark:backdrop:bg-black/80!">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">削除しますか？</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">キャンセル</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="remove">削除</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
