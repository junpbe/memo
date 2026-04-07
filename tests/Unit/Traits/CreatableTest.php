<?php

use App\Models\Memo;
use App\Models\User;
use Illuminate\Database\QueryException;

test('Creatable trait sets created_by on creating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();

    expect($memo->created_by)->toBe($user->id);
});

test('Creatable trait does not set created_by when timestamps are disabled', function () {
    // created_byはnotnullなので、QueryExceptionがスローされることを確認（通常用途ではありえない）

    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = new Memo(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->timestamps = false;
    expect(fn() => $memo->save())->toThrow(QueryException::class);
});