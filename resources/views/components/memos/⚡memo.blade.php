<?php

use App\Models\Memo;
use Livewire\Component;

new class extends Component
{
    public Memo $memo;
};
?>

<div class="flex-none">
	<flux:memo-textarea resize="both">{{ $memo->body }}</flux:textarea>
</div>
