<?php

use App\Exceptions\ModelNotLatestException;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
//use LogicException;

test('Lockable trait lockLatest returns model when up to date', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();
    $originalUpdatedAt = $memo->updated_at;

    DB::transaction(function () use ($memo, $originalUpdatedAt) {
        $lockedMemo = Memo::lockLatest($memo->id, $originalUpdatedAt);
        expect($lockedMemo->id)->toBe($memo->id);
    });
});

test('Lockable trait lockLatest throws exception when model not found', function () {
    DB::transaction(function () {
        expect(fn() => Memo::lockLatest(999, now()))->toThrow(ModelNotLatestException::class);
    });
});

test('Lockable trait lockLatest throws exception when updated_at differs', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();
    $oldUpdatedAt = $memo->updated_at->copy();

    DB::transaction(function () use ($memo, $oldUpdatedAt) {
        expect(fn() => Memo::lockLatest($memo->id, $oldUpdatedAt->subSecond()))->toThrow(ModelNotLatestException::class);
    });
});

test('Lockable trait lockLatest throws exception when not in transaction', function () {
    // Illuminate\Foundation\Testing\RefreshDatabaseがテスト用のトランザクションを切ってしまっているので、ロールバックで強引にトランザクションから外す
    DB::rollBack();

    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::make(['body' => 'Test memo']);
    $memo->user_id = $user->id;
    $memo->save();

    expect(fn() => Memo::lockLatest($memo->id, $memo->updated_at))->toThrow(LogicException::class);
});