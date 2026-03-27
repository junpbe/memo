<?php

use App\Models\Memo;
use App\Models\User;

test('Updatable trait sets updated_by on creating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::create(['body' => 'Test memo', 'user_id' => $user->id]);

    expect($memo->updated_by)->toBe($user->id);
});

test('Updatable trait sets updated_by on updating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::create(['body' => 'Test memo', 'user_id' => $user->id]);
    $memo->body = 'Updated body';
    $memo->save();

    expect($memo->updated_by)->toBe($user->id);
});

test('Updatable trait does not set updated_by when timestamps are disabled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = new Memo(['body' => 'Test memo', 'user_id' => $user->id]);
    $memo->timestamps = false;
    $memo->save();

    expect($memo->updated_by)->toBeNull();
});