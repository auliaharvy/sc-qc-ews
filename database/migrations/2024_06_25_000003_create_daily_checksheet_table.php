<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_checksheet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->date('production_date');
            $table->unsignedInteger('total_produced');
            $table->unsignedInteger('total_ok');
            $table->unsignedInteger('total_ng');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_checksheet');
    }
};
