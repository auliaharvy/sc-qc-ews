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
        Schema::create('bad_news_first', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->text('problem');
            $table->text('description');
            $table->integer('qty');
            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->datetime('issuance_date');
            $table->datetime('completion_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bad_news_first');
    }
};
