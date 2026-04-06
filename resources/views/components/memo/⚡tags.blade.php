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
    public function mount(int $memo_id)
    {
        $this->memo_id = $memo_id;
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

        $this->js('resetTagSelect');
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
    <flux:badge class="me-1">{{ $rec->name }}<flux:badge.close wire:click="detachTag({{ $rec->id }})" /></flux:badge>
@endforeach
@if($this->tags->isNotEmpty())
    <flux:select id="tag-select" class="w-auto inline-block" size="sm" placeholder="タグを追加" wire:change="attachTag($event.target.value)">
@foreach ($this->tags as $rec)
        <flux:select.option :value="$rec->id">{{ $rec->name }}</flux:select.option>
@endforeach
    </flux:select>
@endif
@script
    <script>
        this.$js.resetTagSelect = () => {
            const select = document.getElementById('tag-select');
            select.selectedIndex = 0;
        };
    </script>
@endscript
</div>
