<?php

use App\Models\Memo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('Tag model has fillable attributes', function () {
    $tag = new Tag();
    expect($tag->getFillable())->toContain('name');
});

test('Tag model has user relationship', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create();

    expect($tag->user)->toBeInstanceOf(User::class);
    expect($tag->user->id)->toBe($user->id);
});

test('Tag model has memos relationship', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $user->id]);
    $memo = Memo::factory()->create(['user_id' => $user->id]);

    DB::table('memo_tag')->insert([
        'memo_id' => $memo->id,
        'tag_id' => $tag->id,
        'created_by' => $user->id,
        'updated_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tag->load('memos');

    expect($tag->memos)->toHaveCount(1);
    expect($tag->memos->first()->id)->toBe($memo->id);
});