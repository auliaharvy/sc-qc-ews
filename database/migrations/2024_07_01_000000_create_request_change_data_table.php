<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('request_change_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('daily_checksheet_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('part_id');
            $table->date('production_date');
            $table->enum('shift', ['day', 'night'])->default('day')->nullable(false);
            $table->integer('total_produced');
            $table->integer('total_ok');
            $table->integer('total_ng');
            $table->timestamps();

            $table->foreign('daily_checksheet_id')->references('id')->on('daily_checksheet')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_change_data');
    }
};