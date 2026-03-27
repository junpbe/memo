<?php

use App\Models\Memo;
use App\Models\User;

test('Creatable trait sets created_by on creating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::create(['body' => 'Test memo', 'user_id' => $user->id]);

    expect($memo->created_by)->toBe($user->id);
});

test('Creatable trait does not set created_by when timestamps are disabled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = new Memo(['body' => 'Test memo', 'user_id' => $user->id]);
    $memo->timestamps = false;
    $memo->save();

    expect($memo->created_by)->toBeNull();
});