<?php

use App\Models\Memo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public Collection $add_list;
    public Collection $list;

    #[Computed]
    public function list_()
    {
        return Auth::user()->memos;
    }

    public function mount()
    {
        $this->add_list = collect([]);

        $this->list = Auth::user()->memos;
    }

    public function create()
    {
        $this->add_list->add(new Memo());
    }
};
?>

<div>
	<flux:button square wire:click="create"><flux:icon.plus /></flux:button>
	<button type="button" wire:click="$refresh">test1</button>
	<div class="flex flex-wrap gap-4">
@foreach ($add_list as $rec)
		<livewire:memo.rec wire:key="new_{{ $loop->index }}" />
@endforeach
@foreach ($this->list as $rec)
		<livewire:memo.rec :$rec wire:key="{{ $rec->id }}_{{ $rec->updated_at }}" />
@endforeach
	</div>
</div>
