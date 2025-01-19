<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('adresse')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('code', 12)->unique()->nullable();
            $table->string('payement_id')->unique()->nullable();
            $table->string('payement_mode')->nullable();
            $table->string('tracking_no')->nullable();
            $table->unsignedBigInteger('money')->nullable();
            $table->unsignedBigInteger('nb_services')->nullable();
            $table->tinyInteger('active_plus')->default('0')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
            //$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            //$table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payement');
    }
}
