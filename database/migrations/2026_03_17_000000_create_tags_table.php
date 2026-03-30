<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->comment('タグ');

            $table->id()->comment('サロゲートキー');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ユーザID:users.id');
            $table->string('name', length: 100)->comment('名前');
            $table->unsignedInteger('priority')->comment('優先度');
            $table->datetimes(precision: 6);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->foreignId('created_by')->after('created_at')->comment('作成者');
            $table->foreignId('updated_by')->after('updated_at')->comment('更新者');
        });

        Schema::create('memo_tag', function (Blueprint $table) {
            $table->comment('メモとタグの中間テーブル');

            $table->id()->comment('サロゲートキー');
            $table->foreignId('memo_id')->constrained()->cascadeOnDelete()->comment('メモID:memos.id');
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete()->comment('タグID:tags.id');
            $table->datetimes(precision: 6);

            $table->unique(['memo_id', 'tag_id']);
        });

        Schema::table('memo_tag', function (Blueprint $table) {
            $table->foreignId('created_by')->after('created_at')->comment('作成者');
            $table->foreignId('updated_by')->after('updated_at')->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memo_tag');
        Schema::dropIfExists('tags');
    }
};
