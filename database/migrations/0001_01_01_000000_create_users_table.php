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
        Schema::create('US000', function (Blueprint $table) {
            $table->string('GUID', 128)->primary();
            $table->integer('NUMBER');
            $table->string('NAME', 500);
            $table->string('USER_NAME', 500)->unique();
            $table->string('PASSWORD', 500);
            $table->string('MOB1', 500)->nullable();
            $table->string('MAIL', 500)->nullable();
            $table->integer('USER_LEVEL')->default(1);
            $table->boolean('FREEZ')->default(false);
            $table->binary('IMG')->nullable();
            $table->rememberToken();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 128)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('US000');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
