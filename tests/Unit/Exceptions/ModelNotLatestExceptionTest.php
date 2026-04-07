<?php

use App\Exceptions\ModelNotLatestException;

test('ModelNotLatestException can be thrown', function () {
    throw new ModelNotLatestException();
})->throws(ModelNotLatestException::class);