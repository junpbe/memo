<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public Collection $memos;

    public function mount()
    {
        $this->memos = Auth::user()->memos;
    }
};
?>

<div class="flex flex-wrap gap-4">
@foreach ($memos as $memo)
	<livewire:memos.memo :$memo />
@endforeach
</div>
