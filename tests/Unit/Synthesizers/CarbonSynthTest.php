<?php

use App\Synthesizers\CarbonSynth;
use Carbon\CarbonImmutable;

test('CarbonSynth exists', function () {
    expect(class_exists(CarbonSynth::class))->toBeTrue();
});