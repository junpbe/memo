<?php

use App\Models\Memo;
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
    /** @var int メモID */
    #[Locked]
    public int $memo_id;

    /** @var string タグのサイズ */
    #[Locked]
    public string $tag_size = '';

    /** @var string セレクトボックスのサイズ */
    #[Locked]
    public string $select_size = '';

    /** @var \Illuminate\Database\Eloquent\Collection 付与されているタグ */
    #[Locked]
    public Collection $attached_tags;

    /**
     * ユーザに属するすべてのタグ。
     *
     * @return Collection ユーザに属するすべてのタグ
     */
    #[Computed]
    public function allTags(): Collection
    {
        // タグのコンポーネントの数だけ同じクエリを発行してしまうので、ユーザIDで結果をキャッシュする（別ユーザIDが来ることはないはずだが念のため）
        static $cache = [];

        if (isset($cache[Auth::id()])) {
            return $cache[Auth::id()];
        }

        $all_tags = Auth::user()->tags()->orderBy('priority')->get();
        $cache[Auth::id()] = $all_tags;

        return $all_tags;
    }

    /**
     * 付与されていないタグ。
     *
     * @return Collection 付与されていないタグ
     */
    #[Computed]
    public function unattachedTags(): Collection
    {
        return $this->all_tags->diff($this->attached_tags);
    }

    /**
     * 追加予定のタグ。
     *
     * @return Collection 追加予定のタグ
     */
    #[Computed]
    public function addedTags(): Collection
    {
        return $this->attached_tags->diff($this->attached_tags_original);
    }

    /**
     * 削除予定のタグ。
     *
     * @return Collection 削除予定のタグ
     */
    #[Computed]
    public function removedTags(): Collection
    {
        return $this->attached_tags_original->diff($this->attached_tags);
    }

    /**
     * 付与されているタグ（DBに記録されている状態）。
     *
     * @return Collection 付与されているタグ
     */
    #[Computed]
    protected function attachedTagsOriginal(): Collection
    {
        $memo = Memo::find($this->memo_id);
        if (!isset($memo)) {
            return new Collection([]);
        }

        return $memo->tags()->orderBy('priority')->get();
    }

    /**
     * mount.
     *
     * @param \App\Models\Memo $memo メモ
     * @param string $tag_size タグのサイズ
     * @param string $select_size セレクトボックスのサイズ
     */
    public function mount(Memo $memo, string $tag_size = '', string $select_size = '')
    {
        $this->memo_id = $memo->id;
        $this->tag_size = $tag_size;
        $this->select_size = $select_size;

        $this->attached_tags = $memo->tags;
    }

    /**
     * タグ付与。
     *
     * @param int $id タグID
     */
    public function attachTag(int $tag_id): void
    {
        $tag = $this->all_tags->find($tag_id);

        // タグがない場合や、付与されているタグの場合は何もしない
        if (!isset($tag) || $this->attached_tags->contains($tag_id)){
            return;
        }

        $this->attached_tags->push($tag)->sortBy('priority');
    }

    /**
     * タグ除去。
     *
     * @param int $id タグID
     */
    public function detachTag(int $tag_id): void
    {
        // 付与されていないタグの場合何もしない
        if (!$this->attached_tags->contains($tag_id)) {
            return;
        }

        $this->attached_tags = $this->attached_tags->except([$tag_id]);
    }

    /**
     * 遅延更新。
     */
    #[On('tags-lazy-update')]
    public function saveLazy(): void
    {
        DB::transaction(function () {
            $memo = Memo::lockForUpdate()->find($this->memo_id);

            // メモがない場合は何もしない
            if (!isset($memo)) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            // タグが自分のタグでないものを除去
            $this->attached_tags = $this->attached_tags->whereStrict('user_id', Auth::id());

            // タグを更新
            $memo->tags()->sync($this->attached_tags->pluck('id')->all());

            unset($this->added_tags);
            unset($this->removed_tags);
            unset($this->attached_tags_original);
        });
        $this->dispatch('tags-lazy-updated');
    }
};
?>

<div {{ $attributes }}>
@foreach ($this->attached_tags->merge($this->removed_tags)->sortBy('priority') as $rec)
    <flux:badge class="me-1" size="{{ $tag_size }}" color="{{ $this->added_tags->contains($rec->id) ? 'yellow' : ($this->removed_tags->contains($rec->id) ? 'red' : 'blue') }}" wire:transition>
        {{ $rec->name }}
@if(!$this->removed_tags->contains($rec->id))
        <flux:badge.close wire:click="detachTag({{ $rec->id }})" />
@endif
    </flux:badge>
@endforeach
@if($this->unattached_tags->isNotEmpty())
    <flux:dropdown>
        <flux:button icon="ellipsis-horizontal" size="{{ $select_size }}" class="align-middle" variant="primary" color="blue"></flux:button>
        <flux:menu class="bg-blue-300! dark:bg-blue-500!">
@foreach ($this->unattached_tags as $rec)
            <flux:menu.item wire:click="attachTag({{ $rec->id }})">{{ $rec->name }}</flux:menu.item>
@endforeach
        </flux:menu>
    </flux:dropdown>
@endif
</div>
