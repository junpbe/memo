<?php

use App\Models\Memo;
use App\Models\User;

test('Updatable trait sets updated_by on creating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();

    expect($memo->updated_by)->toBe($user->id);
});

test('Updatable trait sets updated_by on updating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();

    $another_user = User::factory()->create();
    $this->actingAs($another_user);

    $memo->body = 'Updated body';
    $memo->save();

    expect($memo->updated_by)->toBe($another_user->id);
});

test('Updatable trait does not set updated_by when timestamps are disabled', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();

    $another_user = User::factory()->create();
    $this->actingAs($another_user);

    $memo->body = 'Updated body';
    $memo->timestamps = false;
    $memo->save();

    expect($memo->updated_by)->toBe($user->id);
});