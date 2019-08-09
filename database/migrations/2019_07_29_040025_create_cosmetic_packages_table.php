<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCosmeticPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cosmetic_package', function (Blueprint $table) {
            $table->unsignedBigInteger('cosmetic_id');
            $table->unsignedBigInteger('package_id');
            $table->timestamps();

            $table->primary(['cosmetic_id','package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cosmetic_package');
    }
}
