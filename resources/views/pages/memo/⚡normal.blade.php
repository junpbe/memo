<?php

use App\Exceptions\ModelNotLatestException;
use App\Livewire\Forms\MemoForm;
use App\Models\Memo;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

new class extends Component
{
    /** @var \App\Livewire\Forms\MemoForm フォーム */
    public MemoForm $form;

    /** @var \App\Models\Memo メモ */
    #[Locked]
    public ?Memo $memo = null;

    /**
     * 一覧。
     *
     * @return Collection 一覧
     */
    #[Computed]
    public function list(): Collection
    {
        return Auth::user()->memos()->with(['tags' => fn($q) => $q->orderBy('priority')])->orderByDesc('id')->get();
    }

    /**
     * 新規追加。
     */
    public function create(): void
    {
        // 念のためフォームをリセットする
        $this->form->reset();
        $this->form->resetErrorBag();
        $this->memo = null;

        Flux::modal('edit')->show();
    }

    /**
     * 編集。
     *
     * @param int $id モデルのid
     */
    public function edit(int $id): void
    {
        $rec = Memo::find($id);
        if (!isset($rec)) {
            // 存在しない場合エラー
            throw new ModelNotLatestException();
        }
        $this->form->setModel($rec);
        $this->memo = $rec;

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
        $this->memo = null;
    }

    /**
     * 保存。
     */
    public function save(): void
    {
        DB::transaction(function () {
            $this->memo = $this->form->save();
        });
        $this->dispatch('tags-lazy-update');
        $this->dispatch('saved-memo', $this->form->id);

        // タグを更新してからレンダリングしないと一覧のタグが古いままになるので、いったんここではレンダリングスキップ
        $this->skipRender();
    }

    /**
     * レンダリング目的のダミー。
     */
    #[On('tags-lazy-updated')]
    public function dummy(){}

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
                $this->memo = null;
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
        <div class="w-64" wire:key="{{ $rec->id }}_{{ $rec->updated_at->format('YmdHisu') }}_{{ $rec->tags->pluck('id')->join('-') }}">
            <flux:card size="sm" class="hover:bg-zinc-100 dark:hover:bg-zinc-600" wire:click="edit({{ $rec->id }})">
                <flux:text class="whitespace-pre-wrap wrap-break-word">{{ $rec->body }}</flux:text>
            </flux:card>
            <livewire:memo.tags class="mb-1 w-64" :memo="$rec" tag_size="sm" readonly />
        </div>
@endforeach
    </div>
    <flux:modal name="edit" class="w-full lg:max-w-5/10 max-w-9/10 dark:backdrop:bg-black/80!" wire:close="closeEdit" :dismissible="false">
        <x-action-message class="me-3" on="model-not-latest-error">他の人によって更新されました。</x-action-message>
        <x-action-message class="inline" on="saved-memo">保存しました</x-action-message>
        @error('form.body') <span class="error">{{ $message }}</span> @enderror
@isset($memo)
        <livewire:memo.tags-lazy-update :$memo select_size="xs" />
@endisset
        <div class="mt-5">
            <textarea name="body" class="w-full resize outline-none" rows="10" wire:model.live.debounce.500ms="form.body"></textarea>
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
