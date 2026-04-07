<?php

use App\Models\Memo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends Component
{
    /** @var int メモID */
    #[Locked]
    public ?int $memo_id = null;

    /** @var string タグのサイズ */
    #[Locked]
    public string $tag_size = '';

    /** @var string セレクトボックスのサイズ */
    #[Locked]
    public string $select_size = '';

    /**
     * タグ。
     *
     * @return Collection タグ
     */
    #[Computed]
    public function tags(): Collection
    {
        $attached_ids = $this->attachedTags()->pluck('id')->all();
        return Auth::user()->tags()->whereNotIn('id', $attached_ids)->orderBy('priority')->get();
    }

    /**
     * 付加されているタグ。
     *
     * @return Collection 付加されているタグ
     */
    #[Computed]
    public function attachedTags(): Collection
    {
        if (!isset($this->memo_id)) {
            return new Collection([]);
        }
        $memo = Memo::find($this->memo_id);
        if (!isset($memo)) {
            return new Collection([]);
        }

        return $memo->tags()->orderBy('priority')->get();
    }

    /**
     * mount.
     */
    public function mount(?int $memo_id, ?string $tag_size = null, ?string $select_size = null)
    {
        $this->memo_id = $memo_id;
        $this->tag_size = $tag_size ?? '';
        $this->select_size = $select_size ?? '';
    }

    /**
     * タグ付与。
     *
     * @param int $id タグID
     */
    public function attachTag(int $tag_id): void
    {
        DB::transaction(function () use ($tag_id) {
            $memo = Memo::find($this->memo_id);

            // メモがない場合や、既に付与されている場合は何もしない
            if (!isset($memo) || $memo->tags()->where('tag_id', $tag_id)->exists()) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $memo->tags()->attach($tag_id);
        });
    }

    /**
     * タグ除去。
     *
     * @param int $id タグID
     */
    public function detachTag(int $tag_id): void
    {
        DB::transaction(function () use ($tag_id) {
            $memo = Memo::find($this->memo_id);

            // メモがない場合や、付与されていない場合は何もしない
            if (!isset($memo) || !$memo->tags()->where('tag_id', $tag_id)->exists()) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $memo->tags()->detach($tag_id);
        });
    }
};
?>

<div {{ $attributes }}>
@foreach ($this->attached_tags as $rec)
    <flux:badge class="me-1" size="{{ $tag_size }}" color="blue">{{ $rec->name }}<flux:badge.close wire:click="detachTag({{ $rec->id }})" /></flux:badge>
@endforeach
@if($this->tags->isNotEmpty())
    <flux:dropdown>
        <flux:button icon="ellipsis-horizontal" size="{{ $select_size }}" class="align-middle" variant="primary" color="blue"></flux:button>
        <flux:menu class="bg-blue-300! dark:bg-blue-500!">
@foreach ($this->tags as $rec)
            <flux:menu.item wire:click="attachTag({{ $rec->id }})">{{ $rec->name }}</flux:menu.item>
@endforeach
        </flux:menu>
    </flux:dropdown>
@endif
</div>
