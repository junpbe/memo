<?php

use App\Livewire\Forms\TagForm;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('TagForm validates name required and max length', function () {
    $form = new TagForm();
    $form->name = '';

    expect(fn() => $form->validate())->toThrow(\Illuminate\Validation\ValidationException::class);

    $form->name = str_repeat('a', 51);
    expect(fn() => $form->validate())->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('TagForm can create new tag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $form = new TagForm();
    $form->name = 'New tag name';

    DB::transaction(function () use ($form) {
        $form->save();
    });

    expect(Tag::count())->toBe(1);
    $tag = Tag::first();
    expect($tag->name)->toBe('New tag name');
    expect($tag->user_id)->toBe($user->id);
});

test('TagForm can update existing tag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tag = Tag::factory()->create(['user_id' => $user->id, 'name' => 'Original name']);

    $form = new TagForm();
    $form->setModel($tag);
    $form->name = 'Updated name';

    DB::transaction(function () use ($form) {
        $form->save();
    });

    $tag->refresh();
    expect($tag->name)->toBe('Updated name');
});

test('TagForm modelExists returns true when id is set', function () {
    $form = new TagForm();
    $form->id = 1;

    expect($form->modelExists())->toBeTrue();
});

test('TagForm modelExists returns false when id is not set', function () {
    $form = new TagForm();

    expect($form->modelExists())->toBeFalse();
});