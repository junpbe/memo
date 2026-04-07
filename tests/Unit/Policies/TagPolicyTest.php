<?php

use App\Models\Tag;
use App\Models\User;
use App\Policies\TagPolicy;
use Mockery;

test('TagPolicy allows admin to view any', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('isAdministrator')->andReturn(true);
    $policy = new TagPolicy();

    expect($policy->before($user, 'viewAny'))->toBeTrue();
});

test('TagPolicy allows user to view own tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();
    $policy = new TagPolicy();

    expect($policy->view($user, $tag))->toBeTrue();
});

test('TagPolicy denies user to view others tag', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tag = Tag::factory()->for($otherUser)->create();
    $policy = new TagPolicy();

    expect($policy->view($user, $tag))->toBeFalse();
});

test('TagPolicy allows create', function () {
    $user = User::factory()->create();
    $policy = new TagPolicy();

    expect($policy->create($user))->toBeTrue();
});

test('TagPolicy allows user to update own tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();
    $policy = new TagPolicy();

    expect($policy->update($user, $tag))->toBeTrue();
});

test('TagPolicy allows user to delete own tag', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();
    $policy = new TagPolicy();

    expect($policy->delete($user, $tag))->toBeTrue();
});