<?php

namespace App\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\CarbonSynth as Org;
use Override;

class CarbonSynth extends Org
{
    #[Override]
    function dehydrate($target) {
        return [
            $target->format('Y-m-d\TH:i:s.uP'),
            ['type' => array_search(get_class($target), static::$types)],
        ];
    }

}
