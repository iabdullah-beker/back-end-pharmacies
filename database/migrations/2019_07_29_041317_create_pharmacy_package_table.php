<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePharmacyPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_pharmacy', function (Blueprint $table) {
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('package_id');
            $table->timestamps();
            $table->primary(['pharmacy_id','package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pharmacy_package');
    }
}
