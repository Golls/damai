<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('zip', false)->default(0);
            $table->string('city', 32)->default('');
            $table->string('area', 32)->default('');
            $table->string('road', 32)->default('');
            $table->string('lane', 32)->default('');
            $table->string('alley', 32)->default('');
            $table->string('no', 32)->default('');
            $table->string('floor', 32)->default('');
            $table->string('address', 255)->default('');
            $table->string('filename', 8)->default('');
            $table->float('latitude')->default(0.0);
            $table->float('lontitue')->default(0.0);
            $table->string('full_address', 255)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
