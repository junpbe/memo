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
        Schema::create('memos', function (Blueprint $table) {
            $table->comment('メモ');

            $table->id()->comment('サロゲートキー');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ユーザID:users.id');
            $table->text('body')->nullable()->comment('本文');
            $table->datetimes(6);
        });

        Schema::table('memos', function (Blueprint $table) {
            $table->foreignId('created_by')->after('created_at')->comment('作成者');
            $table->foreignId('updated_by')->after('updated_at')->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
