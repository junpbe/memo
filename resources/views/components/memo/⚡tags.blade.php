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
        //TODO　ユーザID毎にメソッド内staticでキャッシュした方がよさそう
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
     * mount.
     *
     * @param Memo $memo メモ
     * @param string $tag_size タグのサイズ
     * @param string $select_size セレクトボックスのサイズ
     * @param bool $readonly 読み取り専用の場合true、そうでない場合false
     */
    public function mount(Memo $memo, string $tag_size = '', string $select_size = '', bool $readonly = false)
    {
        $this->memo_id = $memo->id;
        $this->tag_size = $tag_size;
        $this->select_size = $select_size;
        $this->readonly = $readonly;

        $this->attached_tags = $memo->tags;
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

        $memo = DB::transaction(function () use ($tag_id) {
            $memo = Memo::with(['tags' => fn($q) => $q->orderBy('priority')])->lockForUpdate()->find($this->memo_id);

            // メモがない場合や、既に付与されている場合は何もしない
            if (!isset($memo) || $memo->tags->contains($tag_id)) {
                return $memo;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $tag = Tag::lockForUpdate()->find($tag_id);

            // タグがない場合や、自分のタグではない場合は何もしない
            if (!isset($tag) || $tag->user_id !== Auth::id()){
                return $memo;
            }

            $memo->tags()->attach($tag_id);
            return $memo;
        });

        // 最新の状態に更新
        $this->attached_tags = $memo->tags()->orderBy('priority')->get();
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

        $memo = DB::transaction(function () use ($tag_id) {
            $memo = Memo::with(['tags' => fn($q) => $q->orderBy('priority')])->lockForUpdate()->find($this->memo_id);

            // メモがない場合や、付与されていない場合は何もしない
            if (!isset($memo) || !$memo->tags->contains($tag_id)) {
                $this->attached_tags = $memo->tags()->orderBy('priority')->get();
                return $memo;
            }

            // 権限チェック
            Gate::authorize('update', $memo);

            $memo->tags()->detach($tag_id);
            return $memo;
        });

        // 最新の状態に更新
        $this->attached_tags = $memo->tags()->orderBy('priority')->get();
    }
};
?>

<div {{ $attributes }}>
@foreach ($this->attached_tags as $rec)
    <flux:badge class="me-1" size="{{ $tag_size }}" color="blue" wire:transition>
        {{ $rec->name }}
@if(!$readonly)
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
