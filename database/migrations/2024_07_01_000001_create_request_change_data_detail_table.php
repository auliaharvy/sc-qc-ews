<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('request_change_data_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('request_change_data_id');
            $table->unsignedBigInteger('ng_type_id');
            $table->unsignedBigInteger('daily_checksheet_id');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('request_change_data_id')->references('id')->on('request_change_data')->onDelete('cascade');
            $table->foreign('ng_type_id')->references('id')->on('ng_types')->onDelete('cascade');
            $table->foreign('daily_checksheet_id')->references('id')->on('daily_checksheet')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_change_data_detail');
    }
};