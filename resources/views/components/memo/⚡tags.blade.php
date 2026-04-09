<?php

use App\Models\Memo;
use App\Models\Tag;
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

    /** @var bool 読み取り専用の場合true、そうでない場合false */
    #[Locked]
    public bool $readonly = false;

    /** @var bool 遅延更新の場合true、そうでない場合false */
    #[Locked]
    public bool $lazy_update = false;

    /** @var Collection 付与されているタグ */
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
        return Auth::user()->tags()->orderBy('priority')->get();
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
     * @param int $memo_id メモID
     * @param string $tag_size タグのサイズ
     * @param string $select_size セレクトボックスのサイズ
     * @param bool $readonly 読み取り専用の場合true、そうでない場合false
     * @param bool $lazy_update 遅延更新の場合true、そうでない場合false
     */
    public function mount(int $memo_id, string $tag_size = '', string $select_size = '', bool $readonly = false, bool $lazy_update = false)
    {
        $this->memo_id = $memo_id;
        $this->tag_size = $tag_size;
        $this->select_size = $select_size;
        $this->readonly = $readonly;
        $this->lazy_update = $lazy_update;

        // 最初にDBの状態を取得
        $this->attached_tags = $this->attached_tags_original;
    }

    /**
     * タグ付与。
     *
     * @param int $id タグID
     */
    public function attachTag(int $tag_id): void
    {
        // 読み取り専用の場合実行しない
        if ($this->readonly) {
            return;
        }

        // 遅延更新用
        if ($this->lazy_update) {
            $this->attachTagLazy($tag_id);
            return;
        }

        DB::transaction(function () use ($tag_id) {
            $memo = Memo::lockForUpdate()->find($this->memo_id);

            // メモがない場合や、既に付与されている場合は何もしない
            if (!isset($memo) || $memo->tags()->where('tag_id', $tag_id)->exists()) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $tag = Tag::lockForUpdate()->find($tag_id);

            // タグがない場合や、自分のタグではない場合は何もしない
            if (!isset($tag) || $tag->user_id !== Auth::id()){
                return;
            }

            $memo->tags()->attach($tag_id);
            $this->attached_tags = $this->attached_tags_original;
        });
    }

    /**
     * タグ除去。
     *
     * @param int $id タグID
     */
    public function detachTag(int $tag_id): void
    {
        // 読み取り専用の場合実行しない
        if ($this->readonly) {
            return;
        }

        // 遅延更新用
        if ($this->lazy_update) {
            $this->detachTagLazy($tag_id);
            return;
        }

        DB::transaction(function () use ($tag_id) {
            $memo = Memo::lockForUpdate()->find($this->memo_id);

            // メモがない場合や、付与されていない場合は何もしない
            if (!isset($memo) || !$memo->tags()->where('tag_id', $tag_id)->exists()) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $memo->tags()->detach($tag_id);
            $this->attached_tags = $this->attached_tags_original;
        });
    }

    /**
     * 遅延更新。
     */
    #[On('tags-lazy-update')]
    public function saveLazy(): void
    {
        if (!$this->lazy_update) {
            return;
        }

        DB::transaction(function () {
            $memo = Memo::lockForUpdate()->find($this->memo_id);

            // メモがない場合は何もしない
            if (!isset($memo)) {
                return;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $added_tags = $this->added_tags;
            $removed_tags = $this->removed_tags;

            foreach ($added_tags as $tag) {
                // タグが自分のタグでない場合はスキップ
                if ($tag->user_id !== Auth::id()) {
                    continue;
                }
                $memo->tags()->attach($tag->id);
            }

            foreach ($removed_tags as $tag) {
                $memo->tags()->detach($tag->id);
            }

            unset($this->added_tags);
            unset($this->removed_tags);
            unset($this->attached_tags_original);
        });
    }

    /**
     * タグ付与（遅延更新用）。
     *
     * @param int $id タグID
     */
    protected function attachTagLazy(int $tag_id): void
    {
        $tag = $this->all_tags->find($tag_id);

        // タグがない場合や、付与されているタグの場合は何もしない
        if (!isset($tag) || $this->attached_tags->contains($tag_id)){
            return;
        }

        $this->attached_tags->push($tag)->sortBy('priority');
    }

    /**
     * タグ除去（遅延更新用）。
     *
     * @param int $id タグID
     */
    protected function detachTagLazy(int $tag_id): void
    {
        // 付与されていないタグの場合何もしない
        if (!$this->attached_tags->contains($tag_id)) {
            return;
        }

        $this->attached_tags = $this->attached_tags->except([$tag_id]);
    }
};
?>

<div {{ $attributes }}>
@foreach ($this->attached_tags->merge($this->removed_tags)->sortBy('priority') as $rec)
    <flux:badge class="me-1" size="{{ $tag_size }}" color="{{ $this->added_tags->contains($rec->id) ? 'yellow' : ($this->removed_tags->contains($rec->id) ? 'red' : 'blue') }}" wire:transition>
        {{ $rec->name }}
@if(!$readonly && !$this->removed_tags->contains($rec->id))
        <flux:badge.close wire:click="detachTag({{ $rec->id }})" />
@endif
    </flux:badge>
@endforeach
@if(!$readonly && $this->unattached_tags->isNotEmpty())
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
