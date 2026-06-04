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
        if (Schema::hasTable('ITEM') && !Schema::hasColumn('ITEM', 'PATH')) {
            Schema::table('ITEM', function (Blueprint $table) {
                $table->string('PATH', 500)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ITEM') && Schema::hasColumn('ITEM', 'PATH')) {
            Schema::table('ITEM', function (Blueprint $table) {
                $table->dropColumn('PATH');
            });
        }
    }
};
