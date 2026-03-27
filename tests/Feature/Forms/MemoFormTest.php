<?php

use App\Livewire\Forms\MemoForm;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('MemoForm validates body required', function () {
    $form = new MemoForm();
    $form->body = '';

    expect(fn() => $form->validate())->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('MemoForm can create new memo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $form = new MemoForm();
    $form->body = 'New memo body';

    DB::transaction(function () use ($form) {
        $form->save();
    });

    expect(Memo::count())->toBe(1);
    $memo = Memo::first();
    expect($memo->body)->toBe('New memo body');
    expect($memo->user_id)->toBe($user->id);
});

test('MemoForm can update existing memo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $memo = Memo::factory()->create(['user_id' => $user->id, 'body' => 'Original body']);

    $form = new MemoForm();
    $form->setModel($memo);
    $form->body = 'Updated body';

    DB::transaction(function () use ($form) {
        $form->save();
    });

    $memo->refresh();
    expect($memo->body)->toBe('Updated body');
});

test('MemoForm modelExists returns true when id is set', function () {
    $form = new MemoForm();
    $form->id = 1;

    expect($form->modelExists())->toBeTrue();
});

test('MemoForm modelExists returns false when id is not set', function () {
    $form = new MemoForm();

    expect($form->modelExists())->toBeFalse();
});