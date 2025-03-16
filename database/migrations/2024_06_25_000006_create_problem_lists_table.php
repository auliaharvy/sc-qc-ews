<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problem_lists', function (Blueprint $table) {
            $table->id();
            $table->date('production_date');
            $table->foreignId('part_id')->constrained();
            $table->text('problem_description');
            $table->unsignedInteger('quantity_affected');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_lists');
    }
};
