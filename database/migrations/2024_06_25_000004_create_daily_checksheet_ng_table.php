<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_checksheet_ng', function (Blueprint $table) {
            $table->foreignId('daily_checksheet_id')->constrained('daily_checksheet');
            $table->foreignId('ng_type_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->primary(['daily_checksheet_id', 'ng_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_checksheet_ng');
    }
};
