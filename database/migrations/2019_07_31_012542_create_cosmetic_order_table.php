<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCosmeticOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cosmetic_order', function (Blueprint $table) {
            $table->unsignedBigInteger('cosmetic_id');
            $table->unsignedBigInteger('order_id');
            $table->timestamps();
            $table->primary(['cosmetic_id','order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cosmetic_order');
    }
}
