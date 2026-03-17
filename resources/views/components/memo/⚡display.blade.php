<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends Component
{
    use WithPagination, WithoutUrlPagination;

    /** @var int ページ毎の件数 */
    #[Locked]
    public int $per_page;

    /**
     * 一覧。
     *
     * @return mixed 一覧
     */
    #[Computed]
    public function list()
    {
        return Auth::user()->memos()->orderByDesc('id')->simplePaginate($this->per_page);
    }

    /**
     * mount.
     */
    public function mount(int $per_page = 1)
    {
        $this->per_page = $per_page;
    }
};
?>

<div {{ $attributes }}>
    <div class="flex items-center border-b border-zinc-100 dark:border-zinc-700 mb-3">
        <flux:heading size="xl">メモ</flux:heading>
        <flux:pagination :paginator="$this->list" class="grow border-t-0 pb-3" />
    </div>
    <div class="flex flex-wrap gap-4">
@foreach ($this->list as $rec)
        <flux:card size="sm" class="w-64 hover:bg-zinc-100 dark:hover:bg-zinc-600">
            <flux:text class="whitespace-pre-wrap wrap-break-word">{{ $rec->body }}</flux:text>
        </flux:card>
@endforeach
    </div>
</div>
