<?php

use App\Models\Memo;
use Livewire\Component;

new class extends Component
{
    public Memo $memo;
};
?>

<div>

<div class="w-64 flex-initial rounded-xl border border-neutral-200 dark:border-neutral-700">
	{{ $memo->body }}
</div>

</div>
