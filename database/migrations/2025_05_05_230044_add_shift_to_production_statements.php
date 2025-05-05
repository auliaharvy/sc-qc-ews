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
        Schema::table('production_statements', function (Blueprint $table) {
            $table->enum('shift', ['day', 'night'])
                  ->default('day')
                  ->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_statements', function (Blueprint $table) {
            $table->dropColumn('shift');
        });
    }
};
