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
        Schema::create('production_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['production', 'no_production']);
            $table->text('reason')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();

            // Add unique constraint to prevent duplicate entries for the same supplier and date
            $table->unique(['supplier_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_statements');
    }
};
