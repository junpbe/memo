<?php

use App\Models\Memo;
use App\Models\User;
use App\Policies\MemoPolicy;
//use Mockery;

test('MemoPolicy allows admin to view any', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('isAdministrator')->andReturn(true);
    $policy = new MemoPolicy();

    expect($policy->before($user, 'viewAny'))->toBeTrue();
});

test('MemoPolicy allows user to view own memo', function () {
    $user = User::factory()->create();
    $memo = Memo::factory()->for($user)->create();
    $policy = new MemoPolicy();

    expect($policy->view($user, $memo))->toBeTrue();
});

test('MemoPolicy denies user to view others memo', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $memo = Memo::factory()->for($otherUser)->create();
    $policy = new MemoPolicy();

    expect($policy->view($user, $memo))->toBeFalse();
});

test('MemoPolicy allows create', function () {
    $user = User::factory()->create();
    $policy = new MemoPolicy();

    expect($policy->create($user))->toBeTrue();
});

test('MemoPolicy allows user to update own memo', function () {
    $user = User::factory()->create();
    $memo = Memo::factory()->for($user)->create();
    $policy = new MemoPolicy();

    expect($policy->update($user, $memo))->toBeTrue();
});

test('MemoPolicy allows user to delete own memo', function () {
    $user = User::factory()->create();
    $memo = Memo::factory()->for($user)->create();
    $policy = new MemoPolicy();

    expect($policy->delete($user, $memo))->toBeTrue();
});