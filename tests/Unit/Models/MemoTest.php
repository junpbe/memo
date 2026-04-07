<?php

use App\Models\Memo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('Memo model has fillable attributes', function () {
    $memo = new Memo();
    expect($memo->getFillable())->toContain('body');
});

test('Memo model has user relationship', function () {
    $user = User::factory()->create();
    $memo = Memo::factory()->for($user)->create();

    expect($memo->user)->toBeInstanceOf(User::class);
    expect($memo->user->id)->toBe($user->id);
});

test('Memo model has tags relationship', function () {
    $user = User::factory()->create();
    $memo = Memo::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create(['user_id' => $user->id]);

    DB::table('memo_tag')->insert([
        'memo_id' => $memo->id,
        'tag_id' => $tag->id,
        'created_by' => $user->id,
        'updated_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $memo->load('tags');

    expect($memo->tags)->toHaveCount(1);
    expect($memo->tags->first()->id)->toBe($tag->id);
});