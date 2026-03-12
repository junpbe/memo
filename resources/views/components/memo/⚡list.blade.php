<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

new class extends Component
{
    /** @var array 追加一覧 */
    #[Locked]
    public array $add_list = [];

    /** @var \Illuminate\Database\Eloquent\Collection 一覧（新規追加データがリアルタイムで出てきてしまうのでComputed Propertiesは使わず、ユーザが更新ボタンを押したときだけ更新） */
    public Collection $list;

    /**
     * mount.
     */
    public function mount()
    {
        $this->refreshList();
    }

    /**
     * 新規追加。
     */
    public function create(): void
    {
        $this->add_list[] = null;
    }

    /**
     * 削除。
     *
     * @param int $new_key 追加一覧のキー
     */
    #[On('removed-memo')]
    public function remove(?int $new_key = null): void
    {
        // 追加一覧にないものは何もしない（再レンダリングで消える）
        if (!isset($new_key)) {
            return;
        }

        // 追加一覧から削除
        unset($this->add_list[$new_key]);
    }

    /**
     * リセットリフレッシュ。
     */
    public function refreshWtihReset(): void
    {
        $this->reset();
        $this->refreshList();
    }

    /**
     * 一覧最新化。
     */
    public function refreshList(): void
    {
        $this->list = Auth::user()->memos()->orderByDesc('id')->get();
    }
};
?>

<div>
	<div class="mb-3">
		<flux:button square wire:click="create"><flux:icon.plus /></flux:button>
		<flux:button square wire:click="refreshWtihReset"><flux:icon.arrow-path /></flux:button>
	</div>
	<div class="flex flex-wrap gap-4">
@foreach (array_reverse($add_list, true) as $key => $val)
		<livewire:memo.rec :new_key="$key" wire:key="new_{{ $key }}" class="p-1 bg-sky-100/50 dark:bg-sky-900/50" />
@endforeach
@foreach ($list as $rec)
		<livewire:memo.rec :$rec wire:key="{{ $rec->id }}_{{ $rec->updated_at->format('YmdHisu') }}" class="p-1" />
@endforeach
	</div>
</div>
